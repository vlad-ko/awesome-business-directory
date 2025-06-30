<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use App\Services\BusinessLogger;
use Illuminate\Support\Facades\Validator;
use Sentry\SentrySdk;
use function Sentry\addBreadcrumb;

class BusinessOnboardingController extends Controller
{
    // Define step configurations
    private const STEPS = [
        1 => [
            'title' => 'Tell Us About Your Amazing Business!',
            'fields' => ['business_name', 'industry', 'business_type', 'description', 'tagline'],
            'required' => ['business_name', 'industry', 'business_type', 'description'],
            'view' => 'onboarding.steps.step1'
        ],
        2 => [
            'title' => 'How Can Customers Reach You?',
            'fields' => ['primary_email', 'phone_number', 'website_url'],
            'required' => ['primary_email', 'phone_number'],
            'view' => 'onboarding.steps.step2'
        ],
        3 => [
            'title' => 'Where Can People Find You?',
            'fields' => ['street_address', 'city', 'state_province', 'postal_code', 'country'],
            'required' => ['street_address', 'city', 'state_province', 'postal_code', 'country'],
            'view' => 'onboarding.steps.step3'
        ],
        4 => [
            'title' => 'Owner Information',
            'fields' => ['owner_name', 'owner_email'],
            'required' => ['owner_name', 'owner_email'],
            'view' => 'onboarding.steps.step4'
        ]
    ];

    /**
     * Show a specific step of the multi-step form
     */
    public function showStep(Request $request, int $step)
    {
        // Validate step number
        if (!isset(self::STEPS[$step])) {
            return redirect()->route('business.onboard.step', 1);
        }

        // Check if user can access this step (must complete previous steps)
        if (!$this->canAccessStep($step)) {
            return redirect()->route('business.onboard.step', 1)
                ->with('error', 'Please complete the previous steps first.');
        }

        $startTime = microtime(true);

        // Set experience start time for step 1
        if ($step === 1 && !session()->has('onboarding_experience_start_time')) {
            session(['onboarding_experience_start_time' => $startTime]);
        }

        // Log multi-step step started with enhanced context
        BusinessLogger::multiStepStepStarted($step, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('referer'),
            'start_time' => $startTime,
        ]);

        // Start transaction for step viewing
        $transaction = BusinessLogger::startBusinessTransaction("onboarding_step_{$step}", [
            'step_number' => $step,
            'step_title' => self::STEPS[$step]['title'],
        ]);

        // Get existing data for this step (for editing)
        $stepData = session("onboarding_step_{$step}", []);

        // Log step view
        BusinessLogger::onboardingFormProgress("step_{$step}", [
            'step_number' => $step,
            'completion_percentage' => $this->getProgressPercentage($step),
            'has_existing_data' => !empty($stepData),
        ]);

        $response = response()->view(self::STEPS[$step]['view'], [
            'step' => $step,
            'stepConfig' => self::STEPS[$step],
            'data' => $stepData,
            'progress' => $this->getProgressPercentage($step),
            'totalSteps' => count(self::STEPS),
        ]);

        // Track UI performance
        $renderTime = (microtime(true) - $startTime) * 1000;
        BusinessLogger::onboardingUiPerformance([
            'step_number' => $step,
            'form_render_time_ms' => $renderTime,
            'has_existing_data' => !empty($stepData),
        ]);

        $transaction?->finish();
        return $response;
    }

    /**
     * Store data for a specific step
     */
    public function storeStep(Request $request, int $step)
    {
        // Validate step number
        if (!isset(self::STEPS[$step])) {
            return redirect()->route('business.onboard.step', 1);
        }

        // Check if user can store this step
        if (!$this->canAccessStep($step)) {
            return redirect()->route('business.onboard.step', 1)
                ->with('error', 'Please complete the previous steps first.');
        }

        $startTime = microtime(true);

        // Start transaction for step submission
        $transaction = BusinessLogger::startBusinessTransaction("onboarding_step_{$step}_submit", [
            'step_number' => $step,
            'fields_count' => count(self::STEPS[$step]['fields']),
        ]);

        // Validate step data
        $validationSpan = BusinessLogger::createBusinessSpan('validation', [
            'step' => $step,
            'fields_count' => count(self::STEPS[$step]['fields']),
        ]);

        $validator = $this->validateStep($request, $step);

        $validationSpan?->finish();

        // Handle validation failures
        if ($validator->fails()) {
            $transaction?->setData([
                'validation_status' => 'failed',
                'error_count' => count($validator->errors())
            ]);

            // Log multi-step validation errors
            BusinessLogger::multiStepValidationError($step, $validator->errors()->toArray(), $request->all());

            // Log validation errors for each field
            foreach ($validator->errors()->toArray() as $field => $errors) {
                foreach ($errors as $error) {
                    $errorType = $this->determineErrorType($error);
                    BusinessLogger::onboardingValidationError($field, $errorType, [
                        'step' => $step,
                        'error_message' => $error,
                        'field_value_length' => strlen($request->input($field, '')),
                        'total_errors' => count($validator->errors()),
                    ]);
                }
            }

            BusinessLogger::validationFailed($validator->errors()->toArray(), $request);
            $transaction?->finish();

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Store step data in session
        $validatedData = $validator->validated();
        session(["onboarding_step_{$step}" => $validatedData]);
        
        // Update progress
        $progress = $this->getProgressPercentage($step);
        session(['onboarding_progress' => $progress]);

        // Log successful step completion
        $processingTime = (microtime(true) - $startTime) * 1000;
        
        // Log multi-step step completion
        BusinessLogger::multiStepStepCompleted($step, $validatedData, $processingTime);
        
        BusinessLogger::onboardingFormProgress("step_{$step}_completed", [
            'step_number' => $step,
            'completion_percentage' => $progress,
            'processing_time_ms' => $processingTime,
            'fields_completed' => count($validatedData),
        ]);

        $transaction?->setData([
            'validation_status' => 'passed',
            'processing_time_ms' => round($processingTime, 2)
        ]);
        $transaction?->finish();

        // Determine next step
        $nextStep = $step + 1;
        if ($nextStep <= count(self::STEPS)) {
            return redirect()->route('business.onboard.step', $nextStep);
        } else {
            return redirect()->route('business.onboard.review');
        }
    }

    /**
     * Show review page with all collected data
     */
    public function review(Request $request)
    {
        // Check if all steps are completed
        if (!$this->allStepsCompleted()) {
            return redirect()->route('business.onboard.step', 1)
                ->with('error', 'Please complete all steps first.');
        }

        $startTime = microtime(true);

        // Gather all step data
        $allData = [];
        $allStepData = [];
        for ($i = 1; $i <= count(self::STEPS); $i++) {
            $stepData = session("onboarding_step_{$i}", []);
            $allData = array_merge($allData, $stepData);
            $allStepData["onboarding_step_{$i}"] = $stepData;
        }

        // Calculate total experience time (if available)
        $experienceStartTime = session('onboarding_experience_start_time', $startTime);
        $totalExperienceTime = ($startTime - $experienceStartTime) * 1000;

        // Log review page reached (high-intent event)
        BusinessLogger::multiStepReviewReached($allStepData, $totalExperienceTime);

        // Start transaction for review page
        $transaction = BusinessLogger::startBusinessTransaction('onboarding_review', [
            'total_fields' => count($allData),
        ]);

        $response = response()->view('onboarding.review', [
            'data' => $allData,
            'steps' => self::STEPS,
        ]);

        // Track performance
        $renderTime = (microtime(true) - $startTime) * 1000;
        BusinessLogger::onboardingUiPerformance([
            'page' => 'review',
            'form_render_time_ms' => $renderTime,
            'total_fields' => count($allData),
        ]);

        $transaction?->finish();
        return $response;
    }

    /**
     * Submit the final form and create business
     */
    public function submit(Request $request)
    {
        // Check if all steps are completed
        if (!$this->allStepsCompleted()) {
            return redirect()->route('business.onboard.step', 1)
                ->with('error', 'Please complete all steps first.');
        }

        $startTime = microtime(true);
        
        // Log distributed tracing context for debugging
        $sentryTrace = $request->header('sentry-trace') ?: $request->input('_sentry_sentry_trace');
        $baggage = $request->header('baggage') ?: $request->input('_sentry_baggage');
        
        addBreadcrumb(
            'distributed_tracing',
            'Final business submission with distributed tracing',
            [
                'has_sentry_trace' => !empty($sentryTrace),
                'has_baggage' => !empty($baggage),
                'trace_preview' => $sentryTrace ? substr($sentryTrace, 0, 20) . '...' : null,
                'request_method' => $request->method(),
                'user_agent' => $request->userAgent()
            ]
        );

        // Gather all data from session
        $allData = [];
        for ($i = 1; $i <= count(self::STEPS); $i++) {
            $allData = array_merge($allData, session("onboarding_step_{$i}", []));
        }

        // Start transaction for final submission with distributed tracing context
        $transaction = BusinessLogger::startBusinessTransaction('onboarding_final_submit', [
            'total_fields' => count($allData),
            'has_frontend_trace' => !empty($sentryTrace),
            'distributed_tracing' => [
                'trace_header' => $sentryTrace,
                'baggage_header' => $baggage,
                'correlation_id' => session()->getId()
            ]
        ]);

        // Add comprehensive context to transaction
        $transaction?->setData([
            'business_data' => [
                'business_name' => $allData['business_name'] ?? null,
                'industry' => $allData['industry'] ?? null,
                'business_type' => $allData['business_type'] ?? null,
                'city' => $allData['city'] ?? null,
                'state_province' => $allData['state_province'] ?? null
            ],
            'submission_context' => [
                'session_id' => session()->getId(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referrer' => $request->header('referer'),
                'total_steps_completed' => count(self::STEPS)
            ]
        ]);

        try {
            // Create span for data preparation
            $dataPreparationSpan = BusinessLogger::createBusinessSpan('data_preparation', [
                'operation' => 'prepare_business_data',
                'fields_count' => count($allData)
            ]);
            
            // Simulate data preparation work
            $dataPreparationSpan?->setData([
                'required_fields' => ['business_name', 'industry', 'primary_email'],
                'optional_fields' => array_diff(array_keys($allData), ['business_name', 'industry', 'primary_email']),
                'data_size_bytes' => strlen(json_encode($allData))
            ]);
            $dataPreparationSpan?->finish();

            // Create database span with comprehensive tracing
            $dbSpan = BusinessLogger::createDatabaseSpan('business_create', 'Creating business from multi-step form');
            
            // Add pre-insert context
            $dbSpan?->setData([
                'operation' => 'INSERT',
                'table' => 'businesses',
                'fields_count' => count($allData),
                'business_name' => $allData['business_name'] ?? null,
                'industry' => $allData['industry'] ?? null,
                'pre_insert_timestamp' => now()->toISOString()
            ]);

            // Create the business - this is the actual database operation
            addBreadcrumb(
                'database.operation',
                'Creating business record in database',
                [
                    'operation' => 'Business::create',
                    'table' => 'businesses',
                    'business_name' => $allData['business_name'] ?? null
                ]
            );
            
            $business = Business::create($allData);

            // Add post-insert context
            $dbSpan?->setData([
                'business_id' => $business->id,
                'business_slug' => $business->business_slug,
                'created_at' => $business->created_at->toISOString(),
                'post_insert_timestamp' => now()->toISOString(),
                'insert_success' => true
            ]);
            $dbSpan?->finish();

            // Calculate processing time
            $processingTime = (microtime(true) - $startTime) * 1000;

            // Calculate experience metrics
            $experienceStartTime = session('onboarding_experience_start_time', $startTime);
            $totalExperienceTime = ($startTime - $experienceStartTime) * 1000;
            
            $experienceMetrics = [
                'total_time_ms' => $totalExperienceTime,
                'processing_time_ms' => $processingTime,
                'review_visited' => true,
                'steps_completed' => count(self::STEPS),
                'total_fields' => count($allData),
            ];

            // Log multi-step conversion completion
            BusinessLogger::multiStepConversionCompleted($business, $experienceMetrics);

            // Log successful business creation
            BusinessLogger::businessCreated($business, $processingTime);

            // Clear session data
            $this->clearOnboardingSession();

            $transaction?->setData([
                'status' => 'success',
                'business_id' => $business->id,
                'processing_time_ms' => round($processingTime, 2)
            ]);
            $transaction?->finish();

            return redirect()->route('business.onboard.success')
                ->with('success', 'Business submitted for review!');

        } catch (\Exception $e) {
            // Use Sentry.captureException in try-catch blocks as per new rules
            \Sentry\captureException($e, [
                'tags' => [
                    'component' => 'business_onboarding',
                    'step' => 'final_submission',
                    'feature' => 'multi_step_form'
                ],
                'extra' => [
                    'input_data' => $allData,
                    'processing_time_ms' => (microtime(true) - $startTime) * 1000,
                    'session_id' => session()->getId(),
                    'user_ip' => $request->ip()
                ]
            ]);

            // Also log through BusinessLogger for comprehensive tracking
            BusinessLogger::applicationError($e, 'multi_step_business_creation_failed', [
                'input_data' => $allData,
                'processing_time_ms' => (microtime(true) - $startTime) * 1000,
            ]);

            $transaction?->setData([
                'status' => 'error',
                'error_type' => get_class($e)
            ]);
            $transaction?->finish();

            return redirect()->back()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Show success page
     */
    public function success()
    {
        return response()->view('onboarding.success');
    }

    /**
     * Check if user can access a specific step
     */
    private function canAccessStep(int $step): bool
    {
        if ($step === 1) {
            return true; // First step is always accessible
        }

        // Check if previous steps are completed
        for ($i = 1; $i < $step; $i++) {
            if (!session()->has("onboarding_step_{$i}")) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if all steps are completed
     */
    private function allStepsCompleted(): bool
    {
        for ($i = 1; $i <= count(self::STEPS); $i++) {
            if (!session()->has("onboarding_step_{$i}")) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get progress percentage based on step
     */
    private function getProgressPercentage(int $step): int
    {
        if ($step > count(self::STEPS)) {
            return 100;
        }
        return (int) round(($step / count(self::STEPS)) * 100);
    }

    /**
     * Validate data for a specific step
     */
    private function validateStep(Request $request, int $step): \Illuminate\Validation\Validator
    {
        $stepConfig = self::STEPS[$step];
        $rules = [];

        foreach ($stepConfig['required'] as $field) {
            $rules[$field] = 'required';
            
            // Add specific validation rules
            if ($field === 'primary_email' || $field === 'owner_email') {
                $rules[$field] .= '|email';
            } elseif ($field === 'website_url') {
                $rules[$field] = 'nullable|url';
            } elseif (in_array($field, ['business_name', 'industry', 'description'])) {
                $rules[$field] .= '|string|max:255';
            }
        }

        // Add non-required field rules
        foreach ($stepConfig['fields'] as $field) {
            if (!in_array($field, $stepConfig['required'])) {
                if ($field === 'website_url') {
                    $rules[$field] = 'nullable|url';
                } else {
                    $rules[$field] = 'nullable|string|max:255';
                }
            }
        }

        return Validator::make($request->all(), $rules);
    }

    /**
     * Clear all onboarding session data
     */
    private function clearOnboardingSession(): void
    {
        for ($i = 1; $i <= count(self::STEPS); $i++) {
            session()->forget("onboarding_step_{$i}");
            session()->forget("step_{$i}_error_attempts"); // Clear error attempt tracking
        }
        session()->forget('onboarding_progress');
        session()->forget('onboarding_experience_start_time');
    }
    public function create(Request $request)
    {
        // Redirect old single-form route to new multi-step flow
        return redirect()->route('business.onboard.step', 1);
    }

    public function store(Request $request)
    {
        // Redirect old single-form POST to new multi-step flow
        // Log the attempt for analytics
        BusinessLogger::userInteraction('old_form_attempted', [
            'fields_provided' => array_keys($request->all()),
            'redirected_to' => 'multi_step_flow',
        ]);
        
        return redirect()->route('business.onboard.step', 1);
    }

    /**
     * Determine the type of validation error for better tracking
     */
    private function determineErrorType(string $errorMessage): string
    {
        return match (true) {
            str_contains($errorMessage, 'required') => 'required',
            str_contains($errorMessage, 'email') => 'invalid_email',
            str_contains($errorMessage, 'max') => 'too_long',
            str_contains($errorMessage, 'min') => 'too_short',
            str_contains($errorMessage, 'string') => 'invalid_format',
            str_contains($errorMessage, 'numeric') => 'invalid_number',
            default => 'other'
        };
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use App\Services\BusinessLogger;
use Illuminate\Support\Facades\Validator;

class BusinessOnboardingController extends Controller
{
    public function create(Request $request)
    {
        $startTime = microtime(true);

        // Track if user came from welcome page CTA
        $referrer = $request->header('referer');
        if ($referrer && str_contains($referrer, request()->getSchemeAndHttpHost())) {
            $path = parse_url($referrer, PHP_URL_PATH);
            if ($path === '/') {
                BusinessLogger::welcomeCtaClicked('list_business', $request);
            }
        }

        // Log that someone started the onboarding process
        BusinessLogger::onboardingStarted($request);

        // Track UI performance for the fun form
        $response = response()->view('onboarding.create');
        $renderTime = (microtime(true) - $startTime) * 1000;
        
        BusinessLogger::onboardingUiPerformance([
            'form_render_time_ms' => $renderTime,
            'has_referrer' => !empty($referrer),
            'source_page' => $path ?? 'direct',
        ]);

        return $response;
    }

    public function store(Request $request)
    {
        $startTime = microtime(true);

        // Start custom transaction for business onboarding
        $transaction = BusinessLogger::startBusinessTransaction('onboarding', [
            'industry' => $request->input('industry'),
            'business_type' => $request->input('business_type'),
        ]);

        // Create validation span
        $validationSpan = BusinessLogger::createBusinessSpan('validation', [
            'fields_count' => count($request->all()),
        ]);

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string|max:255',
            'industry' => 'required|string',
            'description' => 'required|string',
            'primary_email' => 'required|email',
            'phone_number' => 'required|string',
            'street_address' => 'required|string',
            'city' => 'required|string',
            'state_province' => 'required|string',
            'postal_code' => 'required|string',
            'country' => 'required|string',
            'owner_name' => 'required|string',
            'owner_email' => 'required|email',
            'business_type' => 'required|string',
        ]);

        $validationSpan?->finish();

        // Log validation failures with structured data
        if ($validator->fails()) {
            $transaction?->setData([
                'validation_status' => 'failed',
                'error_count' => count($validator->errors())
            ]);
            
            // Enhanced validation error tracking for each field
            foreach ($validator->errors()->toArray() as $field => $errors) {
                foreach ($errors as $error) {
                    $errorType = $this->determineErrorType($error);
                    BusinessLogger::onboardingValidationError($field, $errorType, [
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

        $transaction?->setData(['validation_status' => 'passed']);
        $validatedData = $validator->validated();

        try {
            // Create database span for business creation
            $dbSpan = BusinessLogger::createDatabaseSpan('business_create', 'Creating new business record');
            
            // Create the business
            $business = Business::create($validatedData);
            
            $dbSpan?->setData(['business_id' => $business->id]);
            $dbSpan?->finish();

            // Calculate processing time
            $processingTime = (microtime(true) - $startTime) * 1000;

            // Set transaction success data
            $transaction?->setData([
                'status' => 'success',
                'business_id' => $business->id,
                'processing_time_ms' => round($processingTime, 2)
            ]);

            // Log successful business creation with structured data
            BusinessLogger::businessCreated($business, $processingTime);

            // Log performance metric
            BusinessLogger::performanceMetric('business_creation', $processingTime, [
                'business_id' => $business->id,
                'industry' => $business->industry,
            ]);

            // Log user interaction
            BusinessLogger::userInteraction('business_submitted', [
                'business_id' => $business->id,
                'business_name' => $business->business_name,
            ]);

            $transaction?->finish();
            return redirect()->route('business.onboard')->with('success', 'Business submitted for review!');

        } catch (\Exception $e) {
            // Set transaction error data
            $transaction?->setData([
                'status' => 'error',
                'error_type' => get_class($e)
            ]);
            
            // Log any errors that occur during business creation
            BusinessLogger::applicationError($e, 'business_creation_failed', [
                'input_data' => $validatedData,
                'processing_time_ms' => (microtime(true) - $startTime) * 1000,
            ]);

            $transaction?->finish();
            return redirect()->back()
                ->with('error', 'Something went wrong. Please try again.')
                ->withInput();
        }
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

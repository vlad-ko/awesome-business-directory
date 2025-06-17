<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use Illuminate\Support\Facades\Log;

class BusinessOnboardingController extends Controller
{
    public function create()
    {
        return view('onboarding.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
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

        Log::info('Creating business', $validatedData);

        $business = Business::create($validatedData);

        Log::info('Business created', [
            'id' => $business->id,
            'business_name' => $business->business_name,
            'business_slug' => $business->business_slug,
        ]);

        return redirect()->route('business.onboard')->with('success', 'Business submitted for review!');
    }
}

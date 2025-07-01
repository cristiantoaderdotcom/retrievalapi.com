<?php

namespace App\Http\Controllers;

use App\Models\Waitlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class WaitlistController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:waitlist,email',
                'business_name' => 'required|string|max:255',
                'website' => 'nullable|url|max:255',
                'niche' => 'required|string|max:255',
                'platforms' => 'nullable|array',
                'platforms.*' => 'string',
                'desired_features' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            Waitlist::create([
                'name' => $request->name,
                'email' => $request->email,
                'business_name' => $request->business_name,
                'website' => $request->website,
                'niche' => $request->niche,
                'platforms' => $request->platforms ?? [],
                'desired_features' => $request->desired_features,
                'submitted_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Thank you for joining our waitlist! We\'ll be in touch soon with exclusive updates and early access information.');
            
        } catch (ThrottleRequestsException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['rate_limit' => 'You can only submit to our waitlist once every 2 weeks. Please try again later.']);
        }
    }
}

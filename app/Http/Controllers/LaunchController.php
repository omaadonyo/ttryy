<?php

namespace App\Http\Controllers;

use App\Models\LaunchSignup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LaunchController extends Controller
{
    public function index()
    {
        return view('launch', [
            'niches' => config('packages.niches', []),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'idea_name' => ['required', 'string', 'max:120'],
            'niche' => ['nullable', 'string', 'max:80'],
            'idea' => ['required', 'string', 'min:20', 'max:3000'],
            'terms' => ['accepted'],
        ]);

        $signup = LaunchSignup::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'idea_name' => $validated['idea_name'],
            'niche' => $validated['niche'] ?? null,
            'idea' => $validated['idea'],
            'status' => LaunchSignup::STATUS_NEW,
        ]);

        try {
            Mail::raw(
                "New Launch 10K application #{$signup->id}:\n\n".
                "Name: {$signup->name}\nPhone: {$signup->phone}\nEmail: ".($signup->email ?? '—')."\n".
                "Idea: {$signup->idea_name}\nNiche: ".($signup->niche ?? '—')."\n\n{$signup->idea}",
                function ($message) {
                    $message->to(config('packages.admin_email'))->subject('New Launch 10K application');
                }
            );
        } catch (\Throwable $e) {
            Log::warning('Launch signup email failed', ['error' => $e->getMessage()]);
        }

        return back()->with('launch_success', "Application received, {$signup->name}. Our team reviews every idea personally — expect our call on {$signup->phone} within 2 working days.");
    }
}

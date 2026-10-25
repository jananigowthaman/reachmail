<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\EmailAccount;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'sender_name' => Setting::get('sender_name', 'Janani Gowthaman'),
            'sender_email' => Setting::get('sender_email', 'jananigowthaman025@gmail.com'),
            'signature' => Setting::get('signature', "Best regards,\nJanani Gowthaman"),
            'default_reply_to' => Setting::get('default_reply_to', ''),
            'default_sending_window' => Setting::get('default_sending_window', '09:00 - 18:00'),
            'daily_sending_limit' => Setting::get('daily_sending_limit', 150),
            'google_client_id' => Setting::get('google_client_id', config('services.google.client_id', '')),
            'google_client_secret' => Setting::get('google_client_secret', config('services.google.client_secret', '')),
            'google_redirect_uri' => Setting::get('google_redirect_uri', route('settings.oauth.callback')),
        ];

        $emailAccount = EmailAccount::where('provider', 'gmail')->first();

        return view('settings.index', compact('settings', 'emailAccount'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'sender_name' => 'required|string|max:255',
            'sender_email' => 'required|email|max:255',
            'signature' => 'nullable|string',
            'default_reply_to' => 'nullable|email|max:255',
            'daily_sending_limit' => 'required|integer|min:1|max:500',
        ]);

        foreach ($validated as $k => $v) {
            Setting::set($k, $v);
        }

        ActivityLog::log('settings_updated', 'Sender details and sending limits updated.');

        return back()->with('success', 'Sender settings updated successfully.');
    }

    public function updateGoogleCredentials(Request $request)
    {
        $validated = $request->validate([
            'google_client_id' => 'required|string',
            'google_client_secret' => 'required|string',
            'google_redirect_uri' => 'nullable|url',
        ]);

        Setting::set('google_client_id', trim($validated['google_client_id']));
        Setting::set('google_client_secret', trim($validated['google_client_secret']));
        if (!empty($validated['google_redirect_uri'])) {
            Setting::set('google_redirect_uri', trim($validated['google_redirect_uri']));
        }

        ActivityLog::log('google_credentials_saved', 'Google OAuth credentials updated via Settings.');

        return back()->with('success', 'Google OAuth credentials saved successfully. You can now click "Connect Gmail Account".');
    }

    public function connectGmail()
    {
        $clientId = Setting::get('google_client_id') ?: config('services.google.client_id');
        $clientSecret = Setting::get('google_client_secret') ?: config('services.google.client_secret');
        $redirectUri = Setting::get('google_redirect_uri') ?: route('settings.oauth.callback');

        if (!$clientId || !$clientSecret) {
            return back()->with('error', 'Google OAuth credentials are missing. Please enter your Client ID and Client Secret below and click Save.');
        }

        $client = new \Google\Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);
        $client->addScope([\Google\Service\Gmail::GMAIL_SEND, \Google\Service\Gmail::GMAIL_READONLY]);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        return redirect()->away($client->createAuthUrl());
    }

    public function gmailCallback(Request $request)
    {
        $code = $request->input('code');
        if (!$code) {
            return redirect()->route('settings.index')->with('error', 'OAuth authorization cancelled or failed.');
        }

        try {
            $clientId = Setting::get('google_client_id') ?: config('services.google.client_id');
            $clientSecret = Setting::get('google_client_secret') ?: config('services.google.client_secret');
            $redirectUri = Setting::get('google_redirect_uri') ?: route('settings.oauth.callback');

            $client = new \Google\Client();
            $client->setClientId($clientId);
            $client->setClientSecret($clientSecret);
            $client->setRedirectUri($redirectUri);

            $token = $client->fetchAccessTokenWithAuthCode($code);
            $service = new \Google\Service\Gmail($client);
            $profile = $service->users->getProfile('me');

            EmailAccount::updateOrCreate(
                ['provider' => 'gmail'],
                [
                    'email_address' => $profile->getEmailAddress(),
                    'access_token' => $token['access_token'],
                    'refresh_token' => $token['refresh_token'] ?? null,
                    'token_expires_at' => now()->addSeconds($token['expires_in'] ?? 3600),
                    'is_active' => true,
                    'connection_status' => 'connected',
                ]
            );

            ActivityLog::log('gmail_connected', "Connected Gmail account {$profile->getEmailAddress()}");

            return redirect()->route('settings.index')->with('success', "Gmail account connected: {$profile->getEmailAddress()}");
        } catch (\Exception $e) {
            return redirect()->route('settings.index')->with('error', 'Failed to connect Gmail: ' . $e->getMessage());
        }
    }

    public function disconnectGmail()
    {
        EmailAccount::where('provider', 'gmail')->delete();
        ActivityLog::log('gmail_disconnected', 'Disconnected Gmail sending integration.');

        return back()->with('success', 'Gmail integration disconnected successfully.');
    }
}

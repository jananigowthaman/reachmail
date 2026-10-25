<?php

namespace App\Http\Controllers;

use App\Models\CampaignRecipient;
use App\Models\EmailEvent;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Track transparent 1x1 GIF / PNG pixel for email opens.
     */
    public function trackOpen(string $token)
    {
        $recipient = CampaignRecipient::where('tracking_token', $token)->first();

        if ($recipient) {
            // Log opening event
            EmailEvent::create([
                'campaign_recipient_id' => $recipient->id,
                'event_type' => 'open',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'occurred_at' => now(),
            ]);

            if (!$recipient->opened_at) {
                $recipient->opened_at = now();
                $recipient->campaign->increment('opened_count');
            }

            $recipient->increment('open_count');
            $recipient->save();
        }

        // Return 1x1 transparent GIF
        $gif = base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
        return response($gif, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }
}

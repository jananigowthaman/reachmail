<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\EmailAccount;
use App\Models\ActivityLog;
use App\Models\EmailEvent;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailDeliveryService
{
    /**
     * Deliver an individual recipient's email using Gmail API or local SMTP/mail driver.
     */
    public function sendRecipient(CampaignRecipient $recipient): bool
    {
        $campaign = $recipient->campaign;
        $contact = $recipient->contact;

        // Verify Do Not Contact suppression
        if ($contact->do_not_contact) {
            $recipient->update([
                'status' => 'skipped',
                'error_message' => 'Suppressed: Contact is marked as Do Not Contact.'
            ]);
            return false;
        }

        try {
            $senderName = config('mail.from.name', 'ReachMail User');
            $senderEmail = config('mail.from.address', 'me@reachmail.local');

            $subject = $recipient->rendered_subject;
            $body = $recipient->rendered_body;
            $attachment = $campaign->attachment;

            // Generate unique tracking token if not present
            if (!$recipient->tracking_token) {
                $recipient->tracking_token = Str::random(32);
                $recipient->save();
            }

            // Append tracking pixel safely if configured
            $trackingPixel = '<img src="' . route('track.open', $recipient->tracking_token) . '" width="1" height="1" style="display:none;" alt="" />';
            $bodyWithTracking = $body . $trackingPixel;

            // Check if active Gmail OAuth exists
            $account = EmailAccount::where('is_active', true)->where('connection_status', 'connected')->first();

            $messageId = 'reachmail_' . uniqid() . '@' . request()->getHost();

            if ($account && $account->provider === 'gmail' && !empty($account->access_token)) {
                // Official Gmail API sending
                $messageId = $this->sendViaGmailApi($account, $recipient->email, $subject, $bodyWithTracking, $attachment);
            } else {
                // Laravel Mail delivery (fallback or configured SMTP)
                Mail::html($bodyWithTracking, function ($msg) use ($recipient, $subject, $attachment, $senderName, $senderEmail) {
                    $msg->to($recipient->email)
                        ->from($senderEmail, $senderName)
                        ->subject($subject);

                    if ($attachment && file_exists(storage_path('app/' . $attachment->file_path))) {
                        $msg->attach(storage_path('app/' . $attachment->file_path), [
                            'as' => $attachment->file_name,
                            'mime' => $attachment->mime_type,
                        ]);
                    }
                });
            }

            // Update recipient status idempotently
            $recipient->update([
                'status' => 'sent',
                'provider_message_id' => $messageId,
                'sent_at' => now(),
            ]);

            // Update contact last contacted state
            $contact->update([
                'status' => 'Contacted',
                'last_contacted_at' => now(),
                'last_campaign_name' => $campaign->name,
            ]);

            // Update campaign counters
            $campaign->increment('sent_count');

            return true;
        } catch (\Exception $e) {
            $recipient->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $campaign->increment('failed_count');
            return false;
        }
    }

    /**
     * Send email via official Google Client library using RFC 2822 payload.
     */
    protected function sendViaGmailApi(EmailAccount $account, string $toEmail, string $subject, string $bodyHtml, $attachment = null): string
    {
        $clientId = \App\Models\Setting::get('google_client_id') ?: config('services.google.client_id');
        $clientSecret = \App\Models\Setting::get('google_client_secret') ?: config('services.google.client_secret');

        $client = new \Google\Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setAccessToken([
            'access_token' => $account->access_token,
            'refresh_token' => $account->refresh_token,
        ]);

        if ($client->isAccessTokenExpired() && $account->refresh_token) {
            $newToken = $client->fetchAccessTokenWithRefreshToken($account->refresh_token);
            $account->update([
                'access_token' => $newToken['access_token'],
                'token_expires_at' => now()->addSeconds($newToken['expires_in'] ?? 3600),
            ]);
        }

        $service = new \Google\Service\Gmail($client);

        $boundary = uniqid('reachmail_boundary_');
        $rawMessageString = "To: {$toEmail}\r\n";
        $rawMessageString .= "Subject: =?utf-8?B?" . base64_encode($subject) . "?=\r\n";
        $rawMessageString .= "MIME-Version: 1.0\r\n";
        $rawMessageString .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n\r\n";

        // Body section
        $rawMessageString .= "--{$boundary}\r\n";
        $rawMessageString .= "Content-Type: text/html; charset=utf-8\r\n";
        $rawMessageString .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $rawMessageString .= $bodyHtml . "\r\n\r\n";

        // Attachment if applicable
        if ($attachment && file_exists(storage_path('app/' . $attachment->file_path))) {
            $fileData = file_get_contents(storage_path('app/' . $attachment->file_path));
            $base64File = chunk_split(base64_encode($fileData));

            $rawMessageString .= "--{$boundary}\r\n";
            $rawMessageString .= "Content-Type: {$attachment->mime_type}; name=\"{$attachment->file_name}\"\r\n";
            $rawMessageString .= "Content-Disposition: attachment; filename=\"{$attachment->file_name}\"\r\n";
            $rawMessageString .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $rawMessageString .= $base64File . "\r\n\r\n";
        }

        $rawMessageString .= "--{$boundary}--";

        $rawMessage = rtrim(strtr(base64_encode($rawMessageString), '+/', '-_'), '=');

        $msg = new \Google\Service\Gmail\Message();
        $msg->setRaw($rawMessage);

        $sentMsg = $service->users_messages->send('me', $msg);
        return $sentMsg->getId() ?? ('gmail_' . uniqid());
    }
}

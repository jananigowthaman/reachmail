<?php

namespace App\Http\Controllers;

use App\Models\EmailReply;
use App\Models\EmailAccount;
use App\Models\Contact;
use App\Models\Campaign;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class EmailReplyController extends Controller
{
    public function index(Request $request)
    {
        $replies = EmailReply::with(['contact', 'campaign'])
            ->latest('received_at')
            ->paginate(15);

        return view('replies.index', compact('replies'));
    }

    public function show(EmailReply $reply)
    {
        if ($reply->status === 'unread') {
            $reply->update(['status' => 'reviewed']);
        }

        $reply->load(['contact', 'campaign']);
        return view('replies.show', compact('reply'));
    }

    public function update(Request $request, EmailReply $reply)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $reply->update($validated);

        return back()->with('success', 'Reply status updated.');
    }

    /**
     * Poll Gmail API messages matching recent threads.
     */
    public function syncNow()
    {
        $account = EmailAccount::where('is_active', true)->where('connection_status', 'connected')->first();

        if (!$account || !$account->access_token) {
            return back()->with('error', 'No connected Gmail account available for synchronization. Connect your Gmail in Settings.');
        }

        try {
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

            // List recent inbox messages
            $response = $service->users_messages->listUsersMessages('me', [
                'maxResults' => 15,
                'q' => 'is:inbox',
            ]);

            $syncedCount = 0;
            if ($response->getMessages()) {
                foreach ($response->getMessages() as $msgMeta) {
                    $msgId = $msgMeta->getId();

                    if (EmailReply::where('provider_message_id', $msgId)->exists()) {
                        continue;
                    }

                    $msg = $service->users_messages->get('me', $msgId, ['format' => 'full']);
                    $headers = $msg->getPayload()->getHeaders();

                    $from = '';
                    $subject = '';
                    foreach ($headers as $h) {
                        if (strtolower($h->getName()) === 'from') $from = $h->getValue();
                        if (strtolower($h->getName()) === 'subject') $subject = $h->getValue();
                    }

                    preg_match('/<([^>]+)>/', $from, $matches);
                    $senderEmail = $matches[1] ?? $from;

                    // Match sender to an existing outreach contact
                    $contact = Contact::where('normalized_email', Contact::normalizeEmail($senderEmail))->first();

                    if ($contact) {
                        EmailReply::create([
                            'contact_id' => $contact->id,
                            'sender_email' => $senderEmail,
                            'sender_name' => trim(str_replace("<{$senderEmail}>", '', $from)),
                            'subject' => $subject,
                            'snippet' => $msg->getSnippet(),
                            'provider_message_id' => $msgId,
                            'provider_thread_id' => $msg->getThreadId(),
                            'received_at' => now(),
                            'status' => 'unread',
                        ]);

                        $contact->update(['status' => 'Replied', 'reply_status' => 'Replied']);
                        $syncedCount++;
                    }
                }
            }

            $account->update(['last_synced_at' => now()]);
            ActivityLog::log('replies_synced', "Synchronized {$syncedCount} new replies from connected email account.");

            return back()->with('success', "Synchronization completed: {$syncedCount} new replies identified.");
        } catch (\Exception $e) {
            return back()->with('error', 'Synchronization error: ' . $e->getMessage());
        }
    }
}

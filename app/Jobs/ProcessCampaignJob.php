<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\ActivityLog;
use App\Services\EmailDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $campaignId;

    public function __construct(int $campaignId)
    {
        $this->campaignId = $campaignId;
    }

    public function handle(EmailDeliveryService $deliveryService): void
    {
        $campaign = Campaign::with(['recipients.contact', 'attachment'])->find($this->campaignId);

        if (!$campaign || in_array($campaign->status, ['completed', 'cancelled'])) {
            return;
        }

        $campaign->update([
            'status' => 'processing',
            'started_at' => $campaign->started_at ?? now(),
        ]);

        $pendingRecipients = $campaign->recipients()->where('status', 'pending')->get();

        foreach ($pendingRecipients as $recipient) {
            // Re-check campaign cancellation between sends
            $campaign->refresh();
            if ($campaign->status === 'cancelled') {
                break;
            }

            $deliveryService->sendRecipient($recipient);

            // Throttle between emails to respect provider limits
            if ($campaign->delay_between_sends > 0) {
                sleep(min($campaign->delay_between_sends, 15));
            }
        }

        $campaign->refresh();
        $hasPending = $campaign->recipients()->where('status', 'pending')->exists();

        if (!$hasPending && $campaign->status !== 'cancelled') {
            $status = $campaign->failed_count > 0 ? 'completed_with_errors' : 'completed';
            $campaign->update([
                'status' => $status,
                'completed_at' => now(),
            ]);

            ActivityLog::log('campaign_finished', "Campaign '{$campaign->name}' finished with {$campaign->sent_count} sent and {$campaign->failed_count} failed.", $campaign);
        }
    }
}

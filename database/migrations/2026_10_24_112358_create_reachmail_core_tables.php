<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color', 20)->default('#2563eb');
            $table->timestamps();
        });

        // 2. Contacts
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('normalized_email')->unique();
            $table->string('full_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company_name')->nullable()->index();
            $table->string('job_title')->nullable();
            $table->string('company_website')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('location')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('source')->nullable()->default('Manual');
            $table->string('source_url')->nullable();
            $table->date('date_collected')->nullable();
            $table->string('email_verification_status')->default('Unverified'); // Unverified, Valid, Risky, Invalid
            $table->string('status')->default('New'); // New, Ready to Contact, Contacted, Replied, Follow-up Due, Not Interested, Invalid Email, Do Not Contact
            $table->boolean('do_not_contact')->default(false)->index();
            $table->timestamp('last_contacted_at')->nullable();
            $table->string('last_campaign_name')->nullable();
            $table->string('reply_status')->nullable(); // None, Replied, Interested, Not Interested
            $table->date('follow_up_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Category Contact Pivot
        Schema::create('category_contact', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->unique(['category_id', 'contact_id']);
        });

        // 4. Contact Imports & Rows
        Schema::create('contact_imports', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('original_filename');
            $table->integer('total_rows')->default(0);
            $table->integer('imported_count')->default(0);
            $table->integer('updated_count')->default(0);
            $table->integer('skipped_count')->default(0);
            $table->integer('invalid_count')->default(0);
            $table->string('status')->default('completed'); // pending, processing, completed, failed
            $table->string('duplicate_mode')->default('skip'); // skip, update
            $table->json('column_mapping')->nullable();
            $table->json('error_log')->nullable();
            $table->foreignId('target_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('contact_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_import_id')->constrained()->cascadeOnDelete();
            $table->integer('row_number');
            $table->json('raw_data');
            $table->string('status'); // imported, updated, skipped_duplicate, invalid, failed
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        // 5. Attachments (Resumes, cover letters, certificates)
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('display_name');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type', 50)->default('resume'); // resume, cover_letter, certificate, other
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0); // bytes
            $table->boolean('is_default_resume')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 6. Email Templates
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('General'); // Job Application, HR Enquiry, Recruiter Introduction, Follow-up, Interview Follow-up, General
            $table->string('subject');
            $table->longText('body_html');
            $table->longText('body_text')->nullable();
            $table->foreignId('default_attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->boolean('is_favorite')->default(false);
            $table->timestamps();
        });

        // 7. Campaigns
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('purpose')->nullable();
            $table->string('type')->default('Job Application'); // Job Application, HR Enquiry, Recruiter Outreach, Follow-up, Custom
            $table->string('job_title')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('status')->default('draft'); // draft, scheduled, processing, completed, completed_with_errors, cancelled, paused
            $table->foreignId('email_template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('attachment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject_snapshot')->nullable();
            $table->longText('body_html_snapshot')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('replied_count')->default(0);
            $table->integer('delay_between_sends')->default(10); // seconds
            $table->timestamps();
        });

        // 8. Campaign Categories Pivot
        Schema::create('campaign_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->unique(['campaign_id', 'category_id']);
        });

        // 9. Campaign Recipients (Snapshot & individual sending status)
        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('rendered_subject')->nullable();
            $table->longText('rendered_body')->nullable();
            $table->string('status')->default('pending'); // pending, queued, sent, delivered, bounced, failed, skipped
            $table->string('provider_message_id')->nullable()->index();
            $table->string('provider_thread_id')->nullable()->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->integer('open_count')->default(0);
            $table->timestamp('replied_at')->nullable();
            $table->string('tracking_token', 64)->nullable()->unique();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->unique(['campaign_id', 'contact_id']);
        });

        // 10. Email Accounts (OAuth credentials & SMTP)
        Schema::create('email_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('gmail'); // gmail, smtp
            $table->string('email_address');
            $table->string('sender_name')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('connection_status')->default('connected'); // connected, disconnected, expired, error
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        // 11. Email Replies
        Schema::create('email_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_recipient_id')->nullable()->constrained('campaign_recipients')->nullOnDelete();
            $table->string('sender_email');
            $table->string('sender_name')->nullable();
            $table->string('subject');
            $table->longText('snippet')->nullable();
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->string('provider_message_id')->nullable()->index();
            $table->string('provider_thread_id')->nullable()->index();
            $table->timestamp('received_at');
            $table->string('status')->default('unread'); // unread, reviewed, action_required, ignored
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 12. Email Events (Opens, delivery tracking)
        Schema::create('email_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_recipient_id')->constrained('campaign_recipients')->cascadeOnDelete();
            $table->string('event_type'); // open, bounce, delivery, failure
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
        });

        // 13. Job Applications Tracker
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->index();
            $table->string('job_title');
            $table->string('job_url')->nullable();
            $table->string('company_website')->nullable();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->string('hr_contact_name')->nullable();
            $table->string('hr_email')->nullable();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('resume_attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->foreignId('cover_letter_attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->date('application_date')->nullable();
            $table->string('status')->default('Interested'); // Interested, Preparing, Applied, Follow-up Due, Response Received, Interview, Rejected, Offer, Closed
            $table->date('next_follow_up_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 14. Follow-up Reminders
        Schema::create('follow_up_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_application_id')->nullable()->constrained()->nullOnDelete();
            $table->date('reminder_date');
            $table->string('title');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, completed, cancelled, snoozed
            $table->timestamps();
        });

        // 15. Settings & Key-Value configurations
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // 16. Activity Logs
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // contact_created, campaign_sent, import_completed, reply_received, etc.
            $table->string('description');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('follow_up_reminders');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('email_events');
        Schema::dropIfExists('email_replies');
        Schema::dropIfExists('email_accounts');
        Schema::dropIfExists('campaign_recipients');
        Schema::dropIfExists('campaign_categories');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('contact_import_rows');
        Schema::dropIfExists('contact_imports');
        Schema::dropIfExists('category_contact');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('categories');
    }
};

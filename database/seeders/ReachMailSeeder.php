<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Contact;
use App\Models\EmailTemplate;
use App\Models\Attachment;
use App\Models\JobApplication;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Setting;
use App\Models\ActivityLog;

class ReachMailSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Initial Categories
        $catChennai = Category::create([
            'name' => 'Chennai HR & Tech',
            'slug' => 'chennai-hr-tech',
            'description' => 'Target recruiters and hiring managers in Chennai.',
            'color' => '#0f766e',
        ]);

        $catBangalore = Category::create([
            'name' => 'Bangalore Laravel Startups',
            'slug' => 'bangalore-laravel-startups',
            'description' => 'Fast-growing startups hiring Laravel and full stack developers.',
            'color' => '#2563eb',
        ]);

        $catRecruiters = Category::create([
            'name' => 'Tech Recruiters',
            'slug' => 'tech-recruiters',
            'description' => 'Independent and agency technical recruiters.',
            'color' => '#7c3aed',
        ]);

        // 2. Default Resume
        $sampleResume = Attachment::create([
            'display_name' => 'Primary Software Engineer Resume (2026)',
            'file_name' => 'Alex_Rivera_Resume.pdf',
            'file_path' => 'resumes/sample_resume.pdf',
            'file_type' => 'resume',
            'mime_type' => 'application/pdf',
            'file_size' => 142850,
            'is_default_resume' => true,
            'description' => 'Standard resume highlighting Laravel, PHP, and modern web application development.',
        ]);

        // 3. Email Templates
        $templateJob = EmailTemplate::create([
            'name' => 'Direct Job Application - Software Engineer',
            'category' => 'Job Application',
            'subject' => 'Application for {{job_title}} - {{my_name}}',
            'body_html' => "<p>Dear {{first_name}},</p>\n<p>I hope this email finds you well. I am reaching out to express my enthusiastic interest in engineering opportunities at <strong>{{company_name}}</strong>, specifically regarding the {{job_title}} role.</p>\n<p>With a solid track record in developing robust backend applications using PHP and Laravel, I am confident in my ability to make an immediate impact on your team's upcoming initiatives.</p>\n<p>I have attached my resume for your review. I would welcome the opportunity to discuss how my skill set aligns with your current priorities.</p>\n<p>Thank you for your time and consideration.</p>\n<p>Best regards,<br>{{my_name}}<br>{{my_email}}</p>",
            'default_attachment_id' => $sampleResume->id,
            'is_favorite' => true,
        ]);

        $templateEnquiry = EmailTemplate::create([
            'name' => 'HR Exploratory Enquiry',
            'category' => 'HR Enquiry',
            'subject' => 'Exploratory Engineering Enquiry - {{my_name}}',
            'body_html' => "<p>Hi {{first_name}},</p>\n<p>I have been closely following the impressive work {{company_name}} is doing in your sector.</p>\n<p>I am an experienced software engineer specializing in scalable web systems. Are you currently considering full-time developers for your team in {{city}}?</p>\n<p>I would love to connect briefly or share my portfolio if you have openings on the horizon.</p>\n<p>Warm regards,<br>{{my_name}}</p>",
            'is_favorite' => false,
        ]);

        // 4. Sample Professional Contacts
        $contact1 = Contact::create([
            'email' => 'sarah.jenkins@nexustech.io',
            'normalized_email' => 'sarah.jenkins@nexustech.io',
            'full_name' => 'Sarah Jenkins',
            'first_name' => 'Sarah',
            'last_name' => 'Jenkins',
            'company_name' => 'Nexus Technologies',
            'job_title' => 'Head of Talent Acquisition',
            'company_website' => 'https://nexustech.io',
            'city' => 'Chennai',
            'source' => 'LinkedIn Research',
            'status' => 'Ready to Contact',
            'date_collected' => now()->subDays(5),
            'notes' => 'Expanding their engineering team for a new product launch.',
        ]);
        $contact1->categories()->sync([$catChennai->id, $catRecruiters->id]);

        $contact2 = Contact::create([
            'email' => 'arjun.mehta@hypercloud.in',
            'normalized_email' => 'arjun.mehta@hypercloud.in',
            'full_name' => 'Arjun Mehta',
            'first_name' => 'Arjun',
            'last_name' => 'Mehta',
            'company_name' => 'HyperCloud Systems',
            'job_title' => 'Engineering Manager',
            'company_website' => 'https://hypercloud.in',
            'city' => 'Bangalore',
            'source' => 'Direct Outreach',
            'status' => 'Contacted',
            'last_contacted_at' => now()->subDays(2),
            'date_collected' => now()->subDays(8),
        ]);
        $contact2->categories()->sync([$catBangalore->id]);

        $contact3 = Contact::create([
            'email' => 'priya.sharma@finflow.co',
            'normalized_email' => 'priya.sharma@finflow.co',
            'full_name' => 'Priya Sharma',
            'first_name' => 'Priya',
            'last_name' => 'Sharma',
            'company_name' => 'FinFlow Global',
            'job_title' => 'Technical Recruiter',
            'company_website' => 'https://finflow.co',
            'city' => 'Chennai',
            'source' => 'Campus Alumni Network',
            'status' => 'Replied',
            'reply_status' => 'Replied',
            'last_contacted_at' => now()->subDays(4),
        ]);
        $contact3->categories()->sync([$catChennai->id]);

        // 5. Sample Job Application
        JobApplication::create([
            'company_name' => 'Nexus Technologies',
            'job_title' => 'Senior Laravel Developer',
            'job_url' => 'https://nexustech.io/careers/senior-laravel-developer',
            'company_website' => 'https://nexustech.io',
            'contact_id' => $contact1->id,
            'hr_contact_name' => 'Sarah Jenkins',
            'hr_email' => 'sarah.jenkins@nexustech.io',
            'resume_attachment_id' => $sampleResume->id,
            'application_date' => now()->subDays(2),
            'status' => 'Applied',
            'next_follow_up_date' => now()->addDays(5),
            'notes' => 'Sent customized cover letter and tailored resume.',
        ]);

        // 6. Settings Initialization
        Setting::set('sender_name', 'Alex Rivera');
        Setting::set('sender_email', 'alex.rivera@example.com');
        Setting::set('daily_sending_limit', 150);

        ActivityLog::log('system_initialized', 'ReachMail database seeded with initial templates and structure.');
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Contact;
use App\Models\Category;
use App\Models\EmailTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReachMailCoreTest extends TestCase
{
    public function test_dashboard_renders_successfully(): void
    {
        $user = \App\Models\User::first() ?? \App\Models\User::create([
            'name' => 'Test Owner',
            'email' => 'owner@reachmail.local',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Overview');
        $response->assertSee('Total Contacts');
    }

    public function test_contacts_can_be_created_with_normalization(): void
    {
        $uniqueEmail = 'TEST.User.' . uniqid() . '@AcmeCorp.com';
        $contact = Contact::create([
            'email' => $uniqueEmail,
            'normalized_email' => Contact::normalizeEmail($uniqueEmail),
            'full_name' => 'Test User',
            'company_name' => 'Acme Corp',
            'status' => 'Ready to Contact',
            'do_not_contact' => false,
        ]);

        $this->assertEquals(strtolower($uniqueEmail), $contact->normalized_email);
        $this->assertDatabaseHas('contacts', [
            'normalized_email' => strtolower($uniqueEmail),
        ]);
    }

    public function test_template_personalization_replacement(): void
    {
        $template = EmailTemplate::create([
            'name' => 'Test Template',
            'category' => 'Job Application',
            'subject' => 'Application for {{job_title}} at {{company_name}}',
            'body_html' => '<p>Hello {{first_name}}, I want to work at {{company_name}}.</p>',
        ]);

        $subject = str_replace(['{{job_title}}', '{{company_name}}'], ['Developer', 'Acme'], $template->subject);
        $this->assertEquals('Application for Developer at Acme', $subject);
    }
}

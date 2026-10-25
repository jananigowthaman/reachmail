<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\EmailReplyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AuthController;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Workspace Routes (Single-User Owner Workspace)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Contacts & Import Wizard
    Route::get('/contacts/import', [ContactController::class, 'importWizard'])->name('contacts.import');
    Route::post('/contacts/import/upload', [ContactController::class, 'handleUpload'])->name('contacts.import.upload');
    Route::post('/contacts/import/process', [ContactController::class, 'processImport'])->name('contacts.import.process');
    Route::get('/contacts/export', [ContactController::class, 'export'])->name('contacts.export');
    Route::post('/contacts/bulk-action', [ContactController::class, 'bulkAction'])->name('contacts.bulkAction');
    Route::resource('contacts', ContactController::class);

    // Categories
    Route::resource('categories', CategoryController::class);

    // Email Templates
    Route::post('/templates/{template}/duplicate', [EmailTemplateController::class, 'duplicate'])->name('templates.duplicate');
    Route::resource('templates', EmailTemplateController::class);

    // Resume & File Management
    Route::post('/resumes/{attachment}/set-default', [AttachmentController::class, 'setDefaultResume'])->name('resumes.setDefault');
    Route::get('/resumes/{attachment}/download', [AttachmentController::class, 'download'])->name('resumes.download');
    Route::resource('resumes', AttachmentController::class);

    // Campaigns & Campaign Wizard
    Route::get('/campaigns/create/step2', [CampaignController::class, 'createStep2'])->name('campaigns.create.step2');
    Route::post('/campaigns/{campaign}/send-test', [CampaignController::class, 'sendTest'])->name('campaigns.sendTest');
    Route::post('/campaigns/{campaign}/launch', [CampaignController::class, 'launchNow'])->name('campaigns.launch');
    Route::post('/campaigns/{campaign}/cancel', [CampaignController::class, 'cancel'])->name('campaigns.cancel');
    Route::resource('campaigns', CampaignController::class);

    // Job Applications
    Route::resource('applications', JobApplicationController::class);

    // Replies & Inbox
    Route::post('/replies/sync', [EmailReplyController::class, 'syncNow'])->name('replies.sync');
    Route::resource('replies', EmailReplyController::class)->only(['index', 'show', 'update']);

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Settings & OAuth Connection
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/google-credentials', [SettingController::class, 'updateGoogleCredentials'])->name('settings.google.update');
    Route::get('/settings/oauth/gmail/connect', [SettingController::class, 'connectGmail'])->name('settings.oauth.connect');
    Route::get('/settings/oauth/gmail/callback', [SettingController::class, 'gmailCallback'])->name('settings.oauth.callback');
    Route::post('/settings/oauth/gmail/disconnect', [SettingController::class, 'disconnectGmail'])->name('settings.oauth.disconnect');
});

// Public tracking routes (safe transparent tracking pixel)
Route::get('/track/open/{token}.png', [\App\Http\Controllers\TrackingController::class, 'trackOpen'])->name('track.open');

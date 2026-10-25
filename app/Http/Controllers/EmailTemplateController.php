<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\Attachment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailTemplate::query();

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $templates = $query->latest()->get();
        return view('templates.index', compact('templates'));
    }

    public function create()
    {
        $attachments = Attachment::all();
        return view('templates.create', compact('attachments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'default_attachment_id' => 'nullable|exists:attachments,id',
            'is_favorite' => 'nullable|boolean',
        ]);

        $validated['is_favorite'] = $request->boolean('is_favorite');

        $template = EmailTemplate::create($validated);

        ActivityLog::log('template_created', "Created email template '{$template->name}'", $template);

        return redirect()->route('templates.index')->with('success', 'Email template created successfully.');
    }

    public function edit(EmailTemplate $template)
    {
        $attachments = Attachment::all();
        return view('templates.edit', compact('template', 'attachments'));
    }

    public function update(Request $request, EmailTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'default_attachment_id' => 'nullable|exists:attachments,id',
            'is_favorite' => 'nullable|boolean',
        ]);

        $validated['is_favorite'] = $request->boolean('is_favorite');

        $template->update($validated);

        ActivityLog::log('template_updated', "Updated email template '{$template->name}'", $template);

        return redirect()->route('templates.index')->with('success', 'Email template updated successfully.');
    }

    public function duplicate(EmailTemplate $template)
    {
        $clone = $template->replicate();
        $clone->name = $template->name . ' (Copy)';
        $clone->save();

        ActivityLog::log('template_duplicated', "Duplicated template '{$template->name}'", $clone);

        return redirect()->route('templates.edit', $clone)->with('success', 'Template duplicated successfully. You can edit it now.');
    }

    public function destroy(EmailTemplate $template)
    {
        $name = $template->name;
        $template->delete();

        ActivityLog::log('template_deleted', "Deleted template '{$name}'");

        return redirect()->route('templates.index')->with('success', "Template '{$name}' deleted.");
    }
}

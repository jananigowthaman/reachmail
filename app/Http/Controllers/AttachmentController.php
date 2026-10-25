<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function index()
    {
        $resumes = Attachment::where('file_type', 'resume')->latest()->get();
        $coverLetters = Attachment::where('file_type', 'cover_letter')->latest()->get();
        $others = Attachment::whereNotIn('file_type', ['resume', 'cover_letter'])->latest()->get();

        return view('resumes.index', compact('resumes', 'coverLetters', 'others'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,docx,doc|max:10240', // 10MB
            'display_name' => 'required|string|max:255',
            'file_type' => 'required|string',
            'description' => 'nullable|string|max:500',
            'is_default_resume' => 'nullable|boolean',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $path = $file->store('resumes');

        $isDefault = $request->boolean('is_default_resume');

        // If setting as default resume, clear any existing default
        if ($isDefault && $validated['file_type'] === 'resume') {
            Attachment::where('is_default_resume', true)->update(['is_default_resume' => false]);
        }

        $attachment = Attachment::create([
            'display_name' => $validated['display_name'],
            'file_name' => $originalName,
            'file_path' => $path,
            'file_type' => $validated['file_type'],
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'is_default_resume' => $isDefault,
            'description' => $validated['description'] ?? null,
        ]);

        ActivityLog::log('file_uploaded', "Uploaded document {$attachment->display_name}", $attachment);

        return redirect()->route('resumes.index')->with('success', 'Document uploaded successfully.');
    }

    public function setDefaultResume(Attachment $attachment)
    {
        Attachment::where('is_default_resume', true)->update(['is_default_resume' => false]);
        $attachment->update(['is_default_resume' => true]);

        ActivityLog::log('default_resume_set', "Set {$attachment->display_name} as default resume", $attachment);

        return back()->with('success', "{$attachment->display_name} is now your default resume.");
    }

    public function download(Attachment $attachment)
    {
        if (!Storage::exists($attachment->file_path)) {
            return back()->with('error', 'The requested file could not be found.');
        }

        return Storage::download($attachment->file_path, $attachment->file_name);
    }

    public function destroy(Attachment $attachment)
    {
        if (Storage::exists($attachment->file_path)) {
            Storage::delete($attachment->file_path);
        }

        $name = $attachment->display_name;
        $attachment->delete();

        ActivityLog::log('file_deleted', "Deleted file {$name}");

        return redirect()->route('resumes.index')->with('success', "File {$name} removed.");
    }
}

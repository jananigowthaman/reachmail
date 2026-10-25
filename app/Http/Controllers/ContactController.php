<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Category;
use App\Models\ActivityLog;
use App\Models\ContactImport;
use App\Models\ContactImportRow;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::with('categories');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('job_title', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($categoryId = $request->input('category_id')) {
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        if ($request->filled('do_not_contact')) {
            $query->where('do_not_contact', $request->boolean('do_not_contact'));
        }

        $contacts = $query->latest()->paginate(25)->withQueryString();
        $categories = Category::withCount('contacts')->get();

        return view('contacts.index', compact('contacts', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('contacts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'full_name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'company_website' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'city' => 'nullable|string|max:100',
            'source' => 'nullable|string|max:100',
            'status' => 'required|string',
            'do_not_contact' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'categories' => 'nullable|array',
        ]);

        $normalized = Contact::normalizeEmail($validated['email']);

        $existing = Contact::where('normalized_email', $normalized)->first();
        if ($existing) {
            return back()->withInput()->with('error', "A contact with the email '{$validated['email']}' already exists.");
        }

        $validated['normalized_email'] = $normalized;
        $validated['do_not_contact'] = $request->boolean('do_not_contact');

        $contact = Contact::create($validated);

        if (!empty($validated['categories'])) {
            $contact->categories()->sync($validated['categories']);
        }

        ActivityLog::log('contact_created', "Created contact {$contact->display_name} ({$contact->email})", $contact);

        return redirect()->route('contacts.index')->with('success', 'Contact created successfully.');
    }

    public function show(Contact $contact)
    {
        $contact->load(['categories', 'campaignRecipients.campaign', 'jobApplications', 'emailReplies', 'followUpReminders']);
        return view('contacts.show', compact('contact'));
    }

    public function edit(Contact $contact)
    {
        $categories = Category::all();
        $selectedCategories = $contact->categories->pluck('id')->toArray();
        return view('contacts.edit', compact('contact', 'categories', 'selectedCategories'));
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'full_name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'company_website' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'city' => 'nullable|string|max:100',
            'source' => 'nullable|string|max:100',
            'status' => 'required|string',
            'do_not_contact' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'categories' => 'nullable|array',
        ]);

        $normalized = Contact::normalizeEmail($validated['email']);

        $existing = Contact::where('normalized_email', $normalized)
            ->where('id', '!=', $contact->id)
            ->first();

        if ($existing) {
            return back()->withInput()->with('error', "Another contact already exists with the email '{$validated['email']}'.");
        }

        $validated['normalized_email'] = $normalized;
        $validated['do_not_contact'] = $request->boolean('do_not_contact');

        $contact->update($validated);

        if (isset($validated['categories'])) {
            $contact->categories()->sync($validated['categories']);
        } else {
            $contact->categories()->detach();
        }

        ActivityLog::log('contact_updated', "Updated contact details for {$contact->display_name}", $contact);

        return redirect()->route('contacts.index')->with('success', 'Contact updated successfully.');
    }

    public function destroy(Contact $contact)
    {
        $name = $contact->display_name;
        $contact->delete();

        ActivityLog::log('contact_deleted', "Deleted contact {$name}");

        return redirect()->route('contacts.index')->with('success', "Contact {$name} was removed.");
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->input('selected_contacts', []);
        $action = $request->input('bulk_action');

        if (empty($ids)) {
            return back()->with('error', 'No contacts selected for bulk action.');
        }

        if ($action === 'delete') {
            Contact::whereIn('id', $ids)->delete();
            return back()->with('success', count($ids) . ' contacts deleted successfully.');
        } elseif ($action === 'mark_dnc') {
            Contact::whereIn('id', $ids)->update(['do_not_contact' => true, 'status' => 'Do Not Contact']);
            return back()->with('success', count($ids) . ' contacts marked as Do Not Contact.');
        } elseif ($action === 'assign_category') {
            $categoryId = $request->input('bulk_category_id');
            if ($categoryId) {
                $category = Category::find($categoryId);
                if ($category) {
                    $category->contacts()->syncWithoutDetaching($ids);
                    return back()->with('success', count($ids) . " contacts assigned to {$category->name}.");
                }
            }
        }

        return back();
    }

    // Step-by-Step Import Wizard
    public function importWizard()
    {
        $categories = Category::all();
        return view('contacts.import', compact('categories'));
    }

    public function handleUpload(Request $request)
    {
        $request->validate([
            'spreadsheet' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240', // 10MB
        ]);

        $file = $request->file('spreadsheet');
        $originalName = $file->getClientOriginalName();
        $storedPath = $file->store('imports');

        // Parse preview rows using maatwebsite/excel or basic CSV parsing
        $fullPath = storage_path('app/' . $storedPath);
        $rows = [];

        if (Str::endsWith(strtolower($originalName), ['.xlsx', '.xls'])) {
            $sheets = Excel::toArray([], $fullPath);
            $rawRows = $sheets[0] ?? [];
        } else {
            $rawRows = array_map('str_getcsv', file($fullPath));
        }

        if (empty($rawRows)) {
            return back()->with('error', 'The uploaded spreadsheet is empty.');
        }

        $headers = array_filter(array_shift($rawRows));
        $previewRows = array_slice($rawRows, 0, 15);

        // Store temp preview state in session
        session([
            'import_file_path' => $storedPath,
            'import_original_filename' => $originalName,
            'import_headers' => array_values($headers),
            'import_preview_rows' => $previewRows,
            'import_total_rows' => count($rawRows),
        ]);

        return redirect()->route('contacts.import')->with('step', 2);
    }

    public function processImport(Request $request)
    {
        $filePath = session('import_file_path');
        if (!$filePath || !file_exists(storage_path('app/' . $filePath))) {
            return redirect()->route('contacts.import')->with('error', 'Import session expired. Please re-upload your file.');
        }

        $mappings = $request->input('mapping', []);
        $targetCategoryId = $request->input('target_category_id');
        $duplicateMode = $request->input('duplicate_mode', 'skip'); // skip or update
        $defaultStatus = $request->input('default_status', 'Ready to Contact');
        $sourceName = $request->input('source_name', 'Spreadsheet Import');

        $fullPath = storage_path('app/' . $filePath);

        if (Str::endsWith(strtolower($filePath), ['.xlsx', '.xls'])) {
            $sheets = Excel::toArray([], $fullPath);
            $rows = $sheets[0] ?? [];
        } else {
            $rows = array_map('str_getcsv', file($fullPath));
        }

        $headers = array_shift($rows);

        $import = ContactImport::create([
            'file_name' => $filePath,
            'original_filename' => session('import_original_filename', 'imported_contacts'),
            'total_rows' => count($rows),
            'duplicate_mode' => $duplicateMode,
            'column_mapping' => $mappings,
            'target_category_id' => $targetCategoryId,
            'status' => 'processing',
        ]);

        $importedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $invalidCount = 0;

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $data = [];

            foreach ($mappings as $headerKey => $appField) {
                if ($appField && isset($row[$headerKey])) {
                    $data[$appField] = trim((string)$row[$headerKey]);
                }
            }

            if (empty($data['email'])) {
                $invalidCount++;
                ContactImportRow::create([
                    'contact_import_id' => $import->id,
                    'row_number' => $rowNum,
                    'raw_data' => $row,
                    'status' => 'invalid',
                    'reason' => 'Missing email address',
                ]);
                continue;
            }

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $invalidCount++;
                ContactImportRow::create([
                    'contact_import_id' => $import->id,
                    'row_number' => $rowNum,
                    'raw_data' => $row,
                    'status' => 'invalid',
                    'reason' => 'Invalid email format',
                ]);
                continue;
            }

            $normalized = Contact::normalizeEmail($data['email']);
            $existing = Contact::where('normalized_email', $normalized)->first();

            if ($existing) {
                if ($duplicateMode === 'skip') {
                    $skippedCount++;
                    ContactImportRow::create([
                        'contact_import_id' => $import->id,
                        'row_number' => $rowNum,
                        'raw_data' => $row,
                        'status' => 'skipped_duplicate',
                        'reason' => 'Duplicate email already in database',
                    ]);
                    continue;
                } else {
                    // Update existing
                    $existing->update(array_filter([
                        'full_name' => $data['full_name'] ?? $existing->full_name,
                        'company_name' => $data['company_name'] ?? $existing->company_name,
                        'job_title' => $data['job_title'] ?? $existing->job_title,
                        'company_website' => $data['company_website'] ?? $existing->company_website,
                        'city' => $data['city'] ?? $existing->city,
                    ]));

                    if ($targetCategoryId) {
                        $existing->categories()->syncWithoutDetaching([$targetCategoryId]);
                    }

                    $updatedCount++;
                    ContactImportRow::create([
                        'contact_import_id' => $import->id,
                        'row_number' => $rowNum,
                        'raw_data' => $row,
                        'status' => 'updated',
                    ]);
                    continue;
                }
            }

            // Create brand new contact
            $contact = Contact::create([
                'email' => $data['email'],
                'normalized_email' => $normalized,
                'full_name' => $data['full_name'] ?? null,
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'job_title' => $data['job_title'] ?? null,
                'company_website' => $data['company_website'] ?? null,
                'city' => $data['city'] ?? null,
                'source' => $sourceName,
                'status' => $defaultStatus,
                'date_collected' => now(),
            ]);

            if ($targetCategoryId) {
                $contact->categories()->sync([$targetCategoryId]);
            }

            $importedCount++;
            ContactImportRow::create([
                'contact_import_id' => $import->id,
                'row_number' => $rowNum,
                'raw_data' => $row,
                'status' => 'imported',
            ]);
        }

        $import->update([
            'imported_count' => $importedCount,
            'updated_count' => $updatedCount,
            'skipped_count' => $skippedCount,
            'invalid_count' => $invalidCount,
            'status' => 'completed',
        ]);

        ActivityLog::log('contacts_imported', "Imported {$importedCount} contacts from {$import->original_filename}", $import);

        // Clear import session
        session()->forget(['import_file_path', 'import_original_filename', 'import_headers', 'import_preview_rows', 'import_total_rows']);

        return redirect()->route('contacts.index')->with('success', "Import finished: {$importedCount} imported, {$updatedCount} updated, {$skippedCount} skipped duplicates, {$invalidCount} invalid.");
    }

    public function export(Request $request)
    {
        $contacts = Contact::with('categories')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="reachmail_contacts_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($contacts) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Full Name', 'Email', 'Company', 'Designation', 'Website', 'City', 'Status', 'Categories', 'Notes']);

            foreach ($contacts as $c) {
                fputcsv($file, [
                    $c->display_name,
                    $c->email,
                    $c->company_name,
                    $c->job_title,
                    $c->company_website,
                    $c->city,
                    $c->status,
                    $c->categories->pluck('name')->implode(', '),
                    $c->notes,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

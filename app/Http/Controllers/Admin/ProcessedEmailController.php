<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProcessedEmail;
use Illuminate\Http\Request;

class ProcessedEmailController extends Controller
{
    /**
     * Display a listing of processed emails.
     */
    public function index(Request $request)
    {
        $query = ProcessedEmail::with('inbox');
        
        // Filter by inbox
        if ($request->has('inbox_id') && $request->inbox_id) {
            $query->where('email_inbox_id', $request->inbox_id);
        }
        
        // Filter by reply status
        if ($request->has('replied')) {
            $query->where('was_replied', $request->replied === 'yes');
        }
        
        // Search by subject or sender
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('from_email', 'like', "%{$search}%")
                  ->orWhere('from_name', 'like', "%{$search}%");
            });
        }
        
        $emails = $query->latest()->paginate(15);
        
        return view('admin.processed-emails.index', compact('emails'));
    }
    
    /**
     * Display the specified processed email.
     */
    public function show(ProcessedEmail $processedEmail)
    {
        return view('admin.processed-emails.show', compact('processedEmail'));
    }
} 
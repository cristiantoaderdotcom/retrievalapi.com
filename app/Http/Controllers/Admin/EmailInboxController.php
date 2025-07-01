<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessEmailInbox;
use App\Models\EmailInbox;
use DirectoryTree\ImapEngine\Mailbox;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmailInboxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inboxes = EmailInbox::latest()->paginate(10);
        
        return view('admin.email-inboxes.index', compact('inboxes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.email-inboxes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|integer',
            'encryption' => 'required|string|in:ssl,tls,starttls',
            'username' => 'required|string|max:255|email',
            'password' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()
                ->route('email-inboxes.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        // Test the connection before saving
        try {
            $mailbox = new Mailbox([
                'host' => $request->host,
                'port' => $request->port,
                'encryption' => $request->encryption,
                'validate_cert' => $request->has('validate_cert'),
                'username' => $request->username,
                'password' => $request->password,
            ]);
            
            // Try to get the inbox to test the connection
            $mailbox->inbox();
            
        } catch (Exception $e) {
            return redirect()
                ->route('email-inboxes.create')
                ->withErrors(['connection' => 'Failed to connect to the IMAP server: ' . $e->getMessage()])
                ->withInput();
        }
        
        // Save the inbox configuration
        $inbox = EmailInbox::create([
            'name' => $request->name,
            'host' => $request->host,
            'port' => $request->port,
            'encryption' => $request->encryption,
            'validate_cert' => $request->has('validate_cert'),
            'username' => $request->username,
            'password' => $request->password,
            'is_active' => $request->has('is_active'),
        ]);
        
        return redirect()
            ->route('email-inboxes.index')
            ->with('success', 'Email inbox created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(EmailInbox $emailInbox)
    {
        // Load the processed emails relationship
        $emailInbox->load('processedEmails');
        
        return view('admin.email-inboxes.show', compact('emailInbox'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmailInbox $emailInbox)
    {
        return view('admin.email-inboxes.edit', compact('emailInbox'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmailInbox $emailInbox)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|integer',
            'encryption' => 'required|string|in:ssl,tls,starttls',
            'username' => 'required|string|max:255|email',
            'password' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()
                ->route('email-inboxes.edit', $emailInbox)
                ->withErrors($validator)
                ->withInput();
        }
        
        // Only test the connection if credentials changed
        if ($request->host !== $emailInbox->host || 
            $request->port !== $emailInbox->port || 
            $request->encryption !== $emailInbox->encryption || 
            $request->username !== $emailInbox->username || 
            ($request->password && $request->password !== $emailInbox->password)) {
                
            try {
                $mailbox = new Mailbox([
                    'host' => $request->host,
                    'port' => $request->port,
                    'encryption' => $request->encryption,
                    'validate_cert' => $request->has('validate_cert'),
                    'username' => $request->username,
                    'password' => $request->password ?: $emailInbox->password,
                ]);
                
                // Try to get the inbox to test the connection
                $mailbox->inbox();
                
            } catch (Exception $e) {
                return redirect()
                    ->route('email-inboxes.edit', $emailInbox)
                    ->withErrors(['connection' => 'Failed to connect to the IMAP server: ' . $e->getMessage()])
                    ->withInput();
            }
        }
        
        // Update the inbox configuration
        $emailInbox->update([
            'name' => $request->name,
            'host' => $request->host,
            'port' => $request->port,
            'encryption' => $request->encryption,
            'validate_cert' => $request->has('validate_cert'),
            'username' => $request->username,
            'is_active' => $request->has('is_active'),
        ]);
        
        // Only update password if provided
        if ($request->password) {
            $emailInbox->update(['password' => $request->password]);
        }
        
        return redirect()
            ->route('email-inboxes.index')
            ->with('success', 'Email inbox updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailInbox $emailInbox)
    {
        $emailInbox->delete();
        
        return redirect()
            ->route('email-inboxes.index')
            ->with('success', 'Email inbox deleted successfully');
    }
    
    /**
     * Process the specified inbox immediately.
     */
    public function process(EmailInbox $emailInbox)
    {
        ProcessEmailInbox::dispatch($emailInbox);
        
        return redirect()
            ->route('email-inboxes.show', $emailInbox)
            ->with('success', 'Email inbox processing job dispatched');
    }
} 
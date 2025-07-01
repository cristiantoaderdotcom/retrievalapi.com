<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Email - {{ $processedEmail->subject }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1>View Email</h1>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('processed-emails.index') }}" class="btn btn-secondary">Back to List</a>
                <a href="{{ route('email-inboxes.index') }}" class="btn btn-primary">Manage Inboxes</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>Email Details</h5>
                    <span class="badge {{ $processedEmail->was_replied ? 'bg-success' : 'bg-warning' }}">
                        {{ $processedEmail->was_replied ? 'Replied' : 'Pending' }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Inbox</dt>
                    <dd class="col-sm-9">{{ $processedEmail->inbox->name }} ({{ $processedEmail->inbox->username }})</dd>
                    
                    <dt class="col-sm-3">Message ID</dt>
                    <dd class="col-sm-9">{{ $processedEmail->message_id }}</dd>
                    
                    <dt class="col-sm-3">Subject</dt>
                    <dd class="col-sm-9">{{ $processedEmail->subject }}</dd>
                    
                    <dt class="col-sm-3">From</dt>
                    <dd class="col-sm-9">
                        {{ $processedEmail->from_name }} &lt;{{ $processedEmail->from_email }}&gt;
                    </dd>
                    
                    <dt class="col-sm-3">Received</dt>
                    <dd class="col-sm-9">{{ $processedEmail->created_at->format('Y-m-d H:i:s') }}</dd>
                    
                    @if($processedEmail->was_replied)
                        <dt class="col-sm-3">Replied At</dt>
                        <dd class="col-sm-9">{{ $processedEmail->replied_at->format('Y-m-d H:i:s') }}</dd>
                        
                        <dt class="col-sm-3">Tokens Used</dt>
                        <dd class="col-sm-9">{{ $processedEmail->total_tokens }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Original Message</h5>
                    </div>
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        <pre style="white-space: pre-wrap;">{{ $processedEmail->original_message }}</pre>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>AI Response</h5>
                    </div>
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        @if($processedEmail->was_replied)
                            <pre style="white-space: pre-wrap;">{{ $processedEmail->ai_response }}</pre>
                        @else
                            <div class="alert alert-warning">
                                This email has not been replied to yet. The response will be generated soon.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        
    </div>
</body>
</html> 
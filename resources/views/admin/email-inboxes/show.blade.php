<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Email Inbox - {{ $emailInbox->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1>{{ $emailInbox->name }}</h1>
                <p class="text-muted">{{ $emailInbox->username }}</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('email-inboxes.index') }}" class="btn btn-secondary">Back to List</a>
                <a href="{{ route('email-inboxes.edit', $emailInbox) }}" class="btn btn-warning">Edit</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Inbox Details</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Name</dt>
                            <dd class="col-sm-8">{{ $emailInbox->name }}</dd>
                            
                            <dt class="col-sm-4">Host</dt>
                            <dd class="col-sm-8">{{ $emailInbox->host }}</dd>
                            
                            <dt class="col-sm-4">Port</dt>
                            <dd class="col-sm-8">{{ $emailInbox->port }}</dd>
                            
                            <dt class="col-sm-4">Encryption</dt>
                            <dd class="col-sm-8">{{ strtoupper($emailInbox->encryption) }}</dd>
                            
                            <dt class="col-sm-4">Username</dt>
                            <dd class="col-sm-8">{{ $emailInbox->username }}</dd>
                            
                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8">
                                @if($emailInbox->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </dd>
                            
                            <dt class="col-sm-4">Created</dt>
                            <dd class="col-sm-8">{{ $emailInbox->created_at->format('Y-m-d H:i:s') }}</dd>
                            
                            <dt class="col-sm-4">Last Updated</dt>
                            <dd class="col-sm-8">{{ $emailInbox->updated_at->format('Y-m-d H:i:s') }}</dd>
                        </dl>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <form action="{{ route('email-inboxes.process', $emailInbox) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg w-100">Process Inbox Now</button>
                            </form>
                            
                            <a href="{{ route('processed-emails.index') }}?inbox_id={{ $emailInbox->id }}" class="btn btn-info btn-lg">View Processed Emails</a>
                            
                            <form action="{{ route('email-inboxes.destroy', $emailInbox) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this inbox? This will also delete all processed emails.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-lg w-100">Delete Inbox</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h3>{{ $emailInbox->processedEmails->count() }}</h3>
                                        <p class="text-muted">Total Emails Processed</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h3>{{ $emailInbox->processedEmails->where('was_replied', true)->count() }}</h3>
                                        <p class="text-muted">Emails Replied</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h3>{{ $emailInbox->processedEmails->where('was_replied', false)->count() }}</h3>
                                        <p class="text-muted">Pending Replies</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h3>{{ $emailInbox->processedEmails->sum('total_tokens') }}</h3>
                                        <p class="text-muted">Total Tokens Used</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Processed Emails</h5>
                    </div>
                    <div class="card-body">
                        @if($emailInbox->processedEmails->count() > 0)
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>From</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($emailInbox->processedEmails->sortByDesc('created_at')->take(5) as $email)
                                        <tr>
                                            <td>{{ $email->created_at->format('Y-m-d') }}</td>
                                            <td>{{ Str::limit($email->from_email, 20) }}</td>
                                            <td>{{ Str::limit($email->subject, 25) }}</td>
                                            <td>
                                                @if($email->was_replied)
                                                    <span class="badge bg-success">Replied</span>
                                                @else
                                                    <span class="badge bg-warning">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="text-end mt-2">
                                <a href="{{ route('processed-emails.index') }}?inbox_id={{ $emailInbox->id }}" class="btn btn-sm btn-primary">View All</a>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                No emails processed yet. Click "Process Inbox Now" to check for new messages.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
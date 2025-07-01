<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processed Emails</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1>Processed Emails</h1>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('email-inboxes.index') }}" class="btn btn-primary">Manage Inboxes</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Filter Emails</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('processed-emails.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="inbox_id" class="form-label">Inbox</label>
                        <select class="form-select" id="inbox_id" name="inbox_id">
                            <option value="">All Inboxes</option>
                            @foreach(\App\Models\EmailInbox::all() as $inbox)
                                <option value="{{ $inbox->id }}" {{ request('inbox_id') == $inbox->id ? 'selected' : '' }}>
                                    {{ $inbox->name }} ({{ $inbox->username }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="replied" class="form-label">Reply Status</label>
                        <select class="form-select" id="replied" name="replied">
                            <option value="">All</option>
                            <option value="yes" {{ request('replied') === 'yes' ? 'selected' : '' }}>Replied</option>
                            <option value="no" {{ request('replied') === 'no' ? 'selected' : '' }}>Not Replied</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Subject or sender...">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Inbox</th>
                            <th>From</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($emails as $email)
                            <tr>
                                <td>{{ $email->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $email->inbox->name }}</td>
                                <td>
                                    {{ $email->from_name }}<br>
                                    <small class="text-muted">{{ $email->from_email }}</small>
                                </td>
                                <td>{{ Str::limit($email->subject, 50) }}</td>
                                <td>
                                    @if($email->was_replied)
                                        <span class="badge bg-success">Replied</span>
                                        <br><small>{{ $email->replied_at->diffForHumans() }}</small>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('processed-emails.show', $email) }}" class="btn btn-sm btn-info">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No processed emails found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $emails->withQueryString()->links() }}
        </div>
    </div>
</body>
</html> 
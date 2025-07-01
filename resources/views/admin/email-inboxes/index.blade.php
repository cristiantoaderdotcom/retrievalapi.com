<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Inboxes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1>Email Inboxes</h1>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('email-inboxes.create') }}" class="btn btn-primary">Add New Inbox</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Host</th>
                            <th>Username</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inboxes as $inbox)
                            <tr>
                                <td>{{ $inbox->name }}</td>
                                <td>{{ $inbox->host }}</td>
                                <td>{{ $inbox->username }}</td>
                                <td>
                                    @if($inbox->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('email-inboxes.show', $inbox) }}" class="btn btn-sm btn-info">View</a>
                                        <a href="{{ route('email-inboxes.edit', $inbox) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <form action="{{ route('email-inboxes.process', $inbox) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">Process Now</button>
                                        </form>
                                        <form action="{{ route('email-inboxes.destroy', $inbox) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this inbox?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No email inboxes found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $inboxes->links() }}
        </div>

    
    </div>
</body>
</html> 
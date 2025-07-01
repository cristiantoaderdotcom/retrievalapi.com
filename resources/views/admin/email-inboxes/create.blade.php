<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Email Inbox</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1>Add New Email Inbox</h1>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('email-inboxes.index') }}" class="btn btn-secondary">Back to List</a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form action="{{ route('email-inboxes.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        <small class="text-muted">A friendly name to identify this inbox</small>
                    </div>

                    <div class="mb-3">
                        <label for="host" class="form-label">Host</label>
                        <input type="text" class="form-control" id="host" name="host" value="{{ old('host') }}" required>
                        <small class="text-muted">e.g., imap.gmail.com, outlook.office365.com</small>
                    </div>

                    <div class="mb-3">
                        <label for="port" class="form-label">Port</label>
                        <input type="number" class="form-control" id="port" name="port" value="{{ old('port', 993) }}" required>
                        <small class="text-muted">993 for SSL, 143 for non-SSL</small>
                    </div>

                    <div class="mb-3">
                        <label for="encryption" class="form-label">Encryption</label>
                        <select class="form-select" id="encryption" name="encryption">
                            <option value="ssl" {{ old('encryption') == 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="tls" {{ old('encryption') == 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="starttls" {{ old('encryption') == 'starttls' ? 'selected' : '' }}>STARTTLS</option>
                        </select>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="validate_cert" name="validate_cert" {{ old('validate_cert') ? 'checked' : '' }}>
                        <label class="form-check-label" for="validate_cert">Validate Certificate</label>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username/Email</label>
                        <input type="email" class="form-control" id="username" name="username" value="{{ old('username') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">For Gmail, you may need to use an <a href="https://support.google.com/accounts/answer/185833" target="_blank">App Password</a></small>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Save Inbox</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 
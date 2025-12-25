<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">Licenser</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('web.projects.index') }}">Projects</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('web.customers.index') }}">Customers</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('web.licenses.index') }}">Licenses</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('web.users.index') }}">Users</a></li>
                </ul>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-dark">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container mt-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        {{ $slot }}
    </main>

    @stack('scripts')
</body>
</html>

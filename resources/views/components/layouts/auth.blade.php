<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Account' }} — NOMA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400&family=Manrope:wght@400;500;600&family=Newsreader:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('auth.css') }}">
    @livewireStyles
</head>
<body class="account-body">
    <header class="account-header">
        <a class="account-mark" href="{{ route('home') }}">NOMA<sup>®</sup></a>
        @auth
            <nav aria-label="Account navigation">
                <a href="{{ url('/dashboard') }}">Account</a>
                @if (auth()->user()->hasVerifiedEmail() && auth()->user()->hasPermission(\App\Domain\Identity\PermissionName::AccessAdmin->value))
                    <a href="{{ url('/admin') }}">Administration</a>
                @endif
                <form method="POST" action="{{ url('/logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </nav>
        @endauth
    </header>

    <main class="account-main">
        @if (session('status'))
            <p class="flash" role="status">{{ session('status') }}</p>
        @endif
        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>

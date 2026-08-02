@extends('layouts.app')

@section('header', 'Dashboard')

@section('content')
<div class="glass-panel mx-auto mt-6 max-w-2xl p-5 text-center animate-fade-in sm:mt-10 sm:p-7">
    <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-taya-navy-100">
        <svg class="w-10 h-10 text-taya-navy-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
        </svg>
    </div>
    <h2 class="mb-3 text-2xl font-bold text-gray-900">Welcome to TAYA</h2>
    <p class="mb-6 leading-relaxed text-gray-600">
        The system is routing you to your dashboard. If you are not redirected automatically, please verify your account role with the system administrator.
    </p>
    <a href="{{ route('dashboard') }}" class="btn-primary">
        Reload Dashboard
    </a>
</div>
@endsection

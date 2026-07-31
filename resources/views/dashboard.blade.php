@extends('layouts.app')

@section('header', 'Dashboard')

@section('content')
<div class="glass-panel p-8 text-center max-w-2xl mx-auto mt-12 animate-fade-in">
    <div class="w-20 h-20 bg-taya-navy-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10 text-taya-navy-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
        </svg>
    </div>
    <h2 class="text-3xl font-bold text-gray-900 mb-4">Welcome to TAYA</h2>
    <p class="text-gray-600 text-lg mb-8 leading-relaxed">
        The system is routing you to your dashboard. If you are not redirected automatically, please verify your account role with the system administrator.
    </p>
    <a href="{{ route('dashboard') }}" class="btn-primary">
        Reload Dashboard
    </a>
</div>
@endsection

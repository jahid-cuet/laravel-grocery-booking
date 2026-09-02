@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto px-4 py-12 space-y-8">

    <!-- Header -->
    <div class="text-center space-y-2">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 to-emerald-400 flex items-center justify-center text-white mx-auto shadow-lg shadow-brand-500/30">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
            </svg>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Sign in to your account</h1>
        <p class="text-xs text-slate-500">Access catalogue browsing or the admin management panel</p>
    </div>

    <!-- 1-Click Fast Demo Login for Evaluators -->
    <div class="bg-gradient-to-br from-brand-50 to-emerald-50 rounded-3xl p-5 border border-brand-100 space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-xs font-extrabold uppercase tracking-wider text-brand-900 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></span>
                Fast 1-Click Demo Login
            </span>
            <span class="text-[10px] font-bold text-brand-700 bg-brand-200/60 px-2 py-0.5 rounded-full">For Evaluators</span>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('login.quick', 'admin') }}" class="p-3 bg-white hover:bg-slate-900 hover:text-white rounded-2xl border border-brand-200 text-left transition-all duration-200 shadow-sm group">
                <span class="block text-xs font-black text-slate-900 group-hover:text-white">Admin Portal</span>
                <span class="block text-[10px] text-slate-500 group-hover:text-slate-300">admin@grocery.com</span>
            </a>
            <a href="{{ route('login.quick', 'user') }}" class="p-3 bg-white hover:bg-brand-600 hover:text-white rounded-2xl border border-brand-200 text-left transition-all duration-200 shadow-sm group">
                <span class="block text-xs font-black text-slate-900 group-hover:text-white">Customer Store</span>
                <span class="block text-[10px] text-slate-500 group-hover:text-brand-100">user@grocery.com</span>
            </a>
        </div>
    </div>

    <!-- Standard Login Form -->
    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-xl space-y-6">
        <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
            @csrf

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@grocery.com" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                @error('email')
                    <p class="text-xs font-bold text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                @error('password')
                    <p class="text-xs font-bold text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember me -->
            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-600 font-semibold">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <span>Remember this device</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3.5 px-4 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-sm shadow-lg shadow-brand-500/25 active:scale-[0.98] transition-all">
                Sign In
            </button>

            <p class="text-center text-xs text-slate-500">
                New customer?
                <a href="{{ route('register') }}" class="font-bold text-brand-700 hover:underline">Create an account</a>
            </p>
        </form>
    </div>

</div>
@endsection

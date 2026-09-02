@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto px-4 py-12 space-y-8">
    <div class="text-center space-y-2">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 to-emerald-400 flex items-center justify-center text-white mx-auto shadow-lg shadow-brand-500/30">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3.75 20.25a6.75 6.75 0 0113.5 0"></path>
            </svg>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Create your account</h1>
        <p class="text-xs text-slate-500">Register as a customer to browse groceries and place bookings.</p>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-xl space-y-6">
        <form method="POST" action="{{ route('register.submit') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Full Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                @error('name')<p class="text-xs font-bold text-rose-600 mt-1.5">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                @error('email')<p class="text-xs font-bold text-rose-600 mt-1.5">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
                <input type="password" id="password" name="password" required minlength="6" autocomplete="new-password" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                @error('password')<p class="text-xs font-bold text-rose-600 mt-1.5">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6" autocomplete="new-password" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
            </div>

            <button type="submit" class="w-full py-3.5 px-4 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-sm shadow-lg shadow-brand-500/25 active:scale-[0.98] transition-all">
                Create Account
            </button>

            <p class="text-center text-xs text-slate-500">
                Already registered?
                <a href="{{ route('login') }}" class="font-bold text-brand-700 hover:underline">Sign in</a>
            </p>
        </form>
    </div>
</div>
@endsection

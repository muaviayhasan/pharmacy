@extends('layouts.app')

@section('title', 'User Profile')
@section('page-title', 'My Profile')

@section('content')
    {{-- Profile summary header --}}
    <div class="bg-surface-container-lowest rounded-xl p-lg border border-outline-variant custom-shadow flex flex-col md:flex-row items-center md:items-start gap-xl relative">
        <div class="h-28 w-28 rounded-2xl bg-primary-container text-on-primary-container flex items-center justify-center text-4xl font-bold uppercase shrink-0">
            {{ \Illuminate\Support\Str::of($user->name)->explode(' ')->take(2)->map(fn ($p) => \Illuminate\Support\Str::substr($p, 0, 1))->implode('') }}
        </div>
        <div class="text-center md:text-left flex-1">
            <div class="flex flex-col md:flex-row md:items-center gap-md mb-xs">
                <h2 class="text-headline-lg text-on-surface font-semibold">{{ $user->name }}</h2>
                <div class="flex gap-2 justify-center">
                    <span class="px-3 py-1 bg-green-100 text-green-700 text-label-sm rounded-full font-bold uppercase flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                        {{ ucfirst($user->status) }}
                    </span>
                    @if ($user->two_factor_enabled)
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 text-label-sm rounded-full font-bold uppercase flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">verified_user</span> 2FA
                        </span>
                    @endif
                </div>
            </div>
            <p class="text-body-md text-on-surface-variant mb-md">
                {{ \Illuminate\Support\Str::headline($user->getRoleNames()->first() ?? 'No role assigned') }}
                at {{ config('app.name') }}.
            </p>
            <div class="flex flex-wrap justify-center md:justify-start gap-lg text-outline">
                <span class="flex items-center gap-sm"><span class="material-symbols-outlined">mail</span><span class="text-body-sm">{{ $user->email }}</span></span>
                <span class="flex items-center gap-sm"><span class="material-symbols-outlined">call</span><span class="text-body-sm">{{ $user->phone ?? '—' }}</span></span>
                <span class="flex items-center gap-sm"><span class="material-symbols-outlined">schedule</span><span class="text-body-sm">Last login: {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</span></span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-lg">
        {{-- Personal information --}}
        <div class="lg:col-span-2 space-y-lg">
            <section class="bg-surface-container-lowest rounded-xl border border-outline-variant custom-shadow overflow-hidden">
                <div class="px-lg py-md bg-surface-container-low border-b border-outline-variant flex items-center justify-between">
                    <h4 class="text-headline-md text-on-surface font-semibold">Personal Information</h4>
                    <span class="material-symbols-outlined text-outline">contact_page</span>
                </div>
                <form method="POST" action="{{ route('profile.update') }}" class="p-lg grid grid-cols-1 md:grid-cols-2 gap-lg">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-sm">
                        <label class="block text-label-md text-on-surface-variant ml-1">Full Name</label>
                        <input name="name" type="text" value="{{ old('name', $user->name) }}"
                               class="w-full border border-outline-variant rounded-lg p-md focus:ring-2 focus:ring-primary focus:border-primary bg-background text-body-md">
                    </div>
                    <div class="space-y-sm">
                        <label class="block text-label-md text-on-surface-variant ml-1">Email Address</label>
                        <input name="email" type="email" value="{{ old('email', $user->email) }}"
                               class="w-full border border-outline-variant rounded-lg p-md focus:ring-2 focus:ring-primary focus:border-primary bg-background text-body-md">
                    </div>
                    <div class="space-y-sm">
                        <label class="block text-label-md text-on-surface-variant ml-1">Phone</label>
                        <input name="phone" type="text" value="{{ old('phone', $user->phone) }}"
                               class="w-full border border-outline-variant rounded-lg p-md focus:ring-2 focus:ring-primary focus:border-primary bg-background text-body-md">
                    </div>
                    <div class="space-y-sm">
                        <label class="block text-label-md text-on-surface-variant ml-1">Role</label>
                        <input type="text" disabled value="{{ \Illuminate\Support\Str::headline($user->getRoleNames()->first() ?? '—') }}"
                               class="w-full border border-outline-variant rounded-lg p-md bg-surface-container-low text-body-md text-outline">
                    </div>
                    <div class="md:col-span-2 flex items-center justify-between bg-surface-container-low p-md rounded-lg border border-outline-variant">
                        <div>
                            <p class="text-label-md text-on-surface">Two-Factor Authentication</p>
                            <p class="text-label-sm text-outline">Require an emailed OTP code at login.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="two_factor_enabled" value="1" class="sr-only peer" @checked(old('two_factor_enabled', $user->two_factor_enabled))>
                            <div class="w-11 h-6 bg-surface-variant rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" class="bg-primary text-on-primary px-lg py-2.5 rounded-lg text-label-md hover:opacity-90 transition-all flex items-center gap-sm">
                            <span class="material-symbols-outlined text-[18px]">save</span> Save Changes
                        </button>
                    </div>
                </form>
            </section>

            {{-- Branch access --}}
            <section class="bg-surface-container-lowest rounded-xl border border-outline-variant custom-shadow overflow-hidden">
                <div class="px-lg py-md bg-surface-container-low border-b border-outline-variant">
                    <h4 class="text-headline-md text-on-surface font-semibold">Branch Access</h4>
                </div>
                <div class="p-lg grid grid-cols-1 sm:grid-cols-2 gap-md">
                    @forelse ($user->branches as $branch)
                        <div class="flex items-center gap-md p-sm bg-surface-container-low rounded-lg">
                            <div class="h-10 w-10 bg-primary-fixed text-on-primary-fixed flex items-center justify-center rounded-lg">
                                <span class="material-symbols-outlined">apartment</span>
                            </div>
                            <div>
                                <p class="text-label-md text-on-surface">{{ $branch->name }}</p>
                                <p class="text-label-sm text-outline uppercase">{{ ucfirst($branch->pivot->access_level) }} access</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-body-sm text-outline">No branches assigned.</p>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- Security --}}
        <div class="space-y-lg">
            <section class="bg-surface-container-lowest rounded-xl border border-outline-variant custom-shadow overflow-hidden">
                <div class="px-lg py-md bg-surface-container-low border-b border-outline-variant">
                    <h4 class="text-headline-md text-on-surface font-semibold">Change Password</h4>
                </div>
                <form method="POST" action="{{ route('profile.password') }}" class="p-lg space-y-md">
                    @csrf
                    @method('PUT')
                    <div class="space-y-sm">
                        <label class="block text-label-md text-on-surface-variant ml-1">Current Password</label>
                        <input name="current_password" type="password" required
                               class="w-full border border-outline-variant rounded-lg p-md focus:ring-2 focus:ring-primary outline-none bg-background">
                    </div>
                    <div class="space-y-sm">
                        <label class="block text-label-md text-on-surface-variant ml-1">New Password</label>
                        <input name="password" type="password" required
                               class="w-full border border-outline-variant rounded-lg p-md focus:ring-2 focus:ring-primary outline-none bg-background">
                    </div>
                    <div class="space-y-sm">
                        <label class="block text-label-md text-on-surface-variant ml-1">Confirm New Password</label>
                        <input name="password_confirmation" type="password" required
                               class="w-full border border-outline-variant rounded-lg p-md focus:ring-2 focus:ring-primary outline-none bg-background">
                    </div>
                    <button type="submit" class="w-full bg-primary-container text-on-primary-container py-2.5 rounded-lg text-label-md flex items-center justify-center gap-sm">
                        <span class="material-symbols-outlined text-[18px]">lock_reset</span> Update Password
                    </button>
                </form>
            </section>
        </div>
    </div>
@endsection

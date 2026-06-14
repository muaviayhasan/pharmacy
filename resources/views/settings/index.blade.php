@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'System Settings')

@section('content')
    @include('partials.flash')

    <form method="POST" action="{{ route('settings.update') }}" class="space-y-lg max-w-4xl">
        @csrf
        @method('PUT')

        @foreach ($schema as $group => $fields)
            <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
                <div class="bg-surface-container-low px-lg py-md border-b border-outline-variant">
                    <h4 class="text-label-md font-bold text-primary">{{ $group }}</h4>
                </div>
                <div class="p-lg grid grid-cols-1 md:grid-cols-2 gap-lg">
                    @foreach ($fields as $key => [$label, $type])
                        @php $current = $values[$key] ?? ''; @endphp
                        @if ($type === 'boolean')
                            <div class="flex items-center justify-between bg-surface-container-low p-md rounded-lg border border-outline-variant md:col-span-2">
                                <span class="text-label-md text-on-surface">{{ $label }}</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="{{ $key }}" value="1" class="sr-only peer" @checked($current === '1')>
                                    <div class="w-11 h-6 bg-surface-variant rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </div>
                        @else
                            <div class="space-y-sm {{ $key === 'company_address' ? 'md:col-span-2' : '' }}">
                                <label class="block text-label-sm font-bold text-on-surface-variant">{{ $label }}</label>
                                <input name="{{ $key }}" type="{{ $type === 'integer' ? 'number' : 'text' }}" value="{{ old($key, $current) }}"
                                       class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex justify-end gap-sm">
            <button type="reset" class="px-lg py-2.5 rounded-lg text-label-md border border-outline-variant text-on-surface-variant hover:bg-surface-container-low">Reset Changes</button>
            <button type="submit" class="px-lg py-2.5 rounded-lg text-label-md bg-primary text-on-primary hover:opacity-90 flex items-center gap-sm"><span class="material-symbols-outlined text-[18px]">save</span> Save Settings</button>
        </div>
    </form>
@endsection

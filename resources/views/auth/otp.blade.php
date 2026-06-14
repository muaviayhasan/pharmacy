@extends('layouts.guest')

@section('title', 'Verify Login')

@section('content')
    <a href="{{ route('login') }}" class="inline-flex items-center gap-xs text-primary text-label-md hover:underline transition-all">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Login
    </a>

    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-xl shadow-sm">
        <div class="text-center mb-xl">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-primary-fixed/30 text-primary mb-md">
                <span class="material-symbols-outlined text-3xl">verified_user</span>
            </div>
            <h2 class="text-headline-lg text-on-surface mb-sm font-semibold">Verify Your Login</h2>
            <p class="text-body-sm text-on-surface-variant">
                Enter the 6 digit verification code sent to
                <span class="font-semibold text-on-surface">{{ $maskedEmail }}</span>.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-lg bg-error-container text-on-error-container p-md rounded-lg flex gap-sm items-start text-body-sm">
                <span class="material-symbols-outlined text-error text-[20px]">error</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('otp.verify') }}" class="space-y-xl" id="otp-form">
            @csrf
            <div class="flex justify-between gap-sm" id="otp-inputs">
                @for ($i = 0; $i < 6; $i++)
                    <input name="digit[]" inputmode="numeric" maxlength="1" required
                           class="otp-input w-12 h-14 text-center text-headline-md font-bold rounded-lg border border-outline-variant bg-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                @endfor
            </div>
            <button type="submit"
                    class="w-full py-md px-lg bg-primary text-on-primary text-label-md rounded-lg hover:bg-primary-container active:scale-[0.98] transition-all shadow-md flex items-center justify-center gap-sm">
                Verify OTP <span class="material-symbols-outlined text-[18px]">check_circle</span>
            </button>
        </form>

        <div class="mt-xl text-center">
            <form method="POST" action="{{ route('otp.resend') }}">
                @csrf
                <button type="submit" class="text-label-md text-primary hover:underline">Resend Code</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const inputs = document.querySelectorAll('.otp-input');
    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 1);
            if (e.target.value !== '' && index < inputs.length - 1) inputs[index + 1].focus();
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && e.target.value === '' && index > 0) inputs[index - 1].focus();
        });
        input.addEventListener('paste', (e) => {
            const text = (e.clipboardData.getData('text') || '').replace(/[^0-9]/g, '').slice(0, 6);
            if (!text) return;
            e.preventDefault();
            text.split('').forEach((ch, i) => { if (inputs[i]) inputs[i].value = ch; });
            (inputs[text.length] || inputs[inputs.length - 1]).focus();
        });
    });
    if (inputs[0]) inputs[0].focus();
</script>
@endpush

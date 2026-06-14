@if (session('status'))
    <div class="bg-primary-container/10 border border-primary-container text-primary-container p-md rounded-lg flex gap-sm items-center text-body-sm">
        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
        <span class="flex-1">{{ session('status') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="bg-error-container text-on-error-container p-md rounded-lg flex gap-sm items-center text-body-sm">
        <span class="material-symbols-outlined text-error text-[20px]">error</span>
        <span class="flex-1">{{ session('error') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="bg-error-container text-on-error-container p-md rounded-lg text-body-sm">
        <div class="flex gap-sm items-center font-bold mb-xs">
            <span class="material-symbols-outlined text-error text-[20px]">error</span>
            Please fix the following:
        </div>
        <ul class="list-disc list-inside space-y-0.5 ml-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

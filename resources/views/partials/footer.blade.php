<footer class="h-12 shrink-0 w-full px-margin-desktop flex items-center justify-between bg-surface border-t border-outline-variant text-label-sm text-outline">
    <p>&copy; {{ now()->year }} {{ config('app.name', 'PharmaCore') }}. All rights reserved.</p>
    <div class="flex items-center gap-md">
        <a href="#" class="hover:text-on-surface transition-colors">Privacy</a>
        <a href="#" class="hover:text-on-surface transition-colors">Terms</a>
        <span class="flex items-center gap-1">
            <span class="material-symbols-outlined text-sm text-primary">verified</span>
            v1.0.0
        </span>
    </div>
</footer>

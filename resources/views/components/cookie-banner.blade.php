<div x-data="kxConsent()" x-show="open" x-cloak role="dialog" aria-modal="false" aria-labelledby="cookie-title"
     class="fixed inset-x-0 bottom-0 z-50 border-t border-line bg-white p-4 shadow-[0_-4px_16px_rgba(20,38,58,0.12)]">
    <div class="mx-auto flex max-w-5xl flex-col gap-3 sm:flex-row sm:items-center">
        <div class="flex-1 text-sm text-ink">
            <p id="cookie-title" class="font-semibold">Cookies en Kendex</p>
            <p class="kx-muted">Usamos cookies esenciales para que el sistema funcione. Con tu permiso, también cookies de analítica (Google Analytics 4) para mejorar el sitio. Lee la <a class="kx-link" href="{{ route('privacy') }}">política de privacidad</a>.</p>
        </div>
        <div class="flex gap-2">
            <button type="button" class="kx-btn-ghost" @click="choose('essential')">Solo esenciales</button>
            <button type="button" class="kx-btn" @click="choose('all')">Aceptar analítica</button>
        </div>
    </div>
</div>

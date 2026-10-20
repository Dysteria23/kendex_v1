// Banner de cookies y carga condicionada de Google Analytics 4 (checklist 5 y 19).
// GA4 solo se carga si la persona aceptó la analítica.
const KEY = 'kx_consent';

function loadGa4() {
    const id = document.querySelector('meta[name="ga4-id"]')?.content;
    if (!id || window.__ga4Loaded) return;
    window.__ga4Loaded = true;

    const s = document.createElement('script');
    s.async = true;
    s.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(id)}`;
    document.head.appendChild(s);

    window.dataLayer = window.dataLayer || [];
    window.gtag = function () { window.dataLayer.push(arguments); };
    window.gtag('js', new Date());
    window.gtag('config', id, { anonymize_ip: true });
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('kxConsent', () => ({
        open: false,
        init() {
            const saved = localStorage.getItem(KEY);
            if (!saved) this.open = true;
            else if (saved === 'all') loadGa4();
        },
        choose(value) {
            localStorage.setItem(KEY, value);
            this.open = false;
            if (value === 'all') loadGa4();
        },
    }));
});

window.kxResetConsent = () => {
    localStorage.removeItem(KEY);
    location.reload();
};

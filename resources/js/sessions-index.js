import { downloadBrandedQrPng } from './qr-download';

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Enlace copiado al portapapeles');
    });
}

async function downloadQrPng(slug, url = '') {
    const config = window.__SPIKIA_SESSIONS_INDEX__ || {};

    await downloadBrandedQrPng({
        wrapperId: `qr-wrap-${slug}`,
        filename: `qr-${slug}.zip`,
        branding: {
            logoUrl: config.logoUrl,
            title: config.brandTitle || 'SPIKIA',
            subtitle: config.brandSubtitle || 'Panel de sesiones',
            url: url || config.brandUrl || '',
        },
    });
}

window.copyToClipboard = copyToClipboard;
window.downloadQrPng = downloadQrPng;

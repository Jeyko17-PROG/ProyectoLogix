function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Enlace copiado al portapapeles');
    });
}

async function downloadQrPng(slug) {
    const wrapper = document.getElementById(`qr-wrap-${slug}`);
    const svg = wrapper ? wrapper.querySelector('svg') : null;

    if (!svg) {
        alert('No se encontró el QR.');
        return;
    }

    const svgData = new XMLSerializer().serializeToString(svg);
    const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
    const url = URL.createObjectURL(svgBlob);
    const img = new Image();

    img.onload = () => {
        const canvas = document.createElement('canvas');
        const size = Math.max(img.width, img.height) || 1024;
        canvas.width = size;
        canvas.height = size;

        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, size, size);
        ctx.drawImage(img, 0, 0, size, size);

        canvas.toBlob((blob) => {
            if (!blob) {
                alert('No se pudo generar el PNG.');
                URL.revokeObjectURL(url);
                return;
            }

            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `qr-${slug}.png`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(link.href);
            URL.revokeObjectURL(url);
        }, 'image/png');
    };

    img.onerror = () => {
        alert('No se pudo convertir el QR a PNG.');
        URL.revokeObjectURL(url);
    };

    img.src = url;
}

window.copyToClipboard = copyToClipboard;
window.downloadQrPng = downloadQrPng;


// public/assets/script.js
// Placeholder untuk JavaScript tambahan (validasi, interaksi)
document.addEventListener('DOMContentLoaded', () => {
    const flash = document.querySelector('.flash');
    if (flash) {
        setTimeout(() => flash.remove(), 3000);
    }
});

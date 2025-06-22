import './bootstrap';

import Alpine from 'alpinejs';

// BARU: Impor Tom Select
import TomSelect from 'tom-select';

window.Alpine = Alpine;

// BARU: Inisialisasi Tom Select secara global
// Kode ini akan berjalan setelah halaman dimuat dan mengubah semua elemen
// dengan class 'tom-select-multiple' menjadi input yang interaktif.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.tom-select-multiple').forEach((el) => {
        let settings = {
            plugins: ['remove_button'],
            create: false,
        };
        new TomSelect(el, settings);
    });
});


Alpine.start();
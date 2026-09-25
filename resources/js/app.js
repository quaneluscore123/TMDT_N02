import './bootstrap';
import collapse from '@alpinejs/collapse';

// Livewire 4 đã bundle Alpine — KHÔNG import/start instance Alpine riêng
// (instance riêng ghi đè window.Alpine → store 'cart' rơi vào instance chết).
document.addEventListener('alpine:init', () => {
    window.Alpine.plugin(collapse);
});

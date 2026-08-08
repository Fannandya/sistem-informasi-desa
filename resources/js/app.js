import './bootstrap';
import './scroll-fade';
import './gambar-cadangan';

import Alpine from 'alpinejs';

document.addEventListener('alpine:init', () => {
    Alpine.data('countingNumber', (targetValue) => ({
        count: 0,
        target: targetValue,
        started: false,
        init() {
            if (this.target === null || this.target === undefined) {
                this.count = '—';
                return;
            }
            let observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    this.startCount();
                    observer.disconnect();
                }
            }, { threshold: 0.1 });
            observer.observe(this.$el);
        },
        startCount() {
            if (this.started) return;
            this.started = true;
            const duration = 2000;
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                // easeOutExpo for smooth deceleration
                const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                this.count = Math.floor(easeProgress * this.target);
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                } else {
                    this.count = this.target;
                }
            };
            window.requestAnimationFrame(step);
        },
        formatValue() {
            if (this.count === '—') return '—';
            // format standard Indonesia e.g. 1.234
            return this.count.toLocaleString('id-ID');
        }
    }));
});

window.Alpine = Alpine;
Alpine.start();

/**
 * Scroll Fade Animation
 * Animasi fade in/out ketika elemen masuk/keluar viewport
 */

export function initScrollFade() {
    const animatedElements = document.querySelectorAll('[data-fade-in]');

    if (!animatedElements.length || !('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-active');
                    entry.target.classList.remove('fade-out');
                } else {
                    entry.target.classList.add('fade-out');
                    entry.target.classList.remove('fade-in-active');
                }
            });
        },
        {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px',
        }
    );

    animatedElements.forEach((el) => observer.observe(el));
}

// Auto-run on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initScrollFade);
} else {
    initScrollFade();
}

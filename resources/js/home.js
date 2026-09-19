import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('DOMContentLoaded', () => {
    const sections = document.querySelectorAll('#main-content section');

    sections.forEach((section, index) => {
        section.classList.add('home-reveal');
        section.dataset.delay = String(Math.min(index, 3));
    });

    if (!('IntersectionObserver' in window)) {
        sections.forEach((section) => section.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries, currentObserver) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                currentObserver.unobserve(entry.target);
            });
        },
        {
            rootMargin: '0px 0px -10% 0px',
            threshold: 0.08,
        },
    );

    sections.forEach((section) => observer.observe(section));
});

Alpine.start();

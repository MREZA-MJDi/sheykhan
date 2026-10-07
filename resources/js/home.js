document.addEventListener('DOMContentLoaded', () => {
    const elements = document.querySelectorAll('#main-content .home-reveal');

    if (!('IntersectionObserver' in window)) {
        elements.forEach((element) => element.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries, instance) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            instance.unobserve(entry.target);
        });
    }, {
        rootMargin: '0px 0px -8% 0px',
        threshold: 0.08,
    });

    elements.forEach((element) => observer.observe(element));
});
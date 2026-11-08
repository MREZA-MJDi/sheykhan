(() => {
    'use strict';
    document.querySelectorAll('[data-public-back-top]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            window.scrollTo({top:0,behavior:window.matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth'});
            if (window.history?.replaceState) window.history.replaceState(null,'','#top');
        });
    });
})();
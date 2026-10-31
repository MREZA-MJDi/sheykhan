import Alpine from 'alpinejs';

const BOOT_FLAG = '__sheykhanAlpineBooted';

/**
 * Start the single Alpine instance used by the current page.
 *
 * This module owns initialization only. It deliberately contains no markup,
 * classes, theme tokens, or presentation rules.
 */
export function bootAlpineRuntime() {
    if (typeof window === 'undefined') {
        return null;
    }

    const current = window.Alpine || Alpine;
    window.Alpine = current;

    if (window[BOOT_FLAG] === true) {
        return current;
    }

    if (!current || typeof current.start !== 'function') {
        return null;
    }

    current.start();
    window[BOOT_FLAG] = true;

    return current;
}

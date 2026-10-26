import shaka from 'shaka-player/dist/shaka-player.ui.js';
import 'shaka-player/dist/controls.css';
import '../css/shaka-player.css';
import persianTranslations from './locales/shaka-fa.json';

const initializedPlayers = new WeakMap();

const showStatus = (root, message, tone = 'info') => {
    const status = root.querySelector('[data-shaka-status]');
    if (!status) return;

    status.textContent = message;
    status.dataset.tone = tone;
    status.hidden = false;
};

const hideStatus = (root) => {
    const status = root.querySelector('[data-shaka-status]');
    if (status) status.hidden = true;
};

const getPlayableManifestUrl = (rawUrl) => {
    if (!rawUrl || !rawUrl.trim()) return null;

    try {
        const url = new URL(rawUrl, window.location.origin);
        if (!['http:', 'https:'].includes(url.protocol)) return null;
        if (window.location.protocol === 'https:' && url.protocol !== 'https:') return null;
        return url.href;
    } catch {
        return null;
    }
};

async function initializePlayer(root) {
    if (initializedPlayers.has(root)) return initializedPlayers.get(root);

    const task = (async () => {
        const video = root.querySelector('[data-shaka-video]');
        const container = root.querySelector('[data-shaka-player-container]');
        const rawManifestUrl = root.dataset.manifestUrl || '';
        const manifestUrl = getPlayableManifestUrl(rawManifestUrl);

        if (!video || !container) {
            showStatus(root, 'ساختار پلیر ناقص است. صفحه را دوباره بارگذاری کنید.', 'error');
            return;
        }

        if (!manifestUrl) {
            showStatus(root, 'منبع استریم هنوز به این درس متصل نشده است.', 'info');
            root.dataset.playerState = 'awaiting-source';
            return;
        }

        if (typeof shaka?.Player !== 'function' || !shaka.ui?.Overlay) {
            showStatus(root, 'کتابخانهٔ پلیر بارگذاری نشد. منابع فرانت‌اند را دوباره build کنید.', 'error');
            return;
        }

        shaka.polyfill.installAll();

        if (!shaka.Player.isBrowserSupported()) {
            showStatus(root, 'این مرورگر APIهای لازم برای پخش Shaka را پشتیبانی نمی‌کند.', 'error');
            root.dataset.playerState = 'unsupported';
            return;
        }

        let player;
        let ui;

        try {
            player = new shaka.Player();
            await player.attach(video);

            ui = new shaka.ui.Overlay(player, container, video);
            ui.configure({
                addSeekBar: true,
                enableTooltips: true,
                controlPanelElements: [
                    'play_pause',
                    'time_and_duration',
                    'spacer',
                    'mute',
                    'volume',
                    'playback_rate',
                    'overflow_menu',
                    'fullscreen',
                ],
                overflowMenuButtons: [
                    'quality',
                    'language',
                    'captions',
                    'playback_rate',
                    'statistics',
                ],
                seekBarColors: {
                    base: 'rgba(255, 255, 255, 0.28)',
                    buffered: 'rgba(124, 187, 255, 0.72)',
                    played: '#7c83ff',
                },
            });

            const localization = ui.getControls().getLocalization();
            localization.insert('fa', new Map(Object.entries(persianTranslations)));
            localization.changeLocale(['fa']);

            player.addEventListener('error', () => {
                showStatus(root, 'پخش این ویدئو انجام نشد. اتصال، آدرس استریم و مجوز دسترسی را بررسی کنید.', 'error');
                root.dataset.playerState = 'error';
            });

            showStatus(root, 'در حال آماده‌سازی پخش…');
            await player.load(manifestUrl);
            root.dataset.playerState = 'ready';
            hideStatus(root);
        } catch (error) {
            // Keep technical details out of the student-facing UI. The server-side
            // logging and a safe diagnostic channel can be added with the DRM API.
            console.error('[Sheykhan Shaka] Player initialization failed', error);
            showStatus(root, 'پخش این ویدئو آماده نشد. منبع استریم و سازگاری مرورگر را بررسی کنید.', 'error');
            root.dataset.playerState = 'error';

            if (ui) {
                try { ui.destroy(); } catch { /* best-effort cleanup */ }
            }
            if (player) {
                try { await player.destroy(); } catch { /* best-effort cleanup */ }
            }
        }
    })();

    initializedPlayers.set(root, task);
    return task;
}

export async function bootShakaPlayers() {
    const roots = Array.from(document.querySelectorAll('[data-shaka-player-root]'));
    if (!roots.length) return;

    await Promise.all(roots.map(initializePlayer));
}

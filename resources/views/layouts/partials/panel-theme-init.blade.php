{{-- Shared theme bootstrap for Student and Teacher workspaces. Keep this before CSS to avoid a theme flash. --}}
<script>
    (() => {
        try {
            const storedThemes = [
                localStorage.getItem('sheykhan.panel.theme'),
                localStorage.getItem('sheykhan.student.theme'),
                localStorage.getItem('theme'),
            ];
            const savedTheme = storedThemes.find((theme) => theme === 'light' || theme === 'dark');

            if (savedTheme) {
                document.documentElement.dataset.theme = savedTheme;
            }
        } catch (_) {
            // Storage can be unavailable in privacy-restricted browser contexts.
        }
    })();
</script>

<script>
    (() => {
        const root = document.documentElement;
        const allowed = @js(array_keys(config('admin.themes')));
        const key = root.dataset.themeStorageKey;
        const modeKey = root.dataset.modeStorageKey;
        let selected = 'rose';
        let mode = 'light';
        try {
            const stored = localStorage.getItem(key);
            if (allowed.includes(stored)) selected = stored;
            if (localStorage.getItem(modeKey) === 'dark') mode = 'dark';
        } catch {}
        root.dataset.adminTheme = selected;
        root.dataset.adminMode = mode;
    })();
</script>

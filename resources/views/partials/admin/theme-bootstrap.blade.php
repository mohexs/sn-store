<script>
    (() => {
        const root = document.documentElement;
        const allowed = @js(array_keys(config('admin.themes')));
        const key = root.dataset.themeStorageKey;
        let selected = 'rose';
        try {
            const stored = localStorage.getItem(key);
            if (allowed.includes(stored)) selected = stored;
        } catch {}
        root.dataset.adminTheme = selected;
    })();
</script>

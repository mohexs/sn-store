export default () => ({
    sidebarOpen: false,
    desktop: false,
    breakpoint: null,
    onBreakpointChange: null,
    darkMode: document.documentElement.dataset.adminMode === 'dark',
    modeMessage: '',
    onStorage: null,
    init() {
        this.breakpoint = window.matchMedia('(min-width: 1024px)');
        this.desktop = this.breakpoint.matches;
        this.onBreakpointChange = event => {
            this.desktop = event.matches;
            this.sidebarOpen = false;
            document.body.style.overflow = '';
        };
        this.breakpoint.addEventListener('change', this.onBreakpointChange);
        this.onStorage = event => {
            if (event.key === document.documentElement.dataset.modeStorageKey) {
                this.applyMode(event.newValue === 'dark');
            }
        };
        window.addEventListener('storage', this.onStorage);
    },
    applyMode(darkMode) {
        this.darkMode = darkMode;
        const root = document.documentElement;
        root.dataset.adminMode = darkMode ? 'dark' : 'light';
        root.style.colorScheme = darkMode ? 'dark' : 'light';
        const brandColor = getComputedStyle(root).getPropertyValue('--admin-brand-700').trim();
        document.querySelector('meta[name="theme-color"]')?.setAttribute('content', darkMode ? '#0b1220' : `rgb(${brandColor})`);
    },
    openSidebar() {
        this.sidebarOpen = true;
        document.body.style.overflow = 'hidden';
        this.$nextTick(() => this.$refs.sidebarClose.focus());
    },
    closeSidebar() {
        if (!this.sidebarOpen) return;
        this.sidebarOpen = false;
        document.body.style.overflow = '';
        this.$nextTick(() => this.$refs.sidebarToggle.focus());
    },
    trapSidebarFocus(event) {
        if (this.desktop || !this.sidebarOpen) return;
        const focusable = [...event.currentTarget.querySelectorAll('a[href], button:not([disabled])')];
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    },
    toggleMode() {
        this.applyMode(!this.darkMode);

        try {
            localStorage.setItem(document.documentElement.dataset.modeStorageKey, this.darkMode ? 'dark' : 'light');
            this.modeMessage = `Mode ${this.darkMode ? 'gelap' : 'terang'} diaktifkan.`;
        } catch {
            this.modeMessage = `Mode ${this.darkMode ? 'gelap' : 'terang'} diaktifkan, tetapi preferensi tidak dapat disimpan di browser ini.`;
        }
    },
    destroy() {
        this.breakpoint.removeEventListener('change', this.onBreakpointChange);
        window.removeEventListener('storage', this.onStorage);
        document.body.style.overflow = '';
    },
});

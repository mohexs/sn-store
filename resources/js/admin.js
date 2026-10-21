export default () => ({
    sidebarOpen: false,
    desktop: false,
    breakpoint: null,
    onBreakpointChange: null,
    init() {
        this.breakpoint = window.matchMedia('(min-width: 1024px)');
        this.desktop = this.breakpoint.matches;
        this.onBreakpointChange = event => {
            this.desktop = event.matches;
            this.sidebarOpen = false;
            document.body.style.overflow = '';
        };
        this.breakpoint.addEventListener('change', this.onBreakpointChange);
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
    destroy() {
        this.breakpoint.removeEventListener('change', this.onBreakpointChange);
        document.body.style.overflow = '';
    },
});

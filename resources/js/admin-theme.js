const root = document.documentElement;
const allowed = ['rose', 'blue', 'emerald', 'violet', 'amber', 'slate'];
const validTheme = theme => allowed.includes(theme) ? theme : 'rose';

function applyTheme(theme) {
    const selected = validTheme(theme);
    root.dataset.adminTheme = selected;
    const color = getComputedStyle(root).getPropertyValue('--admin-brand-700').trim();
    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', `rgb(${color})`);
    return selected;
}

export default () => ({
    selected: 'rose',
    message: 'Pilihan disimpan pada browser ini untuk akun Anda.',
    onStorage: null,
    init() {
        this.selected = validTheme(root.dataset.adminTheme);
        this.onStorage = event => {
            if (event.key === root.dataset.themeStorageKey) {
                this.selected = applyTheme(event.newValue);
            }
        };
        window.addEventListener('storage', this.onStorage);
    },
    choose(theme) {
        this.selected = applyTheme(theme);
        try {
            localStorage.setItem(root.dataset.themeStorageKey, this.selected);
            this.message = 'Tema diterapkan dan disimpan pada browser ini.';
        } catch {
            this.message = 'Tema diterapkan. Browser ini tidak mengizinkan penyimpanan pilihan.';
        }
    },
    destroy() {
        window.removeEventListener('storage', this.onStorage);
    },
});

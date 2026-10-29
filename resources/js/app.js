import Alpine from 'alpinejs';
import adminShell from './admin';
import adminThemePicker from './admin-theme';
import initializeDateInputs from './date-inputs';
import initializeDeleteConfirmation from './delete-confirmation';
import productForm from './product-form';

Alpine.data('adminShell', adminShell);
Alpine.data('adminThemePicker', adminThemePicker);
Alpine.data('productForm', productForm);
initializeDateInputs();
initializeDeleteConfirmation();

Alpine.data('storefront', () => ({
    query: '', category: 'Semua', cart: [], cartOpen: false, notice: '', noticeTimer: null,
    get count() { return this.cart.reduce((sum, item) => sum + item.quantity, 0); },
    get total() { return this.cart.reduce((sum, item) => sum + item.price * item.quantity, 0); },
    rupiah(value) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value); },
    matches(product) { return (this.category === 'Semua' || this.category === product.category) && `${product.name} ${product.category}`.toLocaleLowerCase('id').includes(this.query.trim().toLocaleLowerCase('id')); },
    hasResults(products) { return products.some(product => this.matches(product)); },
    add(product) {
        const item = this.cart.find(item => item.id === product.id);
        if (item) { item.quantity++; } else { this.cart.push({ ...product, quantity: 1 }); }
        this.notice = `${product.name} ditambahkan ke keranjang demo`;
        clearTimeout(this.noticeTimer);
        this.noticeTimer = setTimeout(() => this.notice = '', 2800);
    },
    trapFocus(event) { const elements = [...event.currentTarget.querySelectorAll('button, a[href], input')]; const first = elements[0]; const last = elements[elements.length - 1]; if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); } else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); } },
    remove(id) { this.cart = this.cart.filter(item => item.id !== id); },
    destroy() { clearTimeout(this.noticeTimer); },
}));

window.Alpine = Alpine;
Alpine.start();

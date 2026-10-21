export default () => ({
    preview: null,
    photoError: '',
    submitting: false,
    selectPhoto(event) {
        if (this.preview) URL.revokeObjectURL(this.preview);
        this.preview = null;
        this.photoError = '';
        const file = event.target.files[0];
        if (!file) return;
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            this.photoError = 'Gunakan foto JPG, PNG, atau WebP.';
        } else if (file.size > 2 * 1024 * 1024) {
            this.photoError = 'Ukuran foto produk maksimal 2 MB.';
        }
        if (this.photoError) {
            event.target.value = '';
            return;
        }
        this.preview = URL.createObjectURL(file);
    },
    clearPhoto() {
        if (this.preview) URL.revokeObjectURL(this.preview);
        this.preview = null;
        this.photoError = '';
        this.$root.querySelector('#photo').value = '';
    },
    destroy() {
        if (this.preview) URL.revokeObjectURL(this.preview);
    },
});

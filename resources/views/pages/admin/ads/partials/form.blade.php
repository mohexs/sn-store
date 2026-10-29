@if ($errors->any())
    <div role="alert" class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800"><p class="font-bold">Banner belum berhasil disimpan.</p><ul class="mt-2 list-disc pl-5 text-xs leading-6">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div class="grid items-start gap-6 xl:grid-cols-3">
    <section aria-labelledby="ad-copy-title" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 xl:col-span-2">
        <div class="mb-6 border-b border-slate-100 pb-5"><h2 id="ad-copy-title" class="text-sm font-bold text-slate-900">Keterangan banner</h2><p class="mt-1 text-xs text-slate-400">Teks ditampilkan di sisi kiri gambar hero.</p></div>
        <div class="grid gap-5 sm:grid-cols-2">
            <label for="eyebrow" class="text-xs font-semibold text-slate-600 sm:col-span-2">Label kecil<input id="eyebrow" name="eyebrow" type="text" required maxlength="120" value="{{ old('eyebrow', $ad->eyebrow) }}" placeholder="Contoh: LANGKAH KECIL, HIDUP LEBIH SEHAT" class="mt-2 w-full rounded-xl border-slate-200 px-3.5 py-3 text-sm font-normal text-slate-700 focus:border-brand-500 focus:ring-brand-500"></label>
            <label for="title" class="text-xs font-semibold text-slate-600">Judul utama<input id="title" name="title" type="text" required maxlength="255" value="{{ old('title', $ad->title) }}" placeholder="Contoh: Kesehatan Anda," class="mt-2 w-full rounded-xl border-slate-200 px-3.5 py-3 text-sm font-normal text-slate-700 focus:border-brand-500 focus:ring-brand-500"></label>
            <label for="accent" class="text-xs font-semibold text-slate-600">Judul aksen<input id="accent" name="accent" type="text" required maxlength="255" value="{{ old('accent', $ad->accent) }}" placeholder="Contoh: prioritas kami." class="mt-2 w-full rounded-xl border-slate-200 px-3.5 py-3 text-sm font-normal text-slate-700 focus:border-brand-500 focus:ring-brand-500"></label>
            <label for="copy" class="text-xs font-semibold text-slate-600 sm:col-span-2">Keterangan<textarea id="copy" name="copy" rows="4" required maxlength="2000" placeholder="Tuliskan keterangan singkat banner" class="mt-2 w-full rounded-xl border-slate-200 px-3.5 py-3 text-sm font-normal text-slate-700 focus:border-brand-500 focus:ring-brand-500">{{ old('copy', $ad->copy) }}</textarea></label>
        </div>
    </section>
    <div class="flex flex-col gap-6">
        <section aria-labelledby="ad-image-title" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 id="ad-image-title" class="text-sm font-bold text-slate-900">Foto banner</h2>
            @if ($ad->image_path)
                <img src="{{ Storage::disk('public')->url($ad->image_path) }}" alt="Banner saat ini" width="600" height="450" class="mt-4 aspect-[4/3] w-full rounded-xl border border-slate-100 object-cover">
            @else
                <div class="mt-4 flex aspect-[4/3] items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 text-xs text-slate-400">Pratinjau foto banner</div>
            @endif
            <label for="image" class="mt-5 block text-xs font-semibold text-slate-600">{{ $ad->exists ? 'Ganti foto banner' : 'Pilih foto banner' }} @if (! $ad->exists)<span aria-hidden="true">*</span>@endif</label>
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" @required(! $ad->exists) aria-invalid="{{ $errors->has('image') ? 'true' : 'false' }}" class="mt-2 block w-full text-[11px] text-slate-500 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2.5 file:text-xs file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
            <p class="mt-2 text-[11px] leading-5 text-slate-400">JPG, JPEG, PNG, WebP · Maks. 2 MB · Maks. 4096 × 4096 px. Disarankan 1200 × 900 px. Gambar tidak di-resize otomatis.</p>
        </section>
        <section aria-labelledby="ad-display-title" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 id="ad-display-title" class="text-sm font-bold text-slate-900">Pengaturan tampil</h2>
            <label for="sort_order" class="mt-4 block text-xs font-semibold text-slate-600">Urutan banner<input id="sort_order" name="sort_order" type="number" required min="0" max="100" step="1" value="{{ old('sort_order', $ad->sort_order ?? 0) }}" class="mt-2 w-full rounded-xl border-slate-200 px-3 py-3 text-sm font-normal text-slate-700 focus:border-brand-500 focus:ring-brand-500"><span class="mt-1 block text-[10px] font-normal text-slate-400">Angka lebih kecil tampil lebih dahulu.</span></label>
            <input type="hidden" name="is_active" value="0">
            <label for="is_active" class="mt-5 flex cursor-pointer items-center gap-3 text-xs font-semibold text-slate-700"><input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $ad->exists ? $ad->is_active : true)) class="rounded border-slate-300 text-brand-700 focus:ring-brand-500">Tampilkan banner di halaman publik</label>
        </section>
    </div>
</div>
<div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 sm:px-6"><p class="text-xs leading-6 text-slate-400">{{ $ad->exists ? 'Banner akan diperbarui sesuai perubahan yang disimpan.' : 'Banner akan ditambahkan ke daftar Ads.' }}</p><div class="flex items-center gap-3"><a href="{{ route('admin.ads.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</a><button type="submit" class="rounded-xl bg-brand-700 px-5 py-3 text-xs font-bold text-white hover:bg-brand-800">{{ $ad->exists ? 'Simpan perubahan' : 'Simpan banner' }}</button></div></div>

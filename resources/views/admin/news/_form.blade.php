<x-admin.validation-summary />

<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @isset($method) @method($method) @endisset

    <div class="row g-4">
        <div class="col-lg-8">
            <x-admin.form.input name="title" label="Judul" :value="$news?->title" maxlength="255" required />
            <x-admin.form.select name="news_category_id" label="Kategori" :options="$categories"
                :value="$news?->news_category_id" placeholder="Pilih kategori" required />
            <x-admin.form.textarea name="excerpt" label="Ringkasan" :value="$news?->excerpt" rows="4" maxlength="1000"
                help="Opsional, maksimal 1.000 karakter." />
                <x-admin.form.file name="thumbnail" label="Pilih gambar" :existing="$news?->thumbnail" :preview="true"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    help="JPG, PNG, atau WebP. Maksimal 4 MB." />
            @if ($news?->thumbnail && Storage::disk('public')->exists($news->thumbnail))
                <img class="admin-current-image mb-3" src="{{ \Illuminate\Support\Facades\Storage::url($news->thumbnail) }}"
                    alt="Thumbnail berita saat ini">
            @endif
            <x-admin.form.textarea name="content" label="Isi Berita" :value="$news?->content" rows="14"
                help="Gunakan teks biasa. Baris baru akan dipertahankan saat ditampilkan." required />
        </div>
        <div class="col-lg-4">
            <x-admin.card title="Publikasi" class="mb-4">
                <x-admin.form.select name="status" label="Status" :options="['draft' => 'Draf', 'published' => 'Terbit']"
                    :value="$news?->status ?? 'draft'" required />
                <x-admin.form.input name="published_at" label="Tanggal Publikasi" type="datetime-local"
                    :value="$news?->published_at?->format('Y-m-d\TH:i')"
                    help="Jika status Terbit dan dikosongkan, waktu saat ini akan digunakan." />
            </x-admin.card>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
        <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.news.index') }}">Batal</a>
    </div>
</form>

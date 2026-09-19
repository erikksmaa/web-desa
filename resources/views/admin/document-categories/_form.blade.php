<x-admin.validation-summary />

<form method="POST" action="{{ $action }}">
    @csrf
    @isset($method) @method($method) @endisset

    <x-admin.form.input name="name" label="Nama" :value="$documentCategory?->name" maxlength="150" required />
    <x-admin.form.textarea name="description" label="Deskripsi" :value="$documentCategory?->description" rows="4" />
    <x-admin.form.input name="sort_order" label="Urutan" type="number" :value="$documentCategory?->sort_order ?? 0" min="0" required />
    <x-admin.form.checkbox name="is_active" label="Kategori aktif" :checked="$documentCategory?->is_active ?? true" help="Kategori aktif tersedia saat membuat berkas baru." />

    <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.document-categories.index') }}">Batal</a>
    </div>
</form>

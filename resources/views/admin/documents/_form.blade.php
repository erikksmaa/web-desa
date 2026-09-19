<x-admin.validation-summary />
<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
@csrf
@if ($method !== 'POST') @method($method) @endif
<x-admin.form.input name="title" label="Judul" :value="$document->title" maxlength="255" required />
<x-admin.form.select name="document_category_id" label="Kategori" :options="$categories" :value="$document->document_category_id" placeholder="Pilih kategori" required />
<p><a href="{{ route('admin.document-categories.index') }}">Kelola kategori berkas</a></p>
<x-admin.form.textarea name="description" label="Deskripsi" :value="$document->description" rows="6" />
<x-admin.form.file name="file" label="Berkas" :existing="$document->original_filename" accept=".pdf,.doc,.docx,.xls,.xlsx" :required="! $document->exists" help="PDF, DOC, DOCX, XLS, XLSX; maksimal 15 MB. Saat mengubah, kosongkan untuk mempertahankan file." />
<x-admin.form.select name="status" label="Status" :options="['draft'=>'Draf','published'=>'Terbit']" :value="$document->status ?? 'draft'" required />
<x-admin.form.input name="published_at" label="Tanggal Publikasi (WIB)" type="datetime-local" :value="$document->published_at?->format('Y-m-d\TH:i')" help="Kosong: pertahankan waktu sebelumnya, atau waktu sekarang saat pertama diterbitkan." />
<button class="btn btn-primary" type="submit">Simpan berkas</button>
<a class="btn btn-outline-secondary" href="{{ route('admin.documents.index') }}">Batal</a>
</form>

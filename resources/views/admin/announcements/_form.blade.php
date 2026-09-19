<x-admin.validation-summary />
<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
@csrf
@if ($method !== 'POST') @method($method) @endif
<x-admin.form.input name="title" label="Judul" :value="$announcement->title" maxlength="255" required />
<x-admin.form.textarea name="content" label="Isi Pengumuman" :value="$announcement->content" rows="12" required />
<x-admin.form.file name="attachment" label="Lampiran" :existing="$announcement->attachment_original_name" accept=".pdf,.doc,.docx,.xls,.xlsx" help="PDF, DOC, DOCX, XLS, XLSX; maksimal 10 MB. Kosongkan untuk mempertahankan lampiran saat ini." />
<x-admin.form.select name="status" label="Status" :options="['draft'=>'Draf','published'=>'Terbit']" :value="$announcement->status ?? 'draft'" required />
<div class="row"><div class="col-md-6"><x-admin.form.input name="published_at" label="Tanggal Publikasi (WIB)" type="datetime-local" :value="$announcement->published_at?->format('Y-m-d\TH:i')" help="Kosong: pertahankan waktu sebelumnya, atau waktu sekarang saat pertama diterbitkan." /></div>
<div class="col-md-6"><x-admin.form.input name="expires_at" label="Tanggal Berakhir (WIB)" type="datetime-local" :value="$announcement->expires_at?->format('Y-m-d\TH:i')" help="Opsional. Pengumuman tidak tampil setelah waktu ini." /></div></div>
<button class="btn btn-primary" type="submit">Simpan pengumuman</button>
<a class="btn btn-outline-secondary" href="{{ route('admin.announcements.index') }}">Batal</a>
</form>

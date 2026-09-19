<x-admin.validation-summary />
<form method="POST" action="{{ $action }}">
@csrf
@if ($method !== 'POST') @method($method) @endif
<x-admin.form.input name="title" label="Judul" :value="$agenda->title" maxlength="255" required />
<x-admin.form.textarea name="description" label="Deskripsi" :value="$agenda->description" rows="8" />
<x-admin.form.input name="location" label="Lokasi" :value="$agenda->location" maxlength="255" />
<div class="row"><div class="col-md-6"><x-admin.form.input name="start_at" label="Waktu Mulai (WIB)" type="datetime-local" :value="$agenda->start_at?->format('Y-m-d\TH:i')" required /></div><div class="col-md-6"><x-admin.form.input name="end_at" label="Waktu Selesai (WIB)" type="datetime-local" :value="$agenda->end_at?->format('Y-m-d\TH:i')" /></div></div>
<x-admin.form.select name="status" label="Status" :options="['draft'=>'Draf','published'=>'Terbit']" :value="$agenda->status ?? 'draft'" required />
<button class="btn btn-primary" type="submit">Simpan agenda</button>
<a class="btn btn-outline-secondary" href="{{ route('admin.agendas.index') }}">Batal</a>
</form>

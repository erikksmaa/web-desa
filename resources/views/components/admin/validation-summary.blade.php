@if ($errors->any())
    <div {{ $attributes->class(['alert', 'alert-danger']) }} role="alert" tabindex="-1" data-validation-summary>
        <p class="fw-semibold mb-2">Periksa kembali data berikut:</p>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

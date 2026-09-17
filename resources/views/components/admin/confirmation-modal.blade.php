<div class="modal fade" id="confirmation-modal" tabindex="-1" aria-labelledby="confirmation-modal-title" aria-describedby="confirmation-modal-description" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="confirmation-modal-title">Konfirmasi tindakan</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p id="confirmation-modal-description" data-confirm-message>Apakah Anda yakin ingin melanjutkan?</p>
                <p class="fw-semibold mb-0" data-confirm-item hidden></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="" data-confirm-form>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

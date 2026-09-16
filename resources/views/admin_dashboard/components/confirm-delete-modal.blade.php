{{-- Global Delete Confirmation --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(formElement, itemName) {
    itemName = itemName || 'data ini';
    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: 'Apakah Anda yakin ingin menghapus <strong>' + itemName + '</strong>?<br><small class="text-muted">Tindakan ini tidak dapat dibatalkan.</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff3e1d',
        cancelButtonColor: '#8592a3',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
    }).then(function(result) {
        if (result.isConfirmed) {
            formElement.submit();
        }
    });
}
</script>

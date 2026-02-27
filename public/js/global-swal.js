
$(document).ready(function() {
    // Global SweetAlert2 Confirmation Logic for .btn-confirm
    $(document).on('click', '.btn-confirm', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');
        let title = $(this).data('title') || 'Konfirmasi';
        let text = $(this).data('text') || 'Apakah Anda yakin?';
        let icon = $(this).data('icon') || 'question'; // default icon
        let confirmBtnText = $(this).data('confirm-text') || 'Ya';
        let confirmBtnColor = $(this).data('confirm-color') || '#3085d6'; // default blue

        // If 'warning' icon, use yellow/orange warning color if not specified
        if (icon === 'warning' && !$(this).data('confirm-color')) {
             confirmBtnColor = '#d33'; // or warning color
        }
        // If 'danger' or delete action, usually red
        if ($(this).hasClass('btn-danger')) {
             confirmBtnColor = '#d33';
             if(!$(this).data('icon')) icon = 'warning';
        }

        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: confirmBtnColor,
            cancelButtonColor: '#d33',
            confirmButtonText: confirmBtnText,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                if (form.length > 0) {
                    form.submit();
                } else {
                    // Handle non-form links if any
                    let href = $(this).attr('href');
                    if(href && href !== '#') {
                        window.location.href = href;
                    }
                }
            }
        });
    });
});

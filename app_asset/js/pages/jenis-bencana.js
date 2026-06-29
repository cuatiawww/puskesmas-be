(function() {
    'use strict';

    // Handle form submission for create/update
    $(document).ready(function() {
        const form = $('form');
        
        if (form.length === 0) return;

        // Prevent default form submission
        form.on('submit', function(e) {
            e.preventDefault();

            const url = form.attr('action') || window.location.href;

            $.ajax({
                type: 'POST',
                url: url,
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message || 'Data berhasil disimpan',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                        window.location.href = (window.APP_BASE_URL || '') + '/jenis-bencana/index';
                        });
                    } else {
                        // Show validation errors
                        if (response.errors) {
                            let errorMsg = '<div style="text-align: left;">';
                            Object.keys(response.errors).forEach(field => {
                                const errors = response.errors[field];
                                if (Array.isArray(errors)) {
                                    errors.forEach(error => {
                                        errorMsg += '<p style="margin: 5px 0; color: #dc3545;">• ' + error + '</p>';
                                    });
                                }
                            });
                            errorMsg += '</div>';

                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Gagal',
                                html: errorMsg,
                                confirmButtonText: 'Perbaiki'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message || 'Terjadi kesalahan saat menyimpan data'
                            });
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan pada server'
                    });
                }
            });
        });
    });
})();

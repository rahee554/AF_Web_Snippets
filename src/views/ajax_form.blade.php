@push('scripts')
<script>
    $(document).ready(function() {
        // Global variables
        var env = '{{ app()->environment() }}';
        var logType = '{{ $logType ?? 'console' }}';
        // Fetching the onSuccess configurations or setting default values
        var onSuccess = @json($onSuccess ?? []);
        var successLogType = onSuccess.log ?? 'alert';
        var reloadOnSuccess = (onSuccess.reload ?? true) === true;
        var dataTableId = onSuccess.dtable ?? '';

        $('#{{ $id ?? '' }}').submit(function(e) {
            e.preventDefault();

            var $form = $(this);
            var $submitButton = $form.find('button[type="submit"]');
            var formData = $form.serialize();

            // Disable the submit button to prevent multiple submissions
            $submitButton.prop('disabled', true);

            $.ajax({
                url: '{{ route($route) }}',
                type: '{{ strtoupper($method ?? 'POST') }}',
                data: formData,
                success: function(response) {
                    if (successLogType === 'swal') {
                        Swal.fire({
                            title: 'Success',
                            text: response.message,
                            icon: 'success'
                        }).then((result) => {
                            if (result.isConfirmed && reloadOnSuccess) {
                                location.reload();
                            } else if (result.isConfirmed && dataTableId) {
                                $('#' + dataTableId).DataTable().ajax.reload();
                            }
                        });
                    } else if (successLogType === 'alert') {
                        alert(response.message);
                        if (reloadOnSuccess) {
                            location.reload();
                        } else if (dataTableId) {
                            $('#' + dataTableId).DataTable().ajax.reload();
                        }
                    } else if (env !== 'production') {
                        console.log(response.message);
                        if (reloadOnSuccess) {
                            location.reload();
                        } else if (dataTableId) {
                            $('#' + dataTableId).DataTable().ajax.reload();
                        }
                    }
                },
                error: function(xhr) {
                    // Re-enable the submit button in case of an error
                    $submitButton.prop('disabled', false);

                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessage = Object.values(errors).map(function(error) {
                            return '<p>' + error + '</p>';
                        }).join('');
                        if (env !== 'production') {
                            if (logType === 'swal') {
                                Swal.fire({
                                    title: 'Validation Error',
                                    html: errorMessage,
                                    icon: 'error'
                                });
                            } else {
                                console.log(errors);
                            }
                        }
                    } else {
                        var errorMessage = 'Error occurred. Please try again.';
                        if (env !== 'production') {
                            errorMessage += '<br>' + xhr.responseText;
                        }
                        if (logType === 'swal') {
                            Swal.fire({
                                title: 'Error',
                                html: errorMessage,
                                icon: 'error'
                            });
                        } else {
                            console.log('Error occurred. Please try again.');
                            if (env !== 'production') {
                                console.log(xhr.responseText); // Log full error response
                            }
                        }
                    }
                }
            });
        });
    });
</script>
@endpush

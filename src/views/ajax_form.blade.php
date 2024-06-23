@push('scripts')
<script>
    $(document).ready(function() {
        // Assuming you have a global variable for environment set in your Blade template
        var env = '{{ app()->environment() }}';
        // Assuming you have a global variable for logType set in your Blade template
        var logType = '{{ $logType ?? 'console' }}';

        $('#{{ $id ?? '' }}').submit(function(e) {
            e.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: '{{ route($route) }}',
                type: '{{ strtoupper($method ?? 'POST') }}',
                data: formData,
                success: function(response) {
                    console.log(response.message);
                    // Handle success scenario (e.g., show success message)
                    
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        if (env !== 'production') {
                            if (logType === 'swal') {
                                Swal.fire({
                                    title: 'Validation Error',
                                    html: Object.values(errors).join('<br>'),
                                    icon: 'error'
                                });
                            } else {
                                console.log(errors);
                            }
                        }
                        // Handle validation errors (display to the user if needed)
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

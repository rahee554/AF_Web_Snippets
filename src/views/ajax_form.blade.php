@push('scripts')
<script>
    $(document).ready(function() {
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
                        console.log(errors);
                        // Handle validation errors
                    } else {
                        console.log('Error occurred. Please try again.');
                        console.log(xhr.responseText); // Log full error response
                    }
                }
            });
        });
    });
</script>
@endpush
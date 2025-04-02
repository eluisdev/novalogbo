@if (session('success'))
<script>
    Swal.fire({
        text: "{{ session('success') }}",
        icon: 'success',
        confirmButtonText: 'Entendido'
    });
</script>
@endif

@if (session('status'))
<script>
    Swal.fire({
        text: "{{ session('status') }}",
        icon: 'info',
        confirmButtonText: 'Entendido'
    });
</script>
@endif

@if (session('message'))
<script>
    Swal.fire({
        text: "{{ session('message') }}",
        icon: 'info',
        confirmButtonText: 'Entendido'
    });
</script>
@endif

@if (session('error'))
<script>
    Swal.fire({
        text: "{{ session('error') }}",
        icon: 'error',
        confirmButtonText: 'Entendido'
    });
</script>
@endif
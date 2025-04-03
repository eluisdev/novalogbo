@if (session('success'))
<script>
    Swal.fire({
        text: "{{ session('success') }}",
        icon: 'success',
        confirmButtonColor: "#0B628D",
        confirmButtonText: 'Entendido'
    });
</script>
@endif

@if (session('status'))
<script>
    Swal.fire({
        text: "{{ session('status') }}",
        icon: 'info',
        confirmButtonColor: "#0B628D",
        confirmButtonText: 'Entendido'
    });
</script>
@endif

@if (session('message'))
<script>
    Swal.fire({
        text: "{{ session('message') }}",
        icon: 'info',
        confirmButtonColor: "#0B628D",
        confirmButtonText: 'Entendido'
    });
</script>
@endif

@if (session('error'))
<script>
    Swal.fire({
        text: "{{ session('error') }}",
        icon: 'error',
        confirmButtonColor: "#0B628D",
        confirmButtonText: 'Entendido'
    });
</script>
@endif
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Select2 sin opciones en HTML</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .clonable-container {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .btn-clonar, .btn-eliminar {
            margin-top: 10px;
            padding: 8px 15px;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-clonar { background-color: #4CAF50; }
        .btn-eliminar { background-color: #f44336; }
    </style>
</head>
<body>

    <div class="container">
        <h1>Welcome to Laravel</h1>
        <p>This is a simple Laravel application.</p>
        <p>Laravel version: {{ Illuminate\Foundation\Application::VERSION }}</p>
        <p>PHP version: {{ PHP_VERSION }}</p>
        <form
        action=""
        method="post" {{ route('word.generate') }}>
            @csrf
            <input type="text" name="name" placeholder="Enter your name">
            <button type="submit">Submit</button>
        </form>
    </div>
    
    <button id="btn-agregar" class="btn-clonar">Agregar otro elemento</button>
    
    <script>
        $(document).ready(function() {
            // Datos que podrían venir de una API o ser definidos aquí
            const paises = [
                {id: 'mx', text: 'México'},
                {id: 'es', text: 'España'},
                {id: 'co', text: 'Colombia'},
                {id: 'ar', text: 'Argentina'},
                {id: 'pe', text: 'Perú'}
            ];

            // Función para inicializar Select2 con datos dinámicos
            function inicializarSelect2(elemento) {
                elemento.select2({
                    data: paises, // Asignamos los datos aquí
                    placeholder: "Selecciona un país",
                    allowClear: true
                });
            }

            // Inicializar el primero
            inicializarSelect2($('.select-pais'));

            let contador = 1;
            
            $('#btn-agregar').click(function() {
                contador++;
                
                let nuevoElemento = $('.clonable-container:first').clone();
                nuevoElemento.attr('data-id', contador);
                nuevoElemento.find('.contador').text(contador);
                
                // Limpiamos el select clonado (por si acaso)
                let select = nuevoElemento.find('.select-pais').html('');
                
                $('#contenedor-principal').append(nuevoElemento);
                
                // Inicializamos Select2 con los datos
                inicializarSelect2(select);
            });

            $(document).on('click', '.btn-eliminar', function() {
                if($('.clonable-container').length > 1) {
                    $(this).closest('.clonable-container').remove();
                } else {
                    alert("Debe haber al menos un elemento.");
                }
            });
        });
    </script>
</body>
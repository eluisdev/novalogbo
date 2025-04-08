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
    <div id="contenedor-principal">
        <div class="clonable-container" data-id="1">
            <h3>Elemento #<span class="contador">1</span></h3>
            <label>Selecciona un país:</label>
            <!-- Select sin opciones -->
            <select class="select-pais" style="width: 200px;"></select>
            <button class="btn-eliminar">Eliminar</button>
        </div>
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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Select2 con API en campos clonables</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .clonable-container {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .btn-agregar {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-eliminar {
            background-color: #f44336;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }
        .select2-container {
            min-width: 200px;
        }
    </style>
</head>
<body>
    <div id="contenedor-principal">
        <div class="clonable-container" data-id="1">
            <h3>Elemento #<span class="contador">1</span></h3>
            <label>Buscar país:</label>
            <select class="select-pais" style="width: 100%;"></select>
            <button class="btn-eliminar">Eliminar</button>
        </div>
    </div>
    
    <button id="btn-agregar" class="btn-agregar">+ Agregar elemento</button>

    <script>
        $(document).ready(function() {
            // Función para inicializar Select2 con AJAX
            function inicializarSelect2(elemento) {
                elemento.select2({
                    ajax: {
                        url: 'https://restcountries.com/v3.1/all',
                        dataType: 'json',
                        delay: 250, // Tiempo de espera después de teclear
                        data: function(params) {
                            return {
                                q: params.term, // Término de búsqueda
                                page: params.page
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;

                            // Mapeamos los resultados de la API al formato que necesita Select2
                            var resultados = data.map(function(pais) {
                                return {
                                    id: pais.cca2, // Código de país ISO
                                    text: pais.name.common // Nombre del país
                                };
                            });

                            return {
                                results: resultados,
                                pagination: {
                                    more: false // Puedes implementar paginación aquí si la API lo permite
                                }
                            };
                        },
                        cache: true
                    },
                    placeholder: "Escribe para buscar un país",
                    minimumInputLength: 2, // Mínimo de caracteres para iniciar búsqueda
                    allowClear: true
                });
            }

            // Inicializar el primer select
            inicializarSelect2($('.select-pais'));

            let contador = 1;

            // Evento para agregar nuevos elementos
            $('#btn-agregar').click(function() {
                contador++;
                
                // Clonar el primer elemento
                let nuevoElemento = $('.clonable-container:first').clone();
                
                // Actualizar atributos
                nuevoElemento.attr('data-id', contador);
                nuevoElemento.find('.contador').text(contador);
                
                // Limpiar el select clonado
                let nuevoSelect = nuevoElemento.find('.select-pais').html('');
                
                // Agregar al DOM
                $('#contenedor-principal').append(nuevoElemento);
                
                // Inicializar Select2 en el nuevo elemento
                inicializarSelect2(nuevoSelect);
            });

            // Evento para eliminar elementos (usando delegación para elementos dinámicos)
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
</html>
</html>
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

        .btn-clonar,
        .btn-eliminar {
            margin-top: 10px;
            padding: 8px 15px;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-clonar {
            background-color: #4CAF50;
        }

        .btn-eliminar {
            background-color: #f44336;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Welcome to Laravel</h1>
        <p>This is a simple Laravel application.</p>
        <p>Laravel version: {{ Illuminate\Foundation\Application::VERSION }}</p>
        <p>PHP version: {{ PHP_VERSION }}</p>

    </div>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Prueba de Descarga</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('invoice.download') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="id" class="form-label">ID de Billing Note</label>
                                <input type="number" class="form-control" id="id" name="id" required>
                            </div>
                            <div class="mb-3">
                                <label for="template" class="form-label">Visible</label>
                                <select class="form-select" id="visible" name="visible" required>
                                    <option value="1" selected>Si</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="template" class="form-label">Paralelo</label>
                                <select class="form-select" id="is_parallel" name="is_parallel" required>
                                    <option value="1" selected>Si</option>
                                    <option value="0">No</option>
                                </select>
                            </div>


                            <button type="submit" class="btn btn-primary">Descargar Word</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

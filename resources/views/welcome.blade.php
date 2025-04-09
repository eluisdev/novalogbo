<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

    <div class="container">
        <h1>Welcome to Laravel</h1>
        <p>This is a simple Laravel application.</p>
        <p>Laravel version: {{ Illuminate\Foundation\Application::VERSION }}</p>
        <p>PHP version: {{ PHP_VERSION }}</p>
        <form
        method="POST"
        {{-- action="{{ route('billing-note.download') }}" --}}
        >
            @csrf

            <div>
                <label for="quotation_id">ID de Cotización:</label>
                <input type="number" name="quotation_id" id="quotation_id" value="{{ old('quotation_id') }}">
                @error('quotation_id')
                    <div style="color: red;">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="visible">Visible:</label>
                <select name="visible" id="visible">
                    <option value="1" {{ old('visible') === '1' ? 'selected' : '' }}>Sí</option>
                    <option value="0" {{ old('visible') === '0' ? 'selected' : '' }}>No</option>
                </select>
                @error('visible')
                    <div style="color: red;">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit">Enviar</button>
        </form>
    </div>
</body>
</html>

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
        <form action="" method="post" {{ route('word.generate') }}>
            @csrf
            <input type="text" name="name" placeholder="Enter your name">
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>

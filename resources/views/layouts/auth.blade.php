<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Novalogbo Auth</title>
    @vite("resources/css/app.css")
</head>
<body>
    <div class="bg-gray-300/70">
        <section class="min-h-screen flex flex-col items-center justify-center p-4">
            @yield("auth-action")
        </section>
    </div>
</body>
</html>
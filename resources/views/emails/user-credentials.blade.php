<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tus credenciales de acceso</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #3490dc;
        }
        .credentials {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #3490dc;
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.9em;
            color: #666;
        }
        .button {
            display: inline-block;
            background-color: #3490dc;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .imglogo {
            width: 160px;
            height: 140px;
        }

        @media ( max-width : 550px) {
            .title {
                font-size: 1.5rem;
            }
        } 
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://www.novalogisticsrl.com/wp-content/uploads/2022/03/Logotipo-sin-fondo-2.png" class="imglogo"/>
            <h1 class="title">¡Bienvenido(a) a Novalogbosrl!</h1>
            <p>Tu cuenta ha sido creada exitosamente.</p>
        </div>

        <p>Hola <strong>{{ $user->name }} {{ $user->surname }}</strong>,</p>

        <p>Te damos la bienvenida a nuestra plataforma. A continuación encontrarás tus credenciales de acceso:</p>

        <div class="credentials">
            <p><strong>Usuario:</strong> {{ $user->username }}</p>
            <p><strong>Contraseña:</strong> {{ $password }}</p>
        </div>

        <p>Por razones de seguridad, te recomendamos cambiar tu contraseña después de iniciar sesión por primera vez.</p>

        <div style="text-align: center;">
            <a href="{{ url('/login') }}" class="button">Iniciar Sesión</a>
        </div>

        <div class="footer">
            <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>

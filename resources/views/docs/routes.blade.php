<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Documentación de Rutas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 2rem;
            background: #f9f9f9;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <h1>📘 Documentación de Rutas</h1>
    <table>
        <thead>
            <tr>
                <th>Método</th>
                <th>URI</th>
                <th>Nombre</th>
                <th>Middleware</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($routes as $route)
                <tr>
                    <td><span class="badge">{{ $route['method'] }}</span></td>
                    <td>{{ $route['uri'] }}</td>
                    <td>{{ $route['name'] }}</td>
                    <td>{{ $route['middleware'] }}</td>
                    <td>{{ $route['description'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

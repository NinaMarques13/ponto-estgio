<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acesso Negado</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            text-align: center;
            background: white;
            padding: 60px 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
        }
        
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 20px;
            line-height: 1;
        }
        
        h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 15px;
        }
        
        p {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background 0.3s ease;
        }
        
        .btn:hover {
            background: #764ba2;
        }
        
        .btn-secondary {
            background: #ddd;
            color: #333;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background: #ccc;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">403</div>
        <h1>Acesso Negado</h1>
        <p>Desculpe, você não tem permissão para acessar este recurso.</p>
        <p style="font-size: 14px; color: #999;">Se você acredita que isso é um erro, entre em contato com o administrador.</p>
        
        <a href="{{ url('/') }}" class="btn">Voltar ao Início</a>
        <a href="javascript:history.back()" class="btn btn-secondary">Voltar</a>
    </div>
</body>
</html>

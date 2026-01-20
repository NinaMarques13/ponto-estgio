<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #f2f2f2; border: 1px solid #ddd; padding: 10px; font-size: 12px; }
        td { border: 1px solid #ddd; padding: 8px; font-size: 11px; text-align: center; }
        .footer { margin-top: 30px; font-size: 10px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>FOLHA DE PONTO - RELATÓRIO ADMINISTRATIVO</h2>
        <p>Gerado em: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID ESTAGIÁRIO</th>
                <th>NOME COMPLETO</th>
                <th>REGISTRO (DATA/HORA)</th>
                <th>MOTIVO/JUSTIFICATIVA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registros as $registro)
                <tr>
                    <td>{{ $registro->estagiario_id }}</td>
                    <td>{{ $registro->estagiario->name ?? 'USUÁRIO NÃO ENCONTRADO' }}</td>
                    <td>{{ $registro->created_at->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $registro->ds_motivo ?? 'SEM OBSERVAÇÕES' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema de Gestão de Ponto - PM/PR</p>
    </div>
</body>
</html>
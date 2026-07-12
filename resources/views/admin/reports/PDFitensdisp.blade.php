<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Relatório de Equipamentos Disponíveis</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1 { text-align: center; font-size: 18px; margin-bottom: 4px; }
        .subtitulo { text-align: center; color: #777; margin-bottom: 20px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #fafafa; }
        .vazio { text-align: center; color: #999; padding: 20px; }
    </style>
</head>
<body>
    <h1>Relatório de Equipamentos Disponíveis</h1>
    <p class="subtitulo">Gerado em {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>

    @if($unidades->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Modelo</th>
                <th>Ref. IPVC</th>
                <th>Código LIA</th>
                <th>Preço / dia</th>
            </tr>
        </thead>
        <tbody>
            @foreach($unidades as $unidade)
            <tr>
                <td>{{ $unidade->item->nome ?? '—' }}</td>
                <td>{{ $unidade->item->model ?? '—' }}</td>
                <td>{{ $unidade->ipvc_ref ?? '—' }}</td>
                <td>{{ $unidade->lia_code }}</td>
                <td>{{ number_format($unidade->item->price_day, 2, ',', '.') }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p style="margin-top: 15px;"><strong>Total de unidades disponíveis:</strong> {{ $unidades->count() }}</p>
    @else
    <p class="vazio">Não existem equipamentos disponíveis de momento.</p>
    @endif
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <title>Relatório de Reservas Lia</title>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th style="width:140px;">Reservante</th>
                <th>Descrição</th>
                <th style="width:70px;">Custo</th>
                <th style="width:105px;">Data de Início</th>
                <th style="width:95px;">Data de Fim</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservas as $reserva)
                <tr>
                    @foreach ($users as $user)
                    @if ($user->id == $reserva->user_id)
                    <td>{{ $user->name }}</td>
                    @endif
                    @endforeach
                    <td>{{ $reserva->description }}</td>
                    <td>{{ number_format($reserva->cost, 2, ',', '.') }} €</td>
                    <td>{{\Carbon\Carbon::parse($reserva->start_date)->format('d/m/Y')}}</td>
                    <td>{{\Carbon\Carbon::parse($reserva->end_date)->format('d/m/Y')}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
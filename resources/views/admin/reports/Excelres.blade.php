<!DOCTYPE html>
<html>
<head>
    <title>Relatório de Reservas</title>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th style="width:140px;">Requerente</th>
                <th>Descrição</th>
                <th style="width:70px;">Custo</th>
                <th style="width:160px;">Centro de Custos</th>
                <th style="width:110px;">Cíclica</th>
                <th style="width:105px;">Data de Início</th>
                <th style="width:95px;">Data de Fim</th>
                <th style="width:120px;">Data de Entrega</th>
                <th style="width:125px;">Data de Retorno</th>
                <th style="width:590px;">Equipamento</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservas as $reserva)
                <tr>
                    <td>{{ $reserva->user->name }}</td>
                    <td>{{ $reserva->description }}</td>
                    <td>{{ number_format($reserva->cost, 2, ',', '.') }} €</td>
                    <td>{{ $reserva->costcenter->name }}</td>
                    <td>{{ $reserva->ciclica->dia_semana }}</td>
                    <td>{{\Carbon\Carbon::parse($reserva->start_date)->format('d/m/Y')}}</td>
                    <td>{{\Carbon\Carbon::parse($reserva->end_date)->format('d/m/Y')}}</td>
                    <td>
                    @if ($reserva->delivery_date)
                        {{ \Carbon\Carbon::parse($reserva->delivery_date)->format('d/m/Y') }}
                    @endif
                    </td>
                    <td>
                    @if ($reserva->return_date)
                        {{ \Carbon\Carbon::parse($reserva->return_date)->format('d/m/Y') }}
                    @endif
                    </td>
                    <td>
                        @foreach ($reservas_itens as $reservas_item)
                        @foreach ($itens as $item)
                        @if($reservas_item->reserve_id == $reserva->id && $item->id == $reservas_item->item_id)
                            <li>Item: {{ $item->nome }}; Código Lia: {{ $item->lia_code }}</li><br>
                        @endif
                        @endforeach
                        @endforeach

                        @foreach ($reservas_kits as $reservas_kit)
                        @foreach ($kits as $kit)
                        @if($reservas_kit->reserve_id == $reserva->id && $kit->id == $reservas_kit->kit_id)
                            <li>Kit: {{ $kit->description }}; Código Lia: {{ $kit->lia_code }}</li><br>
                        @endif
                        @endforeach
                        @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
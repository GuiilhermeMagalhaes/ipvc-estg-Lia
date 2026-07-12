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
                        {{-- 1. PROCESSAR OS ITENS PEDIDOS --}}
                        @foreach ($reserva->itemReserves as $itemReserve)
                            {{-- Verificamos se este item específico já tem unidades físicas atribuídas na tabela pivot --}}
                            @if ($itemReserve->itemUnityReserves->isNotEmpty())
                                {{-- Se tem unidades associadas, listamos cada uma com o respetivo Código LIA --}}
                                @foreach ($itemReserve->itemUnityReserves as $unityReserve)
                                    <li>
                                        Item: {{ $itemReserve->item->nome }}; 
                                        Código Lia: {{ $unityReserve->itemUnity->lia_code }}
                                    </li><br>
                                @endforeach
                            @else
                                {{-- Se ainda não tem unidades associadas, mostra a quantidade solicitada --}}
                                <li>
                                    Item: {{ $itemReserve->item->nome }} 
                                    (Qtd: {{ $itemReserve->quantity }})
                                    <span style="color: gray; font-style: italic;">- Aguardar atribuição de unidade (Sem LIA)</span>
                                </li><br>
                            @endif
                        @endforeach

                        {{-- 2. PROCESSAR OS KITS PEDIDOS --}}
                        @foreach ($reserva->kitReserves as $kitReserve)
                            {{-- Verificamos se este kit específico já tem malas físicas atribuídas na tabela pivot --}}
                            @if ($kitReserve->kitUnityReserves->isNotEmpty())
                                {{-- Se tem malas associadas, listamos cada uma com o respetivo Código LIA --}}
                                @foreach ($kitReserve->kitUnityReserves as $unityReserve)
                                    <li>
                                        Kit: {{ $kitReserve->kit->name }}; 
                                        Código Lia: {{ $unityReserve->kitUnity->lia_code }}
                                    </li><br>
                                @endforeach
                            @else
                                {{-- Se ainda não tem malas associadas, mostra a quantidade solicitada --}}
                                <li>
                                    Kit: {{ $kitReserve->kit->name }} 
                                    (Qtd: {{ $kitReserve->quantity }})
                                    <span style="color: gray; font-style: italic;">- Aguardar atribuição de unidade (Sem LIA)</span>
                                </li><br>
                            @endif
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
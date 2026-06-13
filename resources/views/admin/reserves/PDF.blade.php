<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        .header-container {
            display: flex;
            justify-content: space-between;
            position: relative; /* Para posicionar o h2 de forma absoluta relativo a este container */
        }

        .left,
        .right {
            width: 100%;
        }

        .right {
            text-align: right;
        }

        .absolute-center {
            position: absolute;
            top: 22%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header-container">
            <div class="left">
                <img src="{{'data:image/png;base64,'.base64_encode(file_get_contents(public_path('images/default.png')))}}" width="250" height="100">
            </div>
            <div class="right">
                <h4>Técnico Responsável</h4>
                <p>
                    {{ Auth::user()->name }}<br>
                    {{ Auth::user()->email }}<br>
                    {{ Auth::user()->phone }}<br>
                    lia.estg.ipvc.pt
                </p>
            </div>
            <div class="absolute-center">
                <h3 style="margin-top: 0;">Requisição de Material - Laboratório de Interação e Audiovisuais (L 3.6)</h3>
            </div>
        </div>
        <p>
            Requisição feita por {{ $reserve->user->name }} da/o {{ $reserve->costCenter->name }} ( Contactos: {{ $reserve->user->email }} , {{ $reserve->user->phone }} ). O motivo da reserva é '{{ $reserve->description }}' com início a
            {{\Carbon\Carbon::parse($reserve->start_date)->format('d/m/Y')}} até {{\Carbon\Carbon::parse($reserve->end_date)->format('d/m/Y')}}.
        </p>

        <h3 style="text-align:center;">Equipamento</h3>
        <ul>
            {{-- KITS DA RESERVA --}}
            @foreach ($reserve_kits as $rk)
                @php
                    $kit = $kits->firstWhere('id', $rk->kit_id);
                    
                    // Vai buscar os LIAs atribuídos a este pedido de Kit na tabela pivot
                    $assignedKitUnities = \Illuminate\Support\Facades\DB::table('kit_unity_reserve')
                        ->join('kit_unity', 'kit_unity_reserve.kit_unity_id', '=', 'kit_unity.id')
                        ->where('kit_unity_reserve.kit_reserve_id', $rk->id)
                        ->pluck('kit_unity.lia_code');
                    
                    // Formata os LIAs (se houver vários, separa por vírgula)
                    $liaKitText = $assignedKitUnities->count() > 0 
                                  ? implode(', ', $assignedKitUnities->toArray()) 
                                  : 'Pendente de Entrega';
                @endphp

                @if($kit)
                <li>
                    <b>Kit:</b> {{ $kit->name }} - {{ $kit->description }} 
                    (<b>Qtd:</b> {{ $rk->quantity ?? 1 }}) 
                    - <b>LIA(s):</b> {{ $liaKitText }} 
                    - {{ $kit->price_day }}€ por dia
                </li>
                @endif
            @endforeach

            {{-- ITENS DA RESERVA --}}
            @foreach ($reserve_itens as $ri)
                @php
                    $item = $itens->firstWhere('id', $ri->item_id);

                    // Vai buscar os LIAs atribuídos a este pedido de Item na tabela pivot
                    $assignedUnities = \Illuminate\Support\Facades\DB::table('item_unity_reserve')
                        ->join('item_unity', 'item_unity_reserve.item_unity_id', '=', 'item_unity.id')
                        ->where('item_unity_reserve.item_reserve_id', $ri->id)
                        ->pluck('item_unity.lia_code');
                    
                    // Formata os LIAs (se houver vários, separa por vírgula)
                    $liaText = $assignedUnities->count() > 0 
                               ? implode(', ', $assignedUnities->toArray()) 
                               : 'Pendente de Entrega';
                @endphp

                @if($item)
                <li>
                    <b>Item:</b> {{ $item->nome }} - {{ $item->model }} 
                    (<b>Qtd:</b> {{ $ri->quantity ?? 1 }}) 
                    - <b>LIA(s):</b> {{ $liaText }} 
                    - {{ $item->price_day }}€ por dia
                </li>
                @endif
            @endforeach
        </ul>

        <div class="total-cost">
            <b>Custo Total da Reserva:</b> 
            <span style="color: #28a745; font-weight: bold;">{{ number_format($reserve->cost, 2, ',', '.') }} €</span>
        </div>

        <h4>
            Após receber o equipamento e ter verificado a sua composição e o seu estado de conservação declaro
            que tomei conhecimento que:
        </h4>
        <ul>
            <li>O equipamento pertence à Escola Superior de Tecnologia e Gestão do Instituto Politécnico de Viana do
                Castelo e está alocado ao Laboratório de Interação e Audiovisuais (L 3.6);</li>
            <li>O equipamento apenas poderá ser utilizado como suporte à realização de trabalhos no âmbito de
                aulas/projectos;</li>
            <li>Não é permitida a disponibilização do equipamento a terceiros;</li>
            <li>A duração da requisição do equipamento é apenas entre o período mencionado anteriormente. Findo este
                período o equipamento deve ser devolvido na data indicada no mesmo estado de conservação em que me foi
                entregue. Caso tal não aconteça estarei sujeito às penalizações indicadas nos termos da avaliação das
                disciplinas do curso em que me encontro inscrito assim como do regulamento interno da unidade orgânica a
                que pertenço.</li>
        </ul>
        <br>
        <p>
            Observações:__________________________________________________________________________
            _____________________________________________________________________________________
            _____________________________________________________________________________________
        </p>
        <br>
        <pre>Requerente:____________________        Requerente:____________________<br>Técnico LIA:___________________        Técnico LIA:___________________<br>Data Levantamento:__/__/____           Data Entrega:__/__/____</pre>
    </div>
</body>

</html>
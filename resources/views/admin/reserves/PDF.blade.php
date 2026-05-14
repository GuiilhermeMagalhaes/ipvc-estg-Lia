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
            @foreach ($reserve_kits as $reserve_kit)
            @foreach ($kits as $kit)
            @if ($kit->id == $reserve_kit->kit_id)
            <li>Kit: {{ $kit->name }} - {{ $kit->description }} - {{ $kit->lia_code }} - {{ $kit->price }}€</li>
            @endif
            @endforeach
            @endforeach
            @foreach ($reserve_itens as $reserve_item)
            @foreach ($itens as $item)
            @if ($item->id == $reserve_item->item_id)
            <li>Item: {{ $item->nome }} - {{ $item->model }} - {{ $item->serial_number }} - {{ $item->preco }}€</li>
            @endif
            @endforeach
            @endforeach
        </ul>

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

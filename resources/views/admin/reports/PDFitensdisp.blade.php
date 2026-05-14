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
                <h3 style="margin-top: 0;">Relatório de Equipamentos Disponíveis - Laboratório de Interação e Audiovisuais (L 3.6)</h3>
            </div>
        </div>
        <p>
            
        </p>

        <h3 style="text-align:center;">Equipamentos</h3>
        <ul>
            @foreach ($itens as $item)
            <li>Item: {{ $item->nome }} - {{ $item->model }} - {{ $item->serial_number }} - {{ $item->lia_code}}</li>
            @endforeach
        </ul>
        <br>
        <p>
            Observações:__________________________________________________________________________
            _____________________________________________________________________________________
            _____________________________________________________________________________________
        </p>
        <br>
        <pre>Técnico LIA:___________________<br>Data:__/__/____</pre>
    </div>
</body>

</html>

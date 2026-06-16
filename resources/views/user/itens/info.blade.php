@extends('index')

@section('content')
<link href="/css/custom.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js/dist/css/shepherd.css">



<div class="page-title">
    <nav class="breadcrumbs">
        <div class="container d-flex justify-content-between align-items-center">
            <ol class="d-flex mb-0">
                @if (session()->has('reserve'))
                    <li><a href="/"><i class="bi bi-house"></i></a></li>
                    <li><a href="{{ route('user.categoria.disponivel', $item->categoria_id) }}">{{ $item->itemCategorie->description }}</a></li>
                    <li><a class="current" href="#">{{ $item->nome }}</a></li>
                @else
                    <li><a href="/"><i class="bi bi-house"></i></a></li>
                    <li><a href="{{ route('user.categoria.index', $item->categoria_id) }}">{{ $item->itemCategorie->description }}</a></li>
                    <li><a class="current" href="#">{{ $item->nome }}</a></li>
                @endif
            </ol>
        </div>
        <button id="start-tutorial" class="btn btn-info" style="float: right; margin-right: 10px;">Ajuda</button>
    </nav>
</div>

<br>

<div class="container">
    <div class="card card-solid">
        <div class="card-body">
            <div class="row">

               
                <div class="col-12 col-sm-6 mt-4">
                    <img src="../{{ $item->image }}" width="100%" style="border-radius:10px;">
                </div>

                
                <div class="col-12 col-sm-6">
                    <h3 class="my-3">{{ $item->nome }}</h3>
                    <p>Modelo: {{ $item->model }}</p>

                    <p>Categoria: {{ $item->itemCategorie->description ?? 'Sem Categoria' }}</p>

                    <p>Observação: {{ $item->observation }}</p>
                    <p>Acessórios: {{ $item->acessorio }}</p>
                

                    <hr>

                  
                    <div class="container d-flex justify-content-center align-items-center text-center flex-column" id="calendar">
                        <div id="datepicker"></div>
                        @php
                            $reservasJS = [];
                            foreach ($reservas as $reserva) {
                                $reservasJS[] = [
                                    'start'      => $reserva['start_date'],
                                    'end'        => $reserva['end_date'],
                                    'ciclica_id' => $reserva['ciclica_id'] ?? 1,
                                ];
                            }
                        @endphp
                        @if (count($reservas) == 0)
                            <p>Nenhuma reserva encontrada para este item.</p>
                        @endif
                    </div>

                    <hr>
                    <div class="row" style="display: flex; justify-content: space-between; align-items: center; text-align: center; padding: 10px; margin: 10px 0;">

                        <div class="col-3" style="flex: 1;">
                            <h4>Preço</h4>
                            <h6>{{ number_format($item->price_day, 2, ',', '.') }} € / dia</h6>
                        </div>

                        <div class="col-4" style="flex: 1;">
                            <h4>Disponíveis</h4>
                            <h6 id="contador-disponivel">
                                @if(session()->has('reserve'))
                                    {{ $quantidadeDisponivel }} Unid. (Nestas datas)
                                @else
                                    {{ $quantidadeDisponivel }} Unid. Total
                                @endif
                            </h6>
                        </div>

                       <div class="col-4" style="flex: 1; text-align: right;">
                            @if(session()->has('reserve'))
                                {{-- Tem reserva: Mostra o formulário normal --}}
                                <form id="form-reservar" action="{{ route('item.add', ['id' => $item->id]) }}" method="post" style="display: flex; justify-content: flex-end;">
                                    @csrf
                                    @method('POST')
                                    <div class="form-group" style="margin-right: 10px; margin-top: 15px;">
                                        <input type="number" name="quantity" id="quantity" class="form-control" min="1" max="{{ $quantidadeDisponivel }}" value="{{ $quantidadeDisponivel > 0 ? 1 : 0 }}" {{ $quantidadeDisponivel <= 0 ? 'disabled' : '' }} style="width: 50px;">
                                    </div>
                                    <button type="submit" class="btn btn-outline-dark" id="item" style="margin-top: 15px;" {{ $quantidadeDisponivel <= 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-cart-plus fa-lg mr-2"></i>
                                        Reservar
                                    </button>
                                </form>
                            @else
                                {{-- NÃO tem reserva: Mostra um botão de aviso --}}
                                <div style="margin-top: 15px; display: flex; justify-content: flex-end;">
                                    <button type="button" class="btn btn-outline-secondary" onclick="alert('Por favor, inicie uma reserva primeiro (no menu lateral Reservas) para poder adicionar equipamentos ao carrinho.')">
                                        <i class="fas fa-cart-arrow-down fa-lg mr-2"></i>
                                        Reservar
                                    </button>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/shepherd.js/dist/js/shepherd.min.js"></script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tour = new Shepherd.Tour({
            defaultStepOptions: {
                scrollTo: true,
                cancelIcon: { enabled: true },
                classes: 'shepherd-theme-arrows',
                modalOverlayOpeningPadding: 5,
                modalOverlayOpeningRadius: 5
            }
        });

        tour.addStep({
            title: 'Item - Etapa 4/5',
            text: 'Se deseja adicionar este item à sua reserva, clique em "Reservar" (Para tal efeito, é necessário efetuar/começar uma reserva antes). Para finalizar, basta ir à página "Reserva".',
            attachTo: { element: '#item', on: 'left' },
            buttons: [{ text: 'Terminar', action: tour.complete }]
        });

        document.getElementById('start-tutorial').addEventListener('click', function () {
            tour.start();
        });
    });
</script>


<script>
    $(document).ready(function () {
        $('[data-toggle="popover"]').popover();
    });

    var ptDate = {
        previousMonth : 'Mês Anterior',
        nextMonth     : 'Próximo Mês',
        months        : ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        weekdays      : ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
        weekdaysShort : ['Dom','2ª','3ª','4ª','5ª','6ª','Sab']
    };

    // Recebemos o array já processado e limpo pelo Controlador!
    var reservas = @json($reservas);

    function parseDate(dateStr) {
        var parts = dateStr.split('/');
        return new Date(parts[2], parts[1] - 1, parts[0], 0, 0, 0);
    }

    var parsedReservas = reservas.map(function (reserva) {
        return {
            start: parseDate(reserva.start_date),
            end: parseDate(reserva.end_date)
        };
    });

    var picker = new Pikaday({
        field   : document.getElementById('datepicker'),
        i18n    : ptDate,
        bound   : false,
        minDate : new Date(),
        onDraw  : function () {
            var days = document.querySelectorAll('.pika-button');

            days.forEach(function (day) {
                var year    = day.getAttribute('data-pika-year');
                var month   = day.getAttribute('data-pika-month');
                var dayNum  = day.getAttribute('data-pika-day');
                var dayDate = new Date(year, month, dayNum, 0, 0, 0);

                var isHighlighted = false;

                // Verifica se este dia bate certo com algum dia esgotado
                parsedReservas.forEach(function (reserva) {
                    if (dayDate >= reserva.start && dayDate <= reserva.end) {
                        isHighlighted = true;
                    }
                });

                if (isHighlighted) {
                    day.classList.add('has-event');
                    day.title = "Stock esgotado neste dia"; // Adiciona um pequeno tooltip
                } else {
                    day.classList.remove('has-event');
                }
            });

            document.querySelector('.pika-single').style.zIndex = '1';
        }
    });
</script>


<script>
$('#form-reservar').on('submit', function (e) {
    e.preventDefault();

    var form = $(this);
    var quantidade = parseInt($('#quantity').val());
    
    // Extrai o número do texto atual
    var textoDisponivel = $('#contador-disponivel').text();
    var disponivel = parseInt(textoDisponivel.replace(/[^0-9]/g, ''));

    $.ajax({
        type : 'POST',
        url  : form.attr('action'),
        data : form.serialize(),
        success: function (response) {
            
            // CORRIGIDO: Variável 'quantidade' em vez de 'quantity'
            var novoDisponivel = disponivel - quantidade;
            if (novoDisponivel < 0) novoDisponivel = 0;
            
            // 1. Atualiza o texto do ecrã
            $('#contador-disponivel').text(novoDisponivel + ' Unid. (Nestas datas)');
            
            // 2. Atualiza o limite máximo da caixa de número
            $('#quantity').attr('max', novoDisponivel);
            
            // 3. Se acabar o stock, bloqueia o botão e o input
            if (novoDisponivel <= 0) {
                $('#quantity').val(0).prop('disabled', true);
                $('#item').prop('disabled', true);
            } else {
                $('#quantity').val(1);
            }

            // Alerta visual de sucesso
            alert('Item adicionado ao carrinho!');
        },
        error: function (xhr) {
            window.location.reload();
        }
    });
});
</script>

@endsection
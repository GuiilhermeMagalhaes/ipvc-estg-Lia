@extends('index')

@section('content')
<link href="/css/custom.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js/dist/css/shepherd.css">
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">

<div class="page-title">
    <nav class="breadcrumbs">
        <div class="container d-flex justify-content-between align-items-center">
            <ol class="d-flex mb-0">
                @if (session()->has('reserve'))
                <li><a href="/"><i class="bi bi-house"></i></a></li>
                <li><a href="{{ route('user.kits.disponivel') }}">Kits Disponíveis</a></li>
                <li><a class="current" href="#">{{ $kit->name }}</a></li>
                @else
                <li><a href="/"><i class="bi bi-house"></i></a></li>
                <li><a href="{{ route('user.kits.index') }}">Todos os Kits</a></li>
                <li><a class="current" href="#">{{ $kit->name }}</a></li>
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
                <div class="col-12 col-sm-6 mb-1">
                    <img src="../{{ $kit->image }}" width="100%" style="border-radius:10px;">
                </div>
                <div class="col-12 col-sm-6">
                    <h3 class="my-3">
                        {{ $kit->name }}
                    </h3>
                    <p>{{ $kit->description }}</p>
                    
                    <hr>
                    <div class="container d-flex justify-content-center align-items-center text-center flex-column" id="calendar">
                        <div id="datepicker"></div>
                        @php
                        $reservasJS = [];
                        foreach ($reservas as $reserva) {
                        $reservasJS[] = [
                        'start' => $reserva['start_date'],
                        'end' => $reserva['end_date']
                        ];
                        }
                        @endphp
                        @if(count($reservas) == 0)
                        <p>Nenhuma reserva encontrada para este kit.</p>
                        @endif
                    </div>
                    <hr>
                    <div class="row" style="display: flex; justify-content: space-between; align-items: center; text-align: center; padding: 10px; margin: 10px 0;">
                        <div class="col-3" style="flex: 1;">
                            <h4>Preço <i class="fas fa-info-circle" data-toggle="popover" data-trigger="hover" title="Informação de Preço" data-content="Este é o preço por dia do kit."></i></h4>
                            <h6>{{number_format($kit->price_day, 2, ',', '.')}} € / dia</h6>
                        </div>
                            <div class="col-4" style="flex: 1;">
                            <h4>Disponíveis</h4>
                            <h6>
                                {{-- 1. ALTERAÇÃO NO TEXTO --}}
                                @if(session()->has('reserve'))
                                    {{ $quantidadeDisponivel }} Unid. (Nestas datas)
                                @else
                                    {{ $kitCount }} Unid. Total
                                @endif
                            </h6>
                        </div>
                        <div class="col-4" style="flex: 1; text-align: right;">
                            <form action="{{ route('kit.add', ['id' => $kit->id]) }}" method="post" style="display: flex;">
                                @csrf
                                @method('POST')
                                <div class="form-group" style="margin-right: 10px; margin-top:15px;">
                                    {{-- 2. ALTERAÇÃO NO ATRIBUTO MAX --}}
                                    <input type="number" name="quantity" id="quantity" class="form-control" min="1" max="{{ session()->has('reserve') ? $quantidadeDisponivel : $kitCount }}" value="1" style="width: 50px;">
                                </div>
                                <button type="submit" class="btn btn-outline-dark" id="kit">
                                    <i class="fas fa-cart-plus fa-lg mr-2"></i>
                                    Reservar
                                </button>
                            </form>
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
            cancelIcon: {
                enabled: true
            },
            classes: 'shepherd-theme-arrows',
            modalOverlayOpeningPadding: 5,
            modalOverlayOpeningRadius: 5
        }
    });

    tour.addStep({
        title: 'Kit - Etapa 4/5',
        text: 'Se deseja adicionar este kit à sua reserva, clique em "Reservar" (Para tal efeito, é necessário efetuar/começar uma reserva antes). Para finalizar, basta ir à página "Reserva".',
        attachTo: {
            element: '#kit',
            on: 'left'
        },
        buttons: [
            {
                text: 'Terminar',
                action: tour.complete
            }
        ]
    });

    // Botão para iniciar o tutorial
    document.getElementById('start-tutorial').addEventListener('click', function () {
        tour.start();
    });
});

</script>
<script>
    $(document).ready(function() {
        $('[data-toggle="popover"]').popover();
    });

    var ptDate = {
        previousMonth: 'Mês Anterior',
        nextMonth: 'Próximo Mês',
        months: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        weekdays: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
        weekdaysShort: ['Dom', '2ª', '3ª', '4ª', '5ª', '6ª', 'Sab']
    };

    var reservas = @json($reservasJS);

    function parseDate(dateStr) {
        var parts = dateStr.split('/');
        return new Date(parts[2], parts[1] - 1, parts[0]);
    }

    var parsedReservas = reservas.map(function(reserva) {
        return {
            start: parseDate(reserva.start),
            end: parseDate(reserva.end)
        };
    });

    var picker = new Pikaday({
        field: document.getElementById('datepicker'),
        i18n: ptDate,
        bound: false,
        minDate: new Date(),
        onDraw: function() {
            // Seleciona todos os botões do Pikaday
            var days = document.querySelectorAll('.pika-button');

            // Itera sobre cada botão para verificar se está dentro de algum intervalo de reserva
            days.forEach(function(day) {
                var year = day.getAttribute('data-pika-year');
                var month = day.getAttribute('data-pika-month');
                var dayNum = day.getAttribute('data-pika-day');
                var dayDate = new Date(year, month, dayNum);

                var isHighlighted = false;

                // Verifica se o dia está dentro de algum intervalo de reserva
                parsedReservas.forEach(function(reserva) {
                    var start = reserva.start;
                    var end = reserva.end;

                    if (dayDate >= start && dayDate <= end) {
                        isHighlighted = true;
                    }
                });

                // Adiciona ou remove a classe 'has-event' com base na verificação
                if (isHighlighted) {
                    day.classList.add('has-event');
                } else {
                    day.classList.remove('has-event');
                }
            });

            // Define o z-index do container do Pikaday
            document.querySelector('.pika-single').style.zIndex = '1';
        }
    });
</script>
@endsection
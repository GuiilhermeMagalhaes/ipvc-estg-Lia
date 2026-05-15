@extends('adminlte::page')

@section('title', 'Painel')

@section('content')
<link href="/css/dashboard.css" rel="stylesheet">
<link href="/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<br>
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h4>Olá, {{ Auth::user()->name }} <i class="bi bi-person-raised-hand"></i></h4>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <a href="{{ route('reserves.ongoing') }}" class="card-link">
                <div class="card h-100">
                    <div class="card-body d-flex justify-content-center align-items-center text-center flex-column">
                        <div class="showCircle">
                            <div class="outerCounts">
                                <div class="innerCounts">
                                    <div id="counts">{{ $ongoingCount }}</div>
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="160px" height="160px">
                                <defs>
                                    <linearGradient id="GradientColor2">
                                        <stop offset="0%" stop-color="orange" />
                                        <stop offset="100%" stop-color="cyan" />
                                    </linearGradient>
                                </defs>
                                <circle cx="80" cy="80" r="70" stroke-linecap="round" style="stroke: url(#GradientColor2);" />
                            </svg>
                        </div>
                        <br>
                        <h5>Reservas em Curso</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('reserves.pending') }}" class="card-link">
                <div class="card h-100">
                    <div class="card-body d-flex justify-content-center align-items-center text-center flex-column">
                        <div class="showCircle">
                            <div class="outerCounts">
                                <div class="innerCounts">
                                    <div id="counts">{{ $pendingCount }}</div>
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="160px" height="160px">
                                <defs>
                                    <linearGradient id="GradientColor3">
                                        <stop offset="0%" stop-color="#373b44" />
                                        <stop offset="100%" stop-color="#4286f4" />
                                    </linearGradient>
                                </defs>
                                <circle cx="80" cy="80" r="70" stroke-linecap="round" style="stroke: url(#GradientColor3);" />
                            </svg>
                        </div>
                        <br>
                        <h5>Reservas Pendentes</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-center align-items-center text-center flex-column">
                    <?php
                    // Calcular percentuais pago
                    $percentPaid = ($totalCost - $totalDebt) / $totalCost * 100;
                    ?>
                    <div class="skill" id="progress-bar-1" data-percent="<?php echo $percentPaid; ?>">
                        <div class="outer">
                            <div class="inner">
                                <div class="number"></div>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="160px" height="160px">
                            <defs>
                                <linearGradient id="GradientColor">
                                    <stop offset="0%" stop-color="#e91e63" />
                                    <stop offset="100%" stop-color="#673ab7" />
                                </linearGradient>
                            </defs>
                            <circle cx="80" cy="80" r="70" stroke-linecap="round" />
                        </svg>
                    </div>
                    <br>
                    <h6 style="font-weight: 510;">
                        Valor Débito: {{ number_format($totalDebt, 2, ',', '.') }} € <br>
                        Valor Total: {{ number_format($totalCost, 2, ',', '.') }} €
                    </h6>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card" style="background-color: tomato;color:white;">
                <div class="card-body d-flex justify-content-center align-items-center text-center flex-column">
                    <h5>Reservas em Atraso</h5>
                    @foreach ($delayedReserves as $reserve)
                    <div class="row align-items-center">
                        <?php
                        $todaydate = date('Y-m-d');
                        $seconds_diff = strtotime($todaydate) - strtotime($reserve->end_date);
                        $days_diff = floor($seconds_diff / 3600 / 24);
                        ?>
                        <div class="col-auto">
                            <p>Reservante: {{ $reserve->user->name }}</p>
                        </div>
                        <div class="col-auto">
                            <p>{{ $days_diff }} dias em atraso!</p>
                        </div>
                        <div class="col-auto mb-3">
                            <a href="{{ route('reserves.show', $reserve->id) }}">
                                <i class="bi bi-plus-circle text-white" style="font-size: 1.5rem;"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card" style="background-color: goldenrod;color:white;">
                <div class="card-body d-flex justify-content-center align-items-center text-center flex-column">
                    <h5>Reservas a Terminar</h5>
                    @foreach ($ongoingReserves as $reserve)
                    <?php
                    $todaydate = date('Y-m-d');
                    $days_remaining = max(0, floor((strtotime($reserve->end_date) - strtotime($todaydate)) / 86400));
                    ?>
                    @if ($days_remaining <= 5) <div class="row align-items-center">
                        <div class="col-auto">
                            <p>Reservante: {{ $reserve->user->name }}</p>
                        </div>
                        <div class="col-auto">
                            <p>{{ $days_remaining }} dia/s para terminar!</p>
                        </div>
                        <div class="col-auto mb-3">
                            <a href="{{ route('reserves.show', $reserve->id) }}">
                                <i class="bi bi-plus-circle text-white" style="font-size: 1.5rem;"></i>
                            </a>
                        </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col-md-9">
        <div class="card" style="background-color:steelblue; color: white;">
            <div class="card-body d-flex justify-content-center align-items-center text-center flex-column">
                <h5>Relatórios</h5>
                <div class="container">
                    <hr>
                    <div class="relatoriospdf row align-items-center mb-2">
                        <div class="col">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex align-items-center">
                                    <a href="{{ route('pdfitensdisp-download') }}">
                                        <span>Relatório de Equipamentos Disponíveis</span>
                                        <i class="fa fa-file-pdf ml-1" style="font-size: 1.5rem;"></i>
                                    </a>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="{{ route('pdfitensind-download') }}">
                                        <span>Relatório de Equipamentos Indisponíveis (Ocultos)</span>
                                        <i class="fa fa-file-pdf ml-1" style="font-size: 1.5rem;"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="relatoriosexcel row align-items-center">
                        <div class="col">
                            <div class="d-flex justify-content-between">
                                <form action="{{ route('excelres-download') }}" method="POST">
                                    @csrf
                                    @method('POST')
                                    <div class="d-flex justify-content-center align-items-center text-center flex-column">
                                        <div class="d-flex align-items-center">
                                            <button type="submit" class="btn btn-link">
                                                <span>Relatório de Reservas</span>
                                                <i class="fa fa-file-excel ml-1" style="font-size: 1.5rem;"></i>
                                            </button>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col">
                                                <label for="dataInicio">De:</label>
                                                <input type="date" id="dataInicio" name="dataInicio" class="form-control">
                                            </div>
                                            <div class="col">
                                                <label for="dataFim">Até:</label>
                                                <input type="date" id="dataFim" name="dataFim" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <form action="{{ route('excelreslia-download') }}" method="POST">
                                    @csrf
                                    @method('POST')
                                    <div class="d-flex justify-content-center align-items-center text-center flex-column">
                                        <div class="d-flex align-items-center">
                                            <button type="submit" class="btn btn-link">
                                                <span>Relatório de Reservas LIA</span>
                                                <i class="fa fa-file-excel ml-1" style="font-size: 1.5rem;"></i>
                                            </button>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="form-group col">
                                                <label for="dataIniciolia">De:</label>
                                                <input type="date" id="dataIniciolia" name="dataIniciolia" class="form-control">
                                            </div>
                                            <div class="form-group col">
                                                <label for="dataFimlia">Até:</label>
                                                <input type="date" id="dataFimlia" name="dataFimlia" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <hr>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card" style="background-color:mediumpurple; color: white;">
            <div class="card-body d-flex justify-content-center align-items-center text-center flex-column">
                <h5>Número de Utilizadores</h5>
                <div class="d-flex justify-content-center align-items-center text-center">
                    <h4>{{$totalUsers}}</h4>
                    <i class="bi bi-person-fill mb-2" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    // Função para inicializar o progress bar
    function initializeProgressBar(progressBarElement) {
        const percentPaid = progressBarElement.getAttribute('data-percent');
        const numberElement = progressBarElement.querySelector('.number');
        const circle = progressBarElement.querySelector('circle');
        const totalLength = 472;
        const targetOffset = (totalLength * (100 - percentPaid)) / 100; // Calcula o offset final
        let counter = 0;
        let currentOffset = totalLength;
        const duration = 2000; // Duração da animação em milissegundos
        const startTime = performance.now();

        function animate(timestamp) {
            const elapsed = timestamp - startTime;
            const progress = Math.min(elapsed / duration, 1);
            currentOffset = totalLength - (totalLength - targetOffset) * progress;
            circle.style.strokeDashoffset = currentOffset;
            counter = Math.round(percentPaid * progress);
            numberElement.innerHTML = counter + "%<br>Pago";

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        }

        requestAnimationFrame(animate);
    }

    // Inicializar todos os progress bars
    document.querySelectorAll('.skill').forEach(initializeProgressBar);
</script>

@endsection
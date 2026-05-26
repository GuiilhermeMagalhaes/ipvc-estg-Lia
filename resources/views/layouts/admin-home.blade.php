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
                    // Adicionei uma proteção caso $totalCost seja 0, para evitar erro de divisão por zero.
                    $percentPaid = $totalCost > 0 ? (($totalCost - $totalDebt) / $totalCost * 100) : 0;
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
                    @if ($days_remaining <= 5) 
                    <div class="row align-items-center">
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
                                        <a href="{{ route('pdfitensdisp-download') }}" style="color: white;">
                                            <span>Relatório de Equipamentos Disponíveis</span>
                                            <i class="fa fa-file-pdf ml-1" style="font-size: 1.5rem;"></i>
                                        </a>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <a href="{{ route('pdfitensind-download') }}" style="color: white;">
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
                                                <button type="submit" class="btn btn-link" style="color: white;">
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
                                                <button type="submit" class="btn btn-link" style="color: white;">
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
                        <i class="bi bi-person-fill mb-2 ml-2" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h3 class="card-title m-0"><i class="bi bi-bar-chart-fill mr-2"></i> Requisições por Centro de Custo</h3>
                </div>
                <div class="card-body">
                    <canvas id="costCenterChart" style="min-height: 350px; height: 350px; max-height: 350px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h3 class="card-title m-0"><i class="bi bi-star-fill mr-2"></i> Top 10 Equipamentos e Kits</h3>
                </div>
                <div class="card-body">
                    <canvas id="topItemsChart" style="min-height: 350px; height: 350px; max-height: 350px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // --- Lógica do teu Progress Bar ---
    function initializeProgressBar(progressBarElement) {
        const percentPaid = progressBarElement.getAttribute('data-percent');
        const numberElement = progressBarElement.querySelector('.number');
        const circle = progressBarElement.querySelector('circle');
        const totalLength = 472;
        const targetOffset = (totalLength * (100 - percentPaid)) / 100;
        let counter = 0;
        let currentOffset = totalLength;
        const duration = 2000;
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
    document.querySelectorAll('.skill').forEach(initializeProgressBar);

    // --- Lógica dos Gráficos Chart.js ---
    document.addEventListener("DOMContentLoaded", function() {
        
        // --- 1. Gráfico dos Centros de Custo (Barras Verticais) ---
        var chartLabels = {!! json_encode($labels ?? []) !!};
        var chartValues = {!! json_encode($values ?? []) !!};

        var ctx1 = document.getElementById('costCenterChart').getContext('2d');
        var costCenterChart = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: chartLabels, 
                datasets: [{
                    label: 'Quantidade de Reservas',
                    data: chartValues,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // --- 2. Gráfico do Top 10 Equipamentos (Barras Horizontais) ---
        var nomesEquipamentos = {!! json_encode($topNomes ?? []) !!};
        var totaisEquipamentos = {!! json_encode($topValores ?? []) !!};

        var ctx2 = document.getElementById('topItemsChart').getContext('2d');
        var topItemsChart = new Chart(ctx2, {
            type: 'bar', 
            data: {
                labels: nomesEquipamentos,
                datasets: [{
                    label: 'Vezes Requisitado',
                    data: totaisEquipamentos,
                    backgroundColor: 'rgba(75, 192, 192, 0.7)', // Verde-água
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y', // Isto vira o gráfico na horizontal!
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });

    });
</script>
@endsection
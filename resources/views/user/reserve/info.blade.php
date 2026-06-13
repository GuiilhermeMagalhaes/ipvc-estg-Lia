@extends('index')

@section('content')
<link href="/css/custom.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js/dist/css/shepherd.css">
<div class="page-title">
    <nav class="breadcrumbs">
        <div class="container d-flex justify-content-between align-items-center">
            <ol class="d-flex mb-0">
                <li><a href="/"><i class="bi bi-house"></i></a></li>
                <li><a class="current" href="#">Informações da Reserva de Equipamentos</a></li>
            </ol>
        </div>
        <button id="start-tutorial" class="btn btn-info" style="float: right; margin-right: 10px;">Ajuda</button>
    </nav>
</div>
<br>
    <div class="card card-dark card-outline">
        <div class="card-body">
            <div>
                <div class="callout callout-info">
                    <h5>Motivo da Reserva</h5>
                    <p>{{ session()->get('reserve.description') }}</p>
                </div>
                <div class="callout callout-info">
                    <h5>Data de Início</h5>
                    <p>{{ \Carbon\Carbon::createFromFormat('Y-m-d', session()->get('reserve.start_date'))->format('d-m-Y') }}</p>
                </div>
                <div class="callout callout-info">
                    <h5>Data de Fim</h5>
                    <p>{{ \Carbon\Carbon::createFromFormat('Y-m-d', session()->get('reserve.end_date'))->format('d-m-Y') }}</p>
                </div>
                <div class="callout callout-info" id="kits">
                    <h5>Kits para Reserva</h5>
                    <table class="table">
                        <thead>
                            <th>Nome</th>
                            <th>Preço</th>
                            <th></th>
                        </thead>
                       @if (session()->has('reserve.kits'))
                            <tbody>
                                @foreach (session()->get('reserve.kits') as $kitId => $kitData)
                                    <tr>
                                        <td>{{ $kitData['name'] }}</td>
                                        
                                        <td>
                                            {{ number_format($kitData['price'], 2, ',', '.') }} € / dia 
                                            <small class="text-muted">(Qtd: {{ $kitData['quantity'] }})</small>
                                        </td>
                                        <td>
                                            <form action="{{ route('kit.remove', ['id' => $kitId]) }}" method="post">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="btn btn-outline-dark mt-auto" style="width: 140px;">Retirar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        @endif
                    </table>
                </div>
               <div class="callout callout-info" id="itens">
    <h5>Itens para Reserva</h5>
    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Preço</th>
                <th>Quantidade</th>
                <th></th>
            </tr>
        </thead>
        @if (session()->has('reserve.items'))
            <tbody>
                @foreach (session()->get('reserve.items') as $itemId => $item)
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ number_format($item['price'], 2, ',', '.') }} € / dia</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>
                            <form action="{{ route('item.remove', ['id' => $itemId]) }}" method="post">
                                @csrf
                                @method('POST')
                                <button type="submit" class="btn btn-outline-dark mt-auto float-end">Retirar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        @endif
    </table>
</div>
            </div>
        </div>

        <div class="card-footer">
            <div class="row">
                <div class="col-md-auto">
                    <form id="cancelForm" action="{{ route('reserve.cancel') }}" method="post">
                        @csrf
                        @method('POST')
                        <button type="button" class="btn btn-outline-dark mt-auto" onclick="cancelReservation()">Cancelar Reserva</button>
                    </form>
                </div>
                <div class="col-md-auto" id="confirm">
                    <form id="confirmForm" action="{{ route('reserve.confirm') }}" method="post">
                        @csrf
                        @method('POST')
                        <button type="button" class="btn btn-outline-dark mt-auto" onclick="confirmReservation()">Concluir Reserva</button>
                    </form>
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
        title: 'Kits - Etapa 2/5',
        text: 'Aqui irá listar todos os kits selecionados para a sua reserva. (Para adicionar: ir à página dos kits e clicar no botão "Ajuda" para mais informações)',
        attachTo: {
            element: '#kits',
            on: 'top'
        },
        buttons: [
            {
                text: 'Próximo',
                action: tour.next
            }
        ]
    });

    tour.addStep({
        title: 'Itens - Etapa 2/5',
        text: 'O mesmo acontece com os itens. (Para adicionar: ir à página dos itens e clicar no botão "Ajuda" para mais informações)',
        attachTo: {
            element: '#itens',
            on: 'top'
        },
        buttons: [
            {
                text: 'Anterior',
                action: tour.back
            },
            {
                text: 'Próximo',
                action: tour.next
            }
        ]
    });

    tour.addStep({
        title: 'Confirmar Reserva - Etapa 5/5',
        text: 'Após ter selecionado tudo o que deseja, clique no botão para finalizar e passará para a fase de aprovação.',
        attachTo: {
            element: '#confirm',
            on: 'top'
        },
        buttons: [
            {
                text: 'Anterior',
                action: tour.back
            },
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
    function cancelReservation() {
        Swal.fire({
            title: 'Cancelar Reserva',
            text: 'Tem a certeza que deseja cancelar a reserva?',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, Cancelar',
            cancelButtonText: 'Não'
        }).then((result) => {
            if (result.value == true) {
                document.getElementById("cancelForm").submit(); 
            }
        });
    }

    function confirmReservation() {
        Swal.fire({
            title: 'Concluir Reserva',
            text: 'Tem a certeza que deseja concluir a reserva?',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, Concluir',
            cancelButtonText: 'Não'
        }).then((result) => {
            if (result.value == true) {
                document.getElementById("confirmForm").submit(); 
            }
        });
    }

    $(document).ready(function() {
        $('[data-toggle="popover"]').popover();
    });
    </script>

@endsection
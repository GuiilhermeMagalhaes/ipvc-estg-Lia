@extends('index')

@section('content')
<link href="/css/custom.css" rel="stylesheet">
<div class="page-title">
    <nav class="breadcrumbs">
        <div class="container d-flex justify-content-between align-items-center">
            <ol class="d-flex mb-0">
                <li><a href="/"><i class="bi bi-house"></i></a></li>
                <li><a class="current" href="#">Minhas Reservas</a></li>
            </ol>
        </div>
    </nav>
</div>
<br>
<div class="container">
    <div class="card card-solid">
        <div class="card-body">
            <div class="row">
                <table id="reserves" class="table table-hover">
                    <thead>
                        <tr>
                            <th class="no-sort">
                                Descrição
                            </th>
                            <th>
                                Início
                            </th>
                            <th>
                                Fim
                            </th>
                            <th>
                                Estado da Reserva
                            </th>
                            <th class="no-sort">
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reserves as $reserve)
                        @if($reserve->user_id == Auth::user()->id)
                        <tr>
                            <td style="vertical-align: middle">
                                {{ $reserve->description }}
                            </td>
                            <td style="vertical-align: middle">
                                {{\Carbon\Carbon::parse($reserve->start_date)->format('d/m/Y')}}
                            </td>
                            <td style="vertical-align: middle">
                                {{\Carbon\Carbon::parse($reserve->end_date)->format('d/m/Y')}}
                            </td>
                            <td style="vertical-align: middle">
                                {{ $reserve->reserveState->description }}
                            </td>
                            <td>
                                <a href="#" class="open-modal" data-reserve-id="{{ $reserve->id }}">
                                    <i class="bi bi-plus-circle text-dark" style="font-size: 1.5rem;"></i>
                                </a>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal-reserve" tabindex="-1" role="dialog" aria-labelledby="modal-reserve-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-reserve-label">Detalhes da Reserva</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="modal-content">
                    <!-- Conteúdo da reserva será carregado aqui dinamicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" style="width: 140px;" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<!-- Fim Modal -->

<!-- Elementos HTML ocultos com dados JSON -->
<div id="data-container" style="display: none;" data-reserves='@json($reserves)' data-reserve-kits='@json($reserve_kits)' data-kits='@json($kits)' data-reserve-itens='@json($reserve_itens)' data-itens='@json($itens)'></div>

<script>
    $(document).ready(function() {
        $('[data-toggle="popover"]').popover();

        // Abrir modal ao clicar no botão
        $('.open-modal').click(function(e) {
            e.preventDefault();
            var reserveId = $(this).data('reserve-id');
            carregarDetalhesReserva(reserveId);
            $('#modal-reserve').modal('show');
        });

        // Função para carregar detalhes da reserva no modal
        function carregarDetalhesReserva(reserveId) {
            var reserves = JSON.parse($('#data-container').attr('data-reserves'));
            var reserveKits = JSON.parse($('#data-container').attr('data-reserve-kits'));
            var kits = JSON.parse($('#data-container').attr('data-kits'));
            var reserveItens = JSON.parse($('#data-container').attr('data-reserve-itens'));
            var itens = JSON.parse($('#data-container').attr('data-itens'));

            var selectedReserve = reserves.find(r => r.id == reserveId);
            var selectedReserveKits = reserveKits.filter(rk => rk.reserve_id == reserveId);
            var selectedKits = kits.filter(k => selectedReserveKits.some(rk => rk.kit_id == k.id));
            var selectedReserveItens = reserveItens.filter(ri => ri.reserve_id == reserveId);
            var selectedItens = itens.filter(i => selectedReserveItens.some(ri => ri.item_id == i.id));

            var modalContent = `
                <div class="row">
                    <!-- Cards de Kits e Itens -->
                    <div class="card-deck">
                        <!-- Kits da Reserva -->
                        <div class="card card-dark card-outline" style="min-width:383.2px;">
                            <div class="card-header">
                                Kits
                            </div>
                            <div class="card-body">
                                ${selectedKits.map(kit => `
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h6 class="card-text"><strong>Nome:</strong> ${kit.name}</h6>
                                            <h6 class="card-text"><strong>Descrição:</strong> ${kit.description}</h6>
                                            <h6 class="card-text"><strong>Preço / dia:</strong> ${Number(kit.price).toFixed(2).replace('.', ',')} €</h6>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        <!-- Itens da Reserva -->
                        <div class="card card-dark card-outline" style="min-width:383.2px;">
                            <div class="card-header">
                                Itens
                            </div>
                            <div class="card-body">
                                ${selectedItens.map(item => `
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h6 class="card-text"><strong>Nome:</strong> ${item.nome}</h6>
                                            <h6 class="card-text"><strong>Modelo:</strong> ${item.model}</h6>
                                            <h6 class="card-text"><strong>Preço / dia:</strong> ${Number(item.preco).toFixed(2).replace('.', ',')} €</h6>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('#modal-content').html(modalContent);
        }
    });
</script>
@endsection
@extends('adminlte::page')

@section('title', 'Reservas dos Centros de Custo')

@section('content')
<br>

    @if(isset($topEquipamentos) && $topEquipamentos->isNotEmpty())
    <div class="container-fluid mb-4">
        <h4 class="mb-3 text-secondary">Top Equipamentos Mais Requisitados</h4>
        <div class="row align-items-stretch">
            @foreach($topEquipamentos as $index => $equipamento)
                <div class="col-12 col-sm-6 col-md-3 mb-3">
                    @php
                        $bgClass = 'bg-info';
                        if($index == 0) $bgClass = 'bg-gradient-warning'; // 1º Lugar (Dourado)
                        elseif($index == 1) $bgClass = 'bg-gradient-secondary'; // 2º Lugar (Prateado)
                        elseif($index == 2) $bgClass = 'bg-gradient-orange'; // 3º Lugar (Bronzeado)
                    @endphp

                    <div class="info-box {{ $bgClass }} elevation-2 h-100 m-0">
                        <span class="info-box-icon"><i class="fas fa-medal"></i></span>

                        <div class="info-box-content d-flex flex-column justify-content-center">
                           <span class="info-box-text" style="white-space: normal;"><strong>#{{ $index + 1 }}</strong> - {{ $equipamento['nome'] }}</span>
                            <span class="info-box-number mt-auto">
                                {{ $equipamento['total'] }} 
                                {{ $equipamento['total'] == 1 ? 'vez' : 'vezes' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
    <div class="container-fluid">
        <div class="card card-dark card-outline">
            <div class="card-header">
                <h3 class="card-title">Histórico de Reservas</h3>
            </div>
            <div class="card-body p-0">
                <table id="reserves" class="table table-hover table-striped m-0">
                    <thead>
                        <tr>
                            <th>Reservante</th>
                            <th class="no-sort">Descrição</th>
                            <th>Início</th>
                            <th>Fim</th>
                            <th>Estado da Reserva</th>
                            <th>Pagamento</th> <th class="no-sort"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reserves as $reserve)    
                            @if( $id == $reserve->cost_center_id )
                                <tr>
                                    <td>
                                        {{ $reserve->user->name }}
                                    </td>
                                    <td>
                                        {{ $reserve->description }}
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($reserve->start_date)->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($reserve->end_date)->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        {{ $reserve->reserveState->description }}
                                    </td>
                                    
                                    <td>
                                        @if($reserve->cost == 0 && $reserve->reserve_state_id == 1)
                                            <span class="badge badge-secondary">Por calcular</span>
                                        @elseif($reserve->is_paid)
                                            <span class="badge badge-success">Paga</span>
                                        @else
                                            <span class="badge badge-danger">Por Pagar</span>
                                        @endif
                                    </td>

                                    <td class="text-right">
                                        <a href="{{ route('reserves.show', $reserve->id) }}" class="btn btn-sm btn-primary">
                                             Ver Detalhes
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
@endsection

@section('js')
    <script>
        jQuery(function($){
            var table = 
            $('#reserves').DataTable({
                "columnDefs": [{ targets: 'no-sort', orderable: false }],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese.json" // Para traduzir a tabela para PT
                }
            });
        })
    </script>
@endsection
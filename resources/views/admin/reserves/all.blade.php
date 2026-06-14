@extends('adminlte::page')

@section('title', 'Todas as Reservas')

@section('content')
<br>
    <div class="container-fluid">
        <div class="card card-dark card-outline">
            <div class="card-header">
                <h3 class="card-title">Lista de Todas as Reservas</h3>
            </div>
            <div class="card-body p-0">
                <table id="reserves" class="table table-hover table-striped m-0">
                    <thead>
                        <tr>
                            <th>Reservante</th>
                            <th class="no-sort">Descrição</th>
                            <th>Realizada em</th> {{-- NOVA COLUNA --}}
                            <th>Início</th>
                            <th>Fim</th>
                            <th>Cíclica</th>
                            <th>Estado da reserva</th>
                            <th>Pagamento</th> 
                            <th class="no-sort"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reserves as $reserve)
                            <tr>
                                <td class="align-middle">
                                    {{ $reserve->user->name }}
                                </td>
                                <td class="align-middle">
                                    {{ $reserve->description }}
                                </td>
                                {{-- NOVO DADO: Data de criação da reserva --}}
                                <td class="align-middle">
                                    {{ \Carbon\Carbon::parse($reserve->created_at)->format('Y/m/d H:i') }}
                                </td>
                                <td class="align-middle">
                                    {{\Carbon\Carbon::parse($reserve->start_date)->format('Y/m/d')}}
                                </td>
                                <td class="align-middle">
                                    {{\Carbon\Carbon::parse($reserve->end_date)->format('Y/m/d')}}
                                </td>
                                <td class="align-middle">
                                    {{ $reserve->ciclica->dia_semana }}
                                </td>
                                <td class="align-middle">
                                    {{ $reserve->reserveState->description }}
                                </td>
                                
                                <td class="align-middle">
                                    @if($reserve->cost == 0 && $reserve->reserve_state_id == 1)
                                        <span class="badge badge-secondary">Por calcular</span>
                                    @elseif($reserve->is_paid)
                                        <span class="badge badge-success">Paga</span>
                                    @else
                                        <span class="badge badge-danger">Por Pagar</span>
                                    @endif
                                </td>

                                <td class="text-right align-middle">
                                    <a href="{{ route('reserves.show', $reserve->id) }}" class="btn btn-sm btn-primary" style="width: 140px;">
                                         Ver reserva
                                    </a>
                                </td>
                            </tr>
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
                "order": [[ 2, "desc" ]], // ORDENAÇÃO NOVA: Coluna índice 2 (Realizada em) de forma decrescente
                "columnDefs": [{ targets: 'no-sort', orderable: false }],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese.json" 
                }
            });
        })
    </script>
@endsection
@extends('adminlte::page')

@section('title', 'Reservas dos Centros de Custo')

@section('content')
<br>

    @if(isset($topEquipamentos) && $topEquipamentos->isNotEmpty())
    <div class="row mb-4 px-2">
        <div class="col-md-12">
            <div class="card" style="border-top: 3px solid #17a2b8;">
                <div class="card-header bg-light">
                    <h3 class="card-title m-0">
                        <strong>Equipamento Mais Requisitado por este Centro</strong>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-around text-center flex-wrap">
                        @foreach($topEquipamentos as $index => $equipamento)
                            <div class="p-3 m-2" style="background: #f8f9fa; border-radius: 8px; min-width: 150px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <h4 style="color: #6c757d; font-weight: bold;">#{{ $index + 1 }}</h4>
                                <p class="mb-2" style="font-size: 1.1rem; color: #343a40;"><strong>{{ $equipamento['nome'] }}</strong></p>
                                <span class="badge" style="background-color: #17a2b8; font-size: 0.9rem; padding: 8px;">
                                    @if ($equipamento['total'] == 1)
                                        {{ $equipamento['total'] }} vez
                                    @else
                                        {{ $equipamento['total'] }} vezes    
                                    @endif
                                    
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <div class="container-fluid">
        <table id="reserves" class="table table-hover">
            <thead>
                <tr>
                    <th>
                        Reservante
                    </th>
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
                        Estado da reserva
                    </th>
                    <th class="no-sort">
                    </th>
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
                                {{ $reserve->start_date }}
                            </td>
                            <td>
                                {{ $reserve->end_date }}
                            </td>
                            <td>
                                {{ $reserve->reserveState->description }}
                            </td>
                            <td>
                                <a href="{{ route('reserves.show', $reserve->id) }}" class="btn btn-primary" style="width: 140px;">Ver reserva</a>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('js')
    <script>
        jQuery(function($){
            var table = 
            $('#reserves').DataTable({
                "columnDefs": [{ targets: 'no-sort', orderable: false }]
            });
        })
    </script>
@endsection
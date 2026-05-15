@extends('adminlte::page')

@section('title', 'Reservas dos Centros de Custo')

@section('content')
<br>
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
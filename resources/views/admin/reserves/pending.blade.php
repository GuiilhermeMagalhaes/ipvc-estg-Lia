@extends('adminlte::page')

@section('title', 'Reservas Pendentes de Aprovação')

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
                        Realizada em
                    </th> 
                    <th>
                        Início
                    </th>
                    <th>
                        Fim
                    </th>
                    <th>
                        Cíclica
                    </th>
                    <th>
                        Estado da reserva
                    </th>
                    <th class="no-sort"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reserves as $reserve)
                    <tr>
                        <td>
                            {{ $reserve->user->name }}
                        </td>
                        <td>
                            {{ $reserve->description }}
                        </td>
                        <td class="align-middle">
                            {{ \Carbon\Carbon::parse($reserve->created_at)->format('Y/m/d H:i') }}
                        </td>
                        <td>
                            {{\Carbon\Carbon::parse($reserve->start_date)->format('d/m/Y')}}
                        </td>
                        <td>
                            {{\Carbon\Carbon::parse($reserve->end_date)->format('d/m/Y')}}
                        </td>
                        <td>
                            {{ $reserve->ciclica->dia_semana }}
                        </td>
                        <td>
                            {{ $reserve->reserveState->description }}
                        </td>
                        <td>
                            <a href="{{ route('reserves.show', $reserve->id) }}" class="btn btn-primary" style="width: 140px;">Ver reserva</a>
                        </td>
                    </tr>
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
                "order": [[ 2, "desc" ]], // ADICIONAR ESTA LINHA PARA ORDENAR
                "columnDefs": [{ targets: 'no-sort', orderable: false }],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese.json" 
                }
            });
        })
    </script>
@endsection
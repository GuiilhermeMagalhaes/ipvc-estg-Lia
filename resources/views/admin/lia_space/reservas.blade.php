@extends('adminlte::page')

@section('title', 'Reservas do Espaço LIA')

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
                        Custo
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reservas as $reserve)
                    <tr>
                        @foreach ($users as $user)
                            @if ($user->id == $reserve->user_id)
                            <td>
                                {{ $user->name }}
                            </td>
                            @endif
                        @endforeach
                        <td>
                            {{ $reserve->description }}
                        </td>
                        <td>
                            {{\Carbon\Carbon::parse($reserve->start_date)->format('Y/m/d')}}
                        </td>
                        <td>
                            {{\Carbon\Carbon::parse($reserve->end_date)->format('Y/m/d')}}
                        </td>
                        <td>
                            {{ number_format($reserve->cost, 2, ',', '.') }} €
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
                "columnDefs": [{ targets: 'no-sort', orderable: false }]
            });
        })
    </script>
@endsection
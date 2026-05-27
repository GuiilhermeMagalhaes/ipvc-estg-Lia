@extends('adminlte::page')

@section('title', 'Centros de Custo')

@section('content')
<br>

    <div class="container-fluid">
        <table id="reserves" class="table table-hover">
            <thead>
                <tr>
                    <th>
                        Nome
                    </th>
                    <th>
                        Custo Total
                    </th>
                    <th>
                        Em Débito
                    </th>
                    <th>
                        Responsável
                    </th>
                    <th class="no-sort"></th>
                    <th class="no-sort"></th>
                    <th class="no-sort"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($centros as $centro)
                    <tr>
                        <td>
                            {{ $centro->name }}
                        </td>
                        <td>
                            {{ number_format($centro->total_cost, 2, ',', '.') }} €
                        </td>
                        <td>
                            {{ number_format($centro->total_debt, 2, ',', '.') }} €
                        </td>
                        <td>
                        @foreach ($cost_center_user as $center_user)
                            @foreach ($users as $user)
                                @if ($center_user->user_id == $user->id && $centro->id == $center_user->cost_center_id)
                                    {{ $user->name }}
                                @endif
                            @endforeach
                        @endforeach
                        </td>
                        <td>
                            <a href="{{ route('centro.reservas', $centro->id) }}" class="btn btn-primary" style="width: 140px;">Ver reservas</a>
                        </td>
                        @if($centro->id == 1)
                            <td>
                                <a></a>
                            </td>
                        @else
                            <td>
                                <a href="{{ route('centro.destroy', $centro->id) }}" class="btn btn-danger" style="width: 140px;">Apagar Centro</a>
                            </td>
                        @endif
                        <td>
                            <a href="{{ route('centro.pagar', $centro->id) }}" class="btn btn-success" style="width: 140px;">Pagar</a>
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

@extends('adminlte::page')

@section('title', 'Perfil de Utilizador')

@section('content')
<br>
    <div class="row">
        <div class="col-md-4">
            <div class="card card-dark card-outline">
                <div class="card-body box-profile">
                    <h3 class="profile-username text-center">
                        {{ $user->name ? $user->name : 'Utilizador' }}
                    </h3>
                    <p class="text-muted text-center">{{ $user->userType->description }}</p>
                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Email</b> <a class="float-right">{{ $user->email }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Telefone</b> <a class="float-right">{{ $user->phone }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Número de Aluno</b> <a class="float-right">{{ $user->n_aluno }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Curso</b> <a class="float-right">{{ $user->curso }}</a>
                        </li>
                        <li class="list-group-item">
                            @if ($user->tipo_user == 1)
                                <b>Tipo de Utilizador</b><a class="float-right">Aluno</a>
                            @elseif ($user->tipo_user == 2)
                                <b>Tipo de Utilizador</b><a class="float-right">Docente</a>
                            @elseif($user->tipo_user == 3)
                                <b>Tipo de Utilizador</b><a class="float-right">Funcionário</a>
                            @elseif($user->tipo_user == 4)
                                <b>Tipo de Utilizador</b><a class="float-right">Outro</a>
                            @else
                                <b>Tipo de Utilizador</b>
                            @endif
                        </li>
                        <li class="list-group-item">
                            <b>Estado de Utilzador</b> <a class="float-right">{{ $user->userStatus->description }}</a>
                        </li>
                        <a class="btn btn-primary mx-auto mt-1" href="{{ route('user.edit', $user->id) }}">Editar Permissões</a>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <h2 class="mt-2">Reservas</h2>
    <div>
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
                        Cíclica
                    </th>
                    <th>
                        Custo
                    </th>
                    <th>
                        Estado da reserva
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reserves as $reserve)
                    @if ($reserve->user_id == $user->id)
                        <tr>
                            <td>
                                {{ $reserve->description }}
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
                                {{ number_format($reserve->cost, 2, ',', '.') }} €
                            </td>
                            <td>
                                {{ $reserve->reserveState->description }}
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
        jQuery(function($) {
            var table =
                $('#reserves').DataTable({
                    "columnDefs": [{
                        targets: 'no-sort',
                        orderable: false
                    }]
                });
        })
    </script>
@endsection

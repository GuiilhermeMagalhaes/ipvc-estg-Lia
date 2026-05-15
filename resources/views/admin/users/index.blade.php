@extends('adminlte::page')

@section('title', 'Utilizadores')

@section('content')
<br>
    <div class="container-fluid">
        <table id="users" class="table table-hover">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Número</th>
                    <th>Tipo de Utilizador</th>
                    <th class="no-sort"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->n_aluno}}</td>
                        @if(  $user->tipo_user  == 1)
                            <td>Aluno</td>
                        @elseif ($user->tipo_user  == 2)
                            <td>Docente</td>
                        @elseif($user->tipo_user  == 3)
                            <td>Funcionário</td>
                        @elseif($user->tipo_user  == 4)
                            <td>Outro</td>
                        @else
                            <td>
                        @endif
                        <td><a href="{{ route('user.show', $user->id) }}" class="btn btn-primary" style="width: 140px;">Ver Utilizador</a></td>
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
            $('#users').DataTable({
                "columnDefs": [{ targets: 'no-sort', orderable: false }]
            });
        })
    </script>
@endsection
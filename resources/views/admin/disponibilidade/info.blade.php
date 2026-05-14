@php
@endphp
@extends('adminlte::page')

@section('title', 'Disponibilidade do Técnico')

@section('content')
<br>
<div class="container-fluid">
    <table id="disponibilidade" class="table table-hover">
        <thead>
            <tr>
                <th style="vertical-align: middle;"> <!-- Ajuste para alinhar verticalmente -->
                    Data
                </th>
                <th style="vertical-align: middle;"> <!-- Ajuste para alinhar verticalmente -->
                    Descrição
                </th>
                <th class="no-sort" style="vertical-align: middle; padding: 0;"> <!-- Ajuste para alinhar verticalmente e remover padding -->
                    <form action="{{ route('disponibilidade.destroyAll') }}" method="POST" onsubmit="return confirm('Tem certeza que deseja eliminar todos os horários?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="width: 140px; margin: 0;">Apagar Todos</button>
                    </form>
                </th>
                <th class="no-sort"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($horarios as $horario)
            <tr>
                @if($horario->entredatas != null)
                <td style="vertical-align: middle;">
                    {{ $horario->entredatas }}
                </td>
                @else
                <td style="vertical-align: middle;">
                    {{\Carbon\Carbon::parse($horario->data)->format('d/m/Y')}}
                </td>
                @endif
                <td style="vertical-align: middle;">
                    {{ $horario->descricao }}
                </td>
                <td style="vertical-align: middle; padding: 0;">
                    <a href="{{ route('disponibilidade.destroy', $horario->id) }}" class="btn btn-danger" style="width: 140px;">Apagar</a>
                </td>
                <td style="vertical-align: middle; padding: 0;">
                    <a href="{{ route('disponibilidade.edit', $horario->id) }}" class="btn btn-primary" style="width: 140px;">Editar</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('js')
<script>
    jQuery(function($) {
        var table =
            $('#disponibilidade').DataTable({
                "columnDefs": [{
                    targets: 'no-sort',
                    orderable: false
                }]
            });
    })
</script>
@endsection

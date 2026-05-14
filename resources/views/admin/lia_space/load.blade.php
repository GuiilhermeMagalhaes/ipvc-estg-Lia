@php
@endphp
@extends('adminlte::page')

@section('title', 'Editar Reserva')

@section('content')
<div class="d-flex flex-column">
    <form id="confirmForm" action="{{ route('lia_space.load', $space->space_code) }}" method="POST">
        @csrf
        @method('PUT')
        <br>
        <div class="card-body">
                <div class="form-group">
                    <label for="occupant_id">Email do Posto</label>
                    <select class="form-control" name="occupant_id">
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" 
                                @if(old('occupant_id', $space->occupant_id) == $user->id) selected @endif>
                            {{ $user->email }}
                        </option>
                    @endforeach
                    </select>

                </div>
                <div class="form-group">
                    <label for="startDate">Data de Início</label>
                    <input name="start_date" type="datetime-local" class="form-control" value="{{ old('start_date', $space->start_date) }}">
                    @error('start_date')
                        <span style="color:red" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="endDate">Data de Fim</label>
                    <input name="end_date" type="datetime-local" class="form-control"value="{{ old('end_date', $space->end_date) }}">
                    @error('end_date')
                        <span style="color:red" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="description">Motivo de Reserva</label>
                    <input name="description" type="text" class="form-control" value="{{ old('description', $space->description) }}">
                    <span style="color:red">{{$errors->first('description')}}</span>
                </div>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-outline-dark mt-auto" onclick="confirmReservation()" style="width: 140px;">Atualizar Reserva</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript">
    function confirmReservation() {
        console.log('confirmReservation function called'); // Verificação de chamada da função
        Swal.fire({
            title: 'Concluir Reserva',
            text: 'Tem a certeza que deseja concluir a reserva?',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, Concluir',
            cancelButtonText: 'Não'
        }).then((result) => {
            if (result.value) {
                document.getElementById("confirmForm").submit(); // Submete o formulário de confirmação
            }
        });
    }

    $(document).ready(function() {
        $('[data-toggle="popover"]').popover();
    });
</script>

@endsection

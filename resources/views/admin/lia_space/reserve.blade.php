@extends('adminlte::page')

@section('title', 'Atribuição Posto')

@section('content')
<div class="d-flex flex-column">
    <form id="confirmForm" action="{{ route('lia.space.reserve', ['id' => $space->id]) }}" method="POST">
        @csrf
        @method('POST')
            <div class="card-body">
                <div class="form-group">
                    <label for="occupant_id">Utilizador do Posto</label>
                    <select class="form-control" name="occupant_id">
                        @foreach ($users as $user)
                        <option value="{{ old('id', $user->id) }}">
                            {{ $user->email }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <!-- <div class="form-group">
                    <label for="space">Posto de Trabalho para Reserva</label>
                    <input type="text" class="form-control" value="Posto {{ $space->space_code ?? 'Código não encontrado' }}" readonly>
                </div> -->
                <div class="form-group">
                    <label for="startDate">Data de Início</label>
                    <input name="start_date" type="datetime-local" class="form-control" value="{{ old('start_data', $start_date) }}">
                    @error('start_date')
                        <span style="color:red" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="endDate">Data de Fim</label>
                    <input name="end_date" type="datetime-local" class="form-control"value="{{ old('end_data', $end_date) }}">
                    @error('end_date')
                        <span style="color:red" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="description">Motivo de Reserva</label>
                    <input name="description" type="text" class="form-control" value="{{ old('description', $description) }}">
                    @error('description')
                        <span style="color:red" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="cost_center_id">Centro de Custos</label>
                    <select class="form-control" name="cost_center_id">
                        @foreach ($costCenters as $costCenter)
                            <option value="{{ old('costCenter->id', $costCenter->id) }}">{{ $costCenter->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-outline-dark mt-auto" onclick="confirmReservation()" style="width: 140px;">Concluir Reserva</button>
            </div>
        </form>
    </div>

<script>
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

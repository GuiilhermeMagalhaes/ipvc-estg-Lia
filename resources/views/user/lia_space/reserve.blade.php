@extends('index')

@section('content')
<link href="/css/custom.css" rel="stylesheet">
<div class="page-title">
    <nav class="breadcrumbs">
        <div class="container d-flex justify-content-between align-items-center">
            <ol class="d-flex mb-0">
                <li><a href="/"><i class="bi bi-house"></i></a></li>
                <li><a href="/lia-space">Espaço LIA</a></li>
                <li><a class="current" href="#">Nova Reserva LIA</a></li>
            </ol>
        </div>
    </nav>
</div>
<br>
    <div class="card card-dark card-outline">
        <form id="confirmForm" action="{{ route('space.reserve', $space->id) }}" method="post">
            @csrf
            @method('POST')
            <div class="card-body">
                <div class="form-group">
                    <label for="space">Posto de Trabalho para Reserva</label>
                    <input type="text" class="form-control" value="Posto {{ $space->space_code }}" readonly>
                </div>
                <div class="form-group">
                    <label for="startDate">Data de Início</label>
                    <input name="start_date" type="date" class="form-control" value="{{ $start_date }}" readonly>
                </div>
                <div class="form-group">
                    <label for="endDate">Data de Fim</label>
                    <input name="end_date" type="date" class="form-control"value="{{ $end_date }}" readonly>
                </div>
                <div class="form-group">
                    <label for="description">Motivo de Reserva</label>
                    <input name="description" type="text" class="form-control" value="{{ old('description') }}">
                    @error('description')
                        <span style="color:red" class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="cost_center_id">Centro de Custos</label>
                    <select class="form-control" name="cost_center_id">
                        @foreach ($costCenters as $costCenter)
                        {{-- @if ($costCenter->id != 1) --}}
                        <option value="{{ $costCenter->id }}">{{ $costCenter->name }}</option>
                        {{-- @endif --}}
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    @if (collect($users)->isNotEmpty())
                        <label for="ocuppant_id">Utilizador do Posto</label>
                        <select class="form-control" name="occupant_id">
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->email }}</option>
                            @endforeach
                        </select>
                    @else
                        <label for="occupant_email">Email do Utilizador do Posto</label>
                        <input name="occupant_email" type="text" class="form-control"
                            value="{{ old('occupant_email') }}">
                        @error('occupant_email')
                            <span style="color:red" class="error">{{ $message }}</span>
                        @enderror
                    @endif

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
            if (result.value == true) {
                document.getElementById("confirmForm").submit(); // Submete o formulário de confirmação
            }
        });
    }

    $(document).ready(function() {
        $('[data-toggle="popover"]').popover();
    });
    </script>
@endsection

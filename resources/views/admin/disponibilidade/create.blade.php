@php
@endphp
@extends('adminlte::page')

@section('title', 'Criar Horário')

@section('content')
<br>
<div class="container-fluid">
    <div class="d-flex flex-column">
        <form action="{{ route('disponibilidade.store') }}" method="POST">
            @csrf
            @method('POST')
            <div class="form-group">
                <label for="tipo">Tipo de Horário</label>
                <select id="tipo" name="tipo" class="form-control">
                    <option value="unico">Data Única</option>
                    <option value="recorrente">Recorrente</option>
                    <option value="entredatas">Entre Datas</option>
                </select>
            </div>

            <div id="unico" class="tipo-opcao">
                <div class="form-group">
                    <label for="data">Data</label>
                    <input type="date" name="data" class="form-control">
                    <span style="color:red">{{ $errors->first('data') }}</span>
                </div>
            </div>

            <div id="recorrente" class="tipo-opcao" style="display: none;">
                <div class="form-group">
                    <label for="dia_semana">Dia da Semana</label>
                    <select name="dia_semana" class="form-control">
                        <option value="1">Segunda-feira</option>
                        <option value="2">Terça-feira</option>
                        <option value="3">Quarta-feira</option>
                        <option value="4">Quinta-feira</option>
                        <option value="5">Sexta-feira</option>
                        <option value="6">Sábado</option>
                        <option value="0">Domingo</option>
                    </select>
                    <span style="color:red">{{ $errors->first('dia_semana') }}</span>
                </div>
                <div class="form-group">
                    <label for="semanas">Número de Semanas Recorrente</label>
                    <input type="number" name="semanas" class="form-control">
                    <span style="color:red">{{ $errors->first('semanas') }}</span>
                </div>
            </div>

            <div id="entredatas" class="tipo-opcao" style="display: none;">
                <div class="form-group">
                    <label for="data_inicio">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control">
                    <span style="color:red">{{ $errors->first('data_inicio') }}</span>
                </div>
                <div class="form-group">
                    <label for="data_fim">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control">
                    <span style="color:red">{{ $errors->first('data_fim') }}</span>
                </div>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição (Horas)</label>
                <input type="text" name="descricao" class="form-control">
                <span style="color:red">{{ $errors->first('descricao') }}</span>
            </div>

            <button type="submit" class="btn btn-success" style="width: 140px;">Criar Horário</button>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoSelect = document.getElementById('tipo');
        const unicoDiv = document.getElementById('unico');
        const recorrenteDiv = document.getElementById('recorrente');
        const entredatasDiv = document.getElementById('entredatas');

        tipoSelect.addEventListener('change', function () {
            if (this.value === 'unico') {
                unicoDiv.style.display = 'block';
                recorrenteDiv.style.display = 'none';
                entredatasDiv.style.display = 'none';
            } else if (this.value === 'recorrente'){
                unicoDiv.style.display = 'none';
                recorrenteDiv.style.display = 'block';
                entredatasDiv.style.display = 'none';
            } else if(this.value === 'entredatas'){
                unicoDiv.style.display = 'none';
                recorrenteDiv.style.display = 'none';
                entredatasDiv.style.display = 'block';
            }
        });
    });
</script>
@endsection
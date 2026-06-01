@extends('adminlte::page')

@section('title', 'Definir Unidades')

@section('content')
<br>
    <div class="d-flex flex-column">
            <h3 class="mb-4"> Configurar Códigos LIA para: {{ $item->nome }}</h3>
            
            <form action="{{ route('itens.updateUnitiesEtapa', $item->id) }}" method="POST">
                @csrf

                <h4 class="mt-3">Unidades Já Registadas</h4>
                <p class="text-muted">Podes atualizar os códigos das unidades atuais.</p>

                @foreach($unidadesAtuais as $unidade)
                    <div class="form-group">
                        <label>Código LIA da Unidade (ID #{{ $unidade->id }})</label>
                        <input type="text" name="lias_atuais[{{ $unidade->id }}]" class="form-control" value="{{ old('lias_atuais.'.$unidade->id, $unidade->lia_code) }}" required>
                        @if($errors->has("lias_atuais.".$unidade->id))
                            <span style="color:red">{{ $errors->first("lias_atuais.".$unidade->id) }}</span>
                        @endif
                    </div>
                @endforeach

                @if($novasUnidadesQtd > 0)
                    <hr>
                    <h4 class="text-success mt-4">Novas Unidades Detetadas (+{{ $novasUnidadesQtd }})</h4>
                    <p class="text-muted">Insere o código LIA para o novo stock adicionado.</p>

                    @for ($i = 0; $i < $novasUnidadesQtd; $i++)
                        <div class="form-group">
                            <label class="text-success">Novo Código LIA #{{ $i + 1 }}</label>
                            <input type="text" name="novos_lias[]" class="form-control" value="{{ old('novos_lias.'.$i) }}" placeholder="Introduza o novo código LIA" required>
                            @if($errors->has("novos_lias.".$i))
                                <span style="color:red">{{ $errors->first("novos_lias.".$i) }}</span>
                            @endif
                        </div>
                    @endfor
                @endif

                <div class="mt-4">
                    <a href="{{ route('itens.edit', $item->id) }}" class="btn btn-secondary mr-2" style="width: 140px;">Voltar</a>
                    <button type="submit" class="btn btn-success" style="width: 180px;">Finalizar e Guardar</button>
                </div>
            </form>
    </div>
@endsection
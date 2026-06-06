@extends('adminlte::page')

@section('title', 'Definir Unidades')

@section('content')
<br>
    <div class="d-flex flex-column">
            <p class="text-dark list-group-item-text mb-4" style="font-size: 1.2rem;">
                Códigos LIA para as {{ session('dados_item_edicao.quantity') }} Unidades de "{{ $item->nome }}"
            </p>
            <form action="{{ route('itens.updateUnitiesEtapa', $item->id) }}" method="POST">
                @csrf

                <p class="text-dark list-group-item-text mt-2" style="font-size: 1.2rem;">Unidades Já Registadas</p> 
                <p class="text-muted">Podes atualizar os códigos das unidades atuais.</p>

                @foreach($unidadesAtuais as $unidade)
                    <div class="form-group">
                        <label>Código LIA da Unidade #{{ $loop->iteration }}</label>
                        <input type="text" name="lias_atuais[{{ $unidade->id }}]" class="form-control" value="{{ old('lias_atuais.'.$unidade->id, $unidade->lia_code) }}" required>
                        @if($errors->has("lias_atuais.".$unidade->id))
                            <span style="color:red">{{ $errors->first("lias_atuais.".$unidade->id) }}</span>
                        @endif
                    </div>

                    <div class="form-group" style="margin-top: -10px; margin-bottom: 25px;">
                        <label>Data de Aquisição </label>
                        <input type="date" name="data_aquisicao_atuais[{{ $unidade->id }}]" class="form-control" max="{{ date('Y-m-d') }}"
       value="{{ old('data_aquisicao_atuais.' . $unidade->id, $unidade->data_aquisicao ? $unidade->data_aquisicao->format('Y-m-d') : '') }}">
                        @if($errors->has("data_aquisicao_atuais.".$unidade->id))
                            <span style="color:red; display:block;">{{ $errors->first("data_aquisicao_atuais.".$unidade->id) }}</span>
                        @endif
                    </div>
                @endforeach

                @if($novasUnidadesQtd > 0)
                    <hr>
                     <p class="text-primary mt-4" style="font-size: 1.2rem;">Novas Unidades Detetadas (+{{ $novasUnidadesQtd }})</p> 
                    
                    <p class="text-muted">Insere o código LIA para o novo stock adicionado.</p>

                    @for ($i = 0; $i < $novasUnidadesQtd; $i++)
                        <div class="form-group">
                            <label class="text-primary">Novo Código LIA #{{ $unidadesAtuais->count() + $i + 1 }}</label>
                            <input type="text" name="novos_lias[]" class="form-control" value="{{ old('novos_lias.'.$i) }}" placeholder="Introduza o novo código LIA" required>
                            @if($errors->has("novos_lias.".$i))
                                <span style="color:red">{{ $errors->first("novos_lias.".$i) }}</span>
                            @endif
                        </div>
                        <div class="form-group" style="margin-top: -10px; margin-bottom: 25px;">
                            <label class="text-dark">Data de Aquisição</label>
                            <input type="date" name="data_aquisicao_novas[]" class="form-control" max="{{ date('Y-m-d') }}" value="{{ old('data_aquisicao_novas.'.$i) }}">
                            @if($errors->has("data_aquisicao_novas.".$i))
                                <span style="color:red; display:block;">{{ $errors->first("data_aquisicao_novas.".$i) }}</span>
                            @endif
                        </div>
                    @endfor
                @endif

                <div class="mt-4">
                    <button type="button" onclick="window.history.back();" class="btn btn-secondary mr-2" style="width: 140px;">
                        Voltar
                    </button>
                    <button type="submit" class="btn btn-primary" style="width: 180px;">Finalizar e Guardar</button>
                </div>
            </form>
    </div>

    @if($errors->any())
    <script>
        // Aguarda a página carregar totalmente
        window.addEventListener('load', function() {
            // Atrasa o alerta em 100 milissegundos para o AdminLTE respirar
            setTimeout(function() {
                alert("Erro: {{ $errors->first() }}");
            }, 100);
        });
    </script>
@endif
@endsection
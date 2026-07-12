@extends('adminlte::page')

@section('title', 'Definir Unidades')

@section('content')
<br>
    <div class="d-flex flex-column">
            <p class="text-dark list-group-item-text mb-4" style="font-size: 1.2rem;">
                Códigos LIA para as {{ session('dados_item_edicao.quantity') }} Unidades de "{{ $item->nome }}"
            </p>
            <form action="{{ route('itens.updateUnitiesEtapa', $item->id) }}" method="POST" novalidate>
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

                    <div class="form-group">
                            <label>Referência IPVC</label>
                            <input type="text" name="ipvc_ref_atuais[{{ $unidade->id }}]" class="form-control" value="{{ old('ipvc_ref_atuais.' . $unidade->id, $unidade->ipvc_ref) }}" placeholder="Ex: IPVC-XXXX">
                            @if($errors->has("ipvc_ref_atuais." . $unidade->id))
                                <span style="color:red; display:block;">{{ $errors->first("ipvc_ref_atuais." . $unidade->id) }}</span>
                            @endif
                        </div>

                        {{-- Número de Série --}}
                        <div class="form-group mb-4">
                            <label>Número de Série (Serial Number)</label>
                            <input type="text" name="serial_number_atuais[{{ $unidade->id }}]" class="form-control" value="{{ old('serial_number_atuais.' . $unidade->id, $unidade->serial_number) }}" placeholder="Ex: SN-XXXX">
                            @if($errors->has("serial_number_atuais." . $unidade->id))
                                <span style="color:red; display:block;">{{ $errors->first("serial_number_atuais." . $unidade->id) }}</span>
                            @endif
                        </div>

                    <div class="form-group mb-5 pb-3">
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
                     <p class="text-primary mt-4" style="font-size: 1.2rem;">Novas Unidades (+{{ $novasUnidadesQtd }})</p> 
                    
                    <p class="text-muted">Insere o código LIA para o novo stock adicionado.</p>

                    @for ($i = 0; $i < $novasUnidadesQtd; $i++)
                        <div class="form-group">
                            <label class="text-dark">Novo Código LIA da Unidade #{{ $unidadesAtuais->count() + $i + 1 }}</label>
                            <input type="text" name="novos_lias[]" class="form-control" value="{{ old('novos_lias.'.$i) }}" placeholder="Introduza o novo código LIA" required>
                            @if($errors->has("novos_lias.".$i))
                                <span style="color:red">{{ $errors->first("novos_lias.".$i) }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                                <label class="text-dark">Nova Referência IPVC</label>
                                <input type="text" name="ipvc_ref_novas[]" class="form-control" value="{{ old('ipvc_ref_novas.' . $i) }}" placeholder="Introduza a referência IPVC">
                                @if($errors->has("ipvc_ref_novas." . $i))
                                    <span style="color:red; display:block;">{{ $errors->first("ipvc_ref_novas." . $i) }}</span>
                                @endif
                            </div>

                            {{-- Novo Número de Série --}}
                            <div class="form-group mb-4">
                                <label class="text-dark">Novo Número de Série</label>
                                <input type="text" name="serial_number_novas[]" class="form-control" value="{{ old('serial_number_novas.' . $i) }}" placeholder="Introduza o número de série">
                                @if($errors->has("serial_number_novas." . $i))
                                    <span style="color:red; display:block;">{{ $errors->first("serial_number_novas." . $i) }}</span>
                                @endif
                            </div>

                       <div class="form-group mb-5 pb-3">
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
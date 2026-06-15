@extends('adminlte::page')

@section('title', 'Item')

@section('content')
<br>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="font-weight-bold text-dark">{{ $item->nome }}</h1>
            <p class="text-muted list-group-item-text" style="font-size: 1.2rem;">Modelo: <strong>{{ $item->model }}</strong></p>
        </div>
    </div>

    <div class="row">
       <div class="col-md-4 col-sm-12 mb-4">
    <div class="text-center text-md-left">
        <img id="img" 
             src="../../{{ $item->image }}" 
             class="img-fluid rounded shadow-sm" 
             style="max-width: 400px; width: 100%; height: 310px; object-fit: contain;">
    </div>
</div>

        <div class="col-md-8 col-sm-12">
            
            <div class="mb-4">
                <h6 class="text-dark font-weight-bold mb-3">Informações da Unidade Atual</h6>
                
                <form id="form-unidade" action="{{ route('unidades.updateUnity', $unidade->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <ul class="list-group shadow-sm">
                        <li class="list-group-item d-flex align-items-center">
                            <span>Código LIA: </span>
                            <input type="text" id="lia_code" name="lia_code" class="form-control form-control-sm" value="{{ $unidade->lia_code }}" style="width: 180px; display: inline-block;">
                        </li>
                        
                        <li class="list-group-item d-flex align-items-center">
                            <span>Estado: </span>
                            <select id="item_unity_state_id" name="item_unity_state_id" class="form-control form-control-sm mr-2" style="width: 180px; display: inline-block;"
                                    data-atual="{{ $unidade->item_unity_state_id }}"
                                    data-has-kit="{{ $unidade->kitUnity ? 'true' : 'false' }}"
                                    data-kit-name="{{ $unidade->kitUnity && $unidade->kitUnity->kit ? $unidade->kitUnity->kit->name : '' }}"
                                    data-kit-lia="{{ $unidade->kitUnity ? $unidade->kitUnity->lia_code : '' }}">
                                <option value="1" {{ $unidade->item_unity_state_id == 1 ? 'selected' : '' }}>Ativo (Visível)</option>
                                <option value="2" {{ $unidade->item_unity_state_id == 2 ? 'selected' : '' }}>Oculto</option>
                                <option value="4" {{ $unidade->item_unity_state_id == 4 ? 'selected' : '' }}>Manutenção</option>
                            </select>
                        </li>
                        
                        <li class="list-group-item d-flex align-items-center">
                            <span style="width: 150px; display: inline-block;">Data de Aquisição: </span>
                            <input type="date" id="data_aquisicao" name="data_aquisicao" class="form-control form-control-sm" value="{{ $unidade->data_aquisicao ? $unidade->data_aquisicao->format('Y-m-d') : '' }}" max="{{ date('Y-m-d') }}" style="width: 180px; display: inline-block;">
                        </li>

                         <li class="list-group-item">
                            <span>Tempo de Vida: </span>
                            {{ $unidade->tempo_de_vida }}
                        </li>
                    </ul>
                </form>
            </div>

                <div class="mb-4 pt-5">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-dark font-weight-bold m-0">Informações do Item</h6>
                    <a href="{{ route('itens.edit', $item->id) }}" class="btn btn-primary" style="width: 140px;">Editar Item</a>
                </div>
                
                <ul class="list-group shadow-sm">
                    {{-- Bloco de Identificação --}}
                    <li class="list-group-item">Categoria: {{ $item->itemCategorie->description ?? 'Sem categoria' }}</li>
                    <li class="list-group-item">Número de Série: {{ $item->serial_number ?? 'Sem número de série'}}</li>
                    <li class="list-group-item">Referência IPVC: {{ $item->ipvc_ref ?? 'Não definida' }}</li>

                    {{-- Bloco Financeiro e Ciclo de Vida --}}
                    <li class="list-group-item">Preço do Item: {{ $item->preco ? number_format($item->preco, 2, ',', '.') . ' €' : 'Não registado' }}</li>
                    <li class="list-group-item">Preço / dia: {{ $item->price_day ? number_format($item->price_day, 2, ',', '.') . ' €' : '0,00 €' }}</li>
                    
                    {{-- Detalhes Adicionais --}}
                    <li class="list-group-item">Acessórios: {{ $item->acessorio ?? 'Nenhum acessório registado' }}</li>
                    <li class="list-group-item">Observações: {{ $item->observation ?? 'Nenhuma observação' }}</li>
                    
                    {{-- Bloco de Inventário/Stock --}}
                   
                    <li class="list-group-item text-muted italic small">
                         Mais unidades do mesmo item registadas: 
                    </li>

                     <li class="list-group-item bg-light">
                        Quantidade Total: {{ $item->quantity }}
                    </li>

                    @if(isset($unidadesDoItem) && $unidadesDoItem->count() > 0)
    <li class="list-group-item bg-light">
        <table class="table table-sm m-0">
            <thead>
                <tr>
                    <th>Código LIA</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($unidadesDoItem as $u)
                <tr>
                    <td><a href="{{ route('itens.show', $u->id) }}" style="color: black">{{ $u->lia_code }}</a></td>
                    <td>
                        @if($u->item_unity_state_id == 1)
                            <span class="badge badge-success">Ativo</span>
                        @elseif($u->item_unity_state_id == 2)
                            <span class="badge badge-secondary">Oculto</span>
                        @elseif($u->item_unity_state_id == 3)
                            <span class="badge badge-danger">Anulado</span>
                        @elseif($u->item_unity_state_id == 4)
                            <span class="badge badge-warning">Manutenção</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </li>
@endif
        
                    
                   
                </ul>

                 {{-- Botões de Ação do Item --}}
                    <div class="mt-3 pt-4">
                        
                           {{-- Botões de Ação do Item --}}
                <div class="mt-3 pt-4">
                    <form id="form-anular-unidade" action="{{ route('unidades.anular', $unidade->id) }}" method="POST" class="form-inline"
                        data-has-kit="{{ $unidade->kitUnity ? 'true' : 'false' }}"
                        data-kit-name="{{ $unidade->kitUnity && $unidade->kitUnity->kit ? $unidade->kitUnity->kit->name : '' }}"
                        data-kit-lia="{{ $unidade->kitUnity ? $unidade->kitUnity->lia_code : '' }}">
                        
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="width: 150px;">Eliminar Unidade</button>
                    </form>
                </div>
                       
            </div>

        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        let liaOriginalValue = $('#lia_code').val();
        let dataOriginalValue = $('#data_aquisicao').val();

        let estadoAnterior = $('#item_unity_state_id').val();

       
       $('#item_unity_state_id').on('change', function() {
            
            let novoEstado = $(this).val();
            let hasKit = $(this).data('has-kit') === true || $(this).data('has-kit') === "true";
            
           
            if (novoEstado == 2 && hasKit) {
                let kitNome = $(this).data('kit-name');
                let kitLia = $(this).data('kit-lia');
                
                let mensagem = `Tem a certeza? O kit cujo nome é "${kitNome}" e o LIA code é "${kitLia}" irá ficar oculto.`;
                
                
                if (!confirm(mensagem)) {
                    // ACRESCENTADO: Se clicar em "Cancelar", reverte o select e cancela o envio
                    $(this).val(estadoAnterior);
                    return false;
                }
            }

            
            estadoAnterior = novoEstado;
           

           
            $('#form-unidade').submit();
        });


                
            $('#form-anular-unidade').on('submit', function(e) {
                
                let hasKit = $(this).data('has-kit') === true || $(this).data('has-kit') === "true";

                if (hasKit) {
                   
                    let kitNome = $(this).data('kit-name');
                    let kitLia = $(this).data('kit-lia');
                    
                    
                    let mensagemAnular = `Tem a certeza que quer eliminar esta unidade? O kit cujo nome é "${kitNome}" e o LIA code é "${kitLia}" irá ficar oculto.`;
                    
                   
                    if (!confirm(mensagemAnular)) {
                        e.preventDefault(); 
                        return false;
                    }
                } else {
                   
                    if (!confirm('Tem a certeza que deseja anular esta unidade?')) {
                        e.preventDefault(); // Bloqueia o envio se cancelar
                        return false;
                    }
                }
            });
            

        
        $('#data_aquisicao').on('change', function() {
            if ($(this).val() !== dataOriginalValue) {
                $('#form-unidade').submit();
            }
        });

       
        $('#lia_code').on('blur', function() {
            if ($(this).val() !== liaOriginalValue) {
                $('#form-unidade').submit();
            }
        });

       
        $('#lia_code').on('keypress', function(e) {
            if (e.which == 13) {
                e.preventDefault();
                $(this).blur();
            }
        });
         @if($errors->any())
            let mensagemErro = "{{ $errors->first() }}";
            alert(mensagemErro);
        @endif
    });

</script>
@endsection
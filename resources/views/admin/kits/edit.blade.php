@php
$i = 0
@endphp
@extends('adminlte::page')

@section('title', 'Editar Kit')

@section('content')

<style>
/* Estilização geral */
#selected-items-container {
    min-height: 50px; /* Garantir espaço inicial */
}

.selected-item-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
    padding: 8px;
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 5px;
}

/* Estilo da barra de pesquisa e lista de disponíveis */
#available-items-container {
    max-height: 300px; /* Limitar altura da lista de itens */
    overflow-y: auto; /* Scroll se necessário */
}

.available-item {
    cursor: pointer;
    transition: background 0.2s;
}

.available-item:hover {
    background: #f0f8ff; /* Realce ao passar o cursor */
}

</style>
<br>
    <div class="d-flex flex-column">
        <form action="{{ route('kits.update', $kit->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Nome</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $kit->name) }}">
                <span style="color:red">{{$errors->first('name')}}</span>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição</label>
                <input type="text" name="description" class="form-control" value="{{ old('description', $kit->description) }}">
                <span style="color:red">{{$errors->first('description')}}</span>
            </div>
            <div class="form-group">
                <label for="lia_code">Codigo LIA</label>
                <input type="text" name="lia_code" class="form-control" value="{{ old('lia_code', $kit->lia_code) }}">
                <span style="color:red">{{$errors->first('lia_code')}}</span>
            </div>
            <div class="form-group">
                <label for="ref_ipvc">Referência IPVC</label>
                <input type="number" name="ipvc_ref" class="form-control" value="{{ old('ipvc_ref', $kit->ipvc_ref) }}">
                <span style="color:red">{{$errors->first('ref_ipvc')}}</span>
            </div>
            <div class="form-group">
                <label for="preco">Preço</label>
                <input type="number" name="price" id="preco" step="0.01" class="form-control" value="{{ old('price', $kit->price) }}">
                <span style="color:red">{{$errors->first('price')}}</span>
            </div>
            <div class="form-group">
                <label for="state">Estado</label>
                <br>
                <select id="state" name="state">
                    <option value=1 <?php if($kit->kit_state_id == '1'){echo("selected");}?>>Visível</option>
                    <option value=2 <?php if($kit->kit_state_id == '2'){echo("selected");}?>>Oculto</option>
                    <option value=3 <?php if($kit->kit_state_id == '3'){echo("selected");}?>>Anulado</option>
                </select>
            </div>
            <div class="form-group">
                <label for="">Imagem para Kit</label>
                <input type="file" class="form-control-file" name="image" id="image">
            </div>
            <div class="form-group">
                <label for="categoria_id">Categoria</label>
                <br>
                <select id="categoria_id" name="categoria_id">
                    @foreach ($categorias as $cat)
                        <option value={{ $cat->id }} <?php if($kit->categoria_id == $cat->id){echo("selected");}?>>{{ $cat->description }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="itens">Itens</label><br/>
                    <div id="selected-items-container">
                        <ul id="list-item">
                            @foreach ($itensKit as $itemKit)
                            <input type="checkbox" name="itens[]" value="{{  $itemKit->id  }}" checked> {{  $itemKit->nome  }}  - <b>código:</b> {{  $itemKit->lia_code  }} <br>
                            @endforeach
                        </ul> <!-- Lista de itens selecionados -->
                        <!-- <input type="checkbox" name="itens[]" value={{  $itemKit->id  }} checked> {{  $itemKit->nome  }}  - código: {{  $itemKit->lia_code  }} <br/> -->
                    </div>

                    <br>
                    <!-- <div class="container mt-4"> -->
                        <div class="mt-4">
                            <h4>Adicionar Itens</h4>
                            <input type="text" id="search-items" class="form-control mb-3" placeholder="Procurar itens..." autocomplete="off">
                            <div id="available-items-container" class="border p-3 rounded bg-light">
                                @foreach ($itens as $item)
                                    <div class="available-item d-flex justify-content-between align-items-center mb-2 p-2 bg-white border rounded"
                                        data-nome="{{ $item->nome }}" data-code="{{ $item->lia_code }}">
                                        <span>
                                            <strong>{{ $item->nome }}</strong> 
                                            <small class="text-muted">({{ $item->lia_code }})</small>
                                        </span>
                                        <button class="btn btn-outline-primary btn-sm add-item-btn" 
                                                data-id="{{ $item->id }}" 
                                                data-nome="{{ $item->nome }}" 
                                                data-code="{{ $item->lia_code }}">
                                            Adicionar
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Modal -->
                        <div class="modal fade" id="itemAlreadySelectedModal" tabindex="-1" aria-labelledby="itemAlreadySelectedModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="itemAlreadySelectedModalLabel">Aviso</h5>
                                <!-- <button type="button" class="btn-close" id="closeModalButton" aria-label="Close"></button> -->
                            </div>
                            <div class="modal-body">
                                <p>Item já selecionado!</p><br>
                                <p style="color:gray;">Clique fora do retângulo para prosseguir</p>
                            </div>
                            <!-- <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" id="closeModalButtonFooter">Fechar</button>
                            </div> -->
                            </div>
                        </div>
                        </div>
                    <!-- </div> -->
                    <!-- @foreach ($itens as $item)
                        <input type="checkbox" name="itens[]" value={{  $item->id  }}> {{  $item->nome  }}  - código: {{  $item->lia_code  }} <br/>
                    @endforeach -->
            </div>
            <button type="submit" class="btn btn-success" style="width: 140px; float:right;">Atualizar Kit</button>
            <br><br>
        </form>
        <br>
    </div>
@endsection

@section('js')
<script type="text/javascript">

// Função para buscar itens à medida que o usuário digita
$(document).on('keyup', '#search-items', function() {
    var search = $(this).val();
    var route = "{{ route('search.itens') }}"; // Ajuste a rota conforme necessário

    $.ajax({
        url: route,
        method: 'GET',
        data: { search: search },
        success: function(response) {
            $('#available-items-container').html(response);
        },
        error: function(xhr, status, error) {
            console.error("Erro no AJAX:", error);
        }
    });
});

// Função para adicionar itens selecionados à lista de itens já selecionados
document.addEventListener("DOMContentLoaded", function () {
    const availableItemsContainer = document.getElementById("available-items-container");

    availableItemsContainer.addEventListener("click", function (e) {
        if (e.target.classList.contains("add-item-btn")) {
            e.preventDefault();
            const btn = e.target;
            const id = btn.dataset.id;
            const nome = btn.dataset.nome;
            const code = btn.dataset.code;

            // Evitar duplicação de itens
            if (document.querySelector(`input[name="itens[]"][value="${id}"]`)) {
                const modal = new bootstrap.Modal(document.getElementById('itemAlreadySelectedModal'));
                modal.show(); // Abre o modal
                return;
            }

            // Criar o checkbox com item
            const newItem = document.createElement("div");
            newItem.innerHTML = `
                <input type="checkbox" name="itens[]" value="${id}" checked> 
                ${nome} - <b>código:</b> ${code}
                <br/>
            `;
            
            // Inserir o item na lista de itens selecionados
            const listItemContainer = document.getElementById("list-item");
            if (listItemContainer) {
                listItemContainer.appendChild(newItem);
            } else {
                console.log("Contêiner de itens selecionados não encontrado.");
            }
        }
    });

    // Prevenir o comportamento de pressionar "Enter" ao focar no campo de pesquisa
    const searchInput = document.getElementById("search-items");
    searchInput.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();  // Impede o envio do formulário
        }
    });
});

</script>
@endsection

@extends('adminlte::page')

@section('title', 'Novo Posto ' . $id)

@section('content')
<br>
<style>
    .dropdown-menu {
        position: absolute;
        width: 100%; /* Faz o dropdown ocupar a largura total do campo de input */
        max-height: 200px; /* Limita a altura do dropdown */
        overflow-y: auto; /* Adiciona rolagem se o conteúdo for maior */
        z-index: 9999; /* Garante que o dropdown apareça sobre outros elementos */
        background-color: white; /* Cor de fundo para o dropdown */
        border: 1px solid #ccc; /* Borda opcional para destacar */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra para destacar */
    }

</style>
<form action="{{ route('space.store', $id) }}" method="post">
    @csrf
    @method('POST')

    <div class="form-group">
        <label for="description">Descrição</label>
        <input class="form-control" type="text" name="description" id="description">
        <span style="color:red">{{$errors->first('description')}}</span>
    </div>

    <div class="form-group">
        <label for="pc">Computador</label>
        <input class="form-control" type="text" name="pc" id="pc">
        <span style="color:red">{{$errors->first('pc')}}</span>
    </div>

    <div class="form-group">
        <label for="teclado">Teclado</label>
        <input class="form-control" type="text" name="teclado" id="teclado">
        <span style="color:red">{{$errors->first('teclado')}}</span>
    </div>

    <div class="form-group">
        <label for="rato">Rato</label>
        <input class="form-control" type="text" name="rato" id="rato">
        <span style="color:red">{{$errors->first('rato')}}</span>
    </div>

    <div class="form-group">
        <label for="lia_code">Código LIA</label>
        <input class="form-control" type="text" name="lia_code" id="lia_code">
        <span style="color:red">{{$errors->first('lia_code')}}</span>
    </div>

    <div class="form-group">
        <label for="price">Custo € / dia</label>
        <input class="form-control" type="number" name="price" step="0.01" id="price">
        <span style="color:red">{{$errors->first('price')}}</span>
    </div>

    <div class="form-group">
        <h3 class="content-header">Constituição do Posto de Trabalho</h3>
        <ul class="list-group" id="list-item" style="width: 100%;">
            </ul>
            <br>
            <button type="button" class="btn btn-outline-primary" onclick="addItem()">Adicionar Equipamento</button>
        <span style="color: red">{{$errors->first('itens.*')}}</span>
    </div>
    <button type="submit" class="btn btn-success" style="width: 140px; float:right">Criar Posto</button>
</form>
@endsection

@section('js')
<script type="text/javascript">
    function addItem() {
        var markup =
            '<li class="list-group-item mt-1" style="border: none; margin: 0; padding: 0; background-color: transparent; width: 100%;">' +
            '<div class="d-flex align-items-center justify-content-between w-100">' +
            '<div class="flex-grow-1 me-3">' +
            ' <input type="text" class="form-control" name="itens[]" placeholder="Pesquise um equipamento..." autocomplete="off" style="width: 100%;">' +
            '<div class="dropdown-menu" style="width: 100%;"></div>' +
            '</div>' +
            '<div class="flex-grow-1 me-3">' +
            ' <input type="text" class="form-control" name="lia_codes[]" placeholder="Código LIA" readonly>' + 
            '</div>' +
            '<div class="text-end">' +
            '<button type="button" class="btn btn-danger" style="margin-left: 10px;" onclick="removeItem(this)">Remover</button>' +
            '</div>' +
            '</div>' +
            '</li>';
        $('#list-item').append(markup);
    }

    function removeItem(btn) {
        var row = btn.parentNode.parentNode;
        row.parentNode.removeChild(row);
    }

    $(document).on('keyup', 'input[name="itens[]"]', function() {
        var search = $(this).val();
        var dropdown = $(this).siblings('.dropdown-menu');
        var route = "{{ route('itens.search') }}";

        $.ajax({
            url: route,
            method: 'GET',
            data: { search: search },
            success: function(response) {
                dropdown.html(response).show();
            }
        });
    });

    $(document).on('click', '.dropdown-item', function() {
        var selectedText = $(this).text().split(' | ')[0]; 
        var liaCode = $(this).data('lia_code');  // O código LIA vem associado ao equipamento
        $(this).closest('li').find('input[name="itens[]"]').val(selectedText);  // Preenche o nome do equipamento
        $(this).closest('li').find('input[name="lia_codes[]"]').val(liaCode);  // Preenche o código LIA
        $(this).closest('.dropdown-menu').hide();
    });

    // $(document).on('click', '.dropdown-item', function() {
    //     var selectedText = $(this).text();
    //     $(this).closest('li').find('input').val(selectedText);
    //     $(this).closest('.dropdown-menu').hide();
    // });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.form-control').length) {
            $('.dropdown-menu').hide();
        }
    });
</script>
@endsection
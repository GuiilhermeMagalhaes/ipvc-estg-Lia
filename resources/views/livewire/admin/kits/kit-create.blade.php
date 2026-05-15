@section('title', 'Kits')

@section('content_header')
    <h1>Novo Kit</h1>
@stop

<div class="d-flex flex-column">
    <form wire:submit.prevent="createKit">
        <div class="form-group">
            <label for="descricao">Descrição</label>
            <input type="text" name="descricao" id="descricao" class="form-control" value="{{ old('descricao') }}" wire:model="description">
            <span style="color:red">{{$errors->first('descricao')}}</span>
        </div>
        <div class="form-group">
            <label for="lia_code">Codigo LIA</label>
            <input type="text" name="lia_code" id="lia_code" class="form-control" value="{{ old('lia_code') }}" wire:model="lia_code">
            <span style="color:red">{{$errors->first('lia_code')}}</span>
        </div>
        <div class="form-group">
            <label for="ref_ipvc">Referência IPVC</label>
            <input type="text" name="ref_ipvc" id="ref_ipvc" class="form-control" value="{{ old('ref_ipvc') }}" wire:model="ipvc_ref">
            <span style="color:red">{{$errors->first('ref_ipvc')}}</span>
        </div>
        <div class="form-group">
            <label for="categoria">Categoria</label>
            <select class="form-control" wire:model="kit_category">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->description }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="preco">Preço</label>
            <input type="text" name="preco" id="preco" class="form-control" value="{{ old('preco') }}" wire:model="price">
            <span style="color:red">{{$errors->first('preco')}}</span>
        </div>

        <h1>Itens</h1>
        <button type="button" class="btn btn-primary" wire:click="addKit">Adicionar Kit</button>
        <button type="button" class="btn btn-primary" wire:click="addItem">Adicionar Item</button>

        <table id="tableItem" class="table">
            <tbody>
                @foreach ($items as $item)
                <tr>
                    <td>
                        <label for="">Descrição</label>
                        <input type="text" class="kit form-control">
                    </td>
                    <td>
                        <label for="">referencia ipvc</label>
                        <input type="text" class="kit form-control">
                    </td>
                    <td>
                        <label for="">serial number</label>
                        <input type="text" class="kit form-control">
                    </td><td>
                        <label for="">modelo</label>
                        <input type="text" class="kit form-control">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <table id="tableKit" class="table">
            <tbody>
                @foreach ($kits as $kit)
                    <tr>
                        <td>
                            <label for="">Código Kit</label>
                            <input type="text" class="kit form-control">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit" class="btn btn-success">Criar Kit</button>
    </form>
</div>
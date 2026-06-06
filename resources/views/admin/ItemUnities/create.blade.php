@extends('adminlte::page')

@section('title', 'Definir Unidades')

@section('content')
<br>
    <div class="d-flex flex-column">
        <p class="text-dark list-group-item-text" style="font-size: 1.2rem;">Códigos LIA para as {{ $quantity }} Unidades de "{{ $item_nome }}" </p> 
            <!--<h3 class="mb-4"> Códigos LIA para as {{ $quantity }} Unidades de "{{ $item_nome }}"</h3> -->
            <form action="{{ route('itens.storeUnities') }}" method="POST">
                @csrf

                @for ($i = 1; $i <= $quantity; $i++)
                    <div class="form-group">
                        <label>Código LIA da Unidade #{{ $i }}</label>
                        <input type="text" name="lia_codes[]" class="form-control" value="{{ old('lia_codes.'.($i-1)) }}" >
                        @if($errors->has("lia_codes.".($i-1)))
                            <span style="color:red">{{ $errors->first("lia_codes.".($i-1)) }}</span>
                        @endif
                    </div>

                    <div class="form-group">
                        <label>Data de Aquisição</label>
                        <input type="date" name="data_aquisicao[]" class="form-control" value="{{ old('data_aquisicao.'.($i-1)) }}" max="{{ date('Y-m-d') }}" >
                        @if($errors->has("data_aquisicao.".($i-1)))
                            <span style="color:red">{{ $errors->first("data_aquisicao.".($i-1)) }}</span>
                        @endif
                    </div>
                @endfor
                <div class="mt-4">
                 <button type="button" onclick="window.history.back();" class="btn btn-secondary mr-2" style="width: 140px;">
                        Voltar
                    </button>
                <button type="submit" class="btn btn-primary" style="width: 150px;">Criar item</button>
                </div>
            </form>
    </div>
@endsection
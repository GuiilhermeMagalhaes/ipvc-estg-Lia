@extends('adminlte::page')

@section('title', 'Definir Unidades')

@section('content')
<br>
    <div class="d-flex flex-column">
            <h3 class="mb-4"> Códigos LIA para as {{ $quantity }} Unidades de "{{ $item_nome }}"</h3>
            <form action="{{ route('itens.storeUnities') }}" method="POST">
                @csrf

                @for ($i = 1; $i <= $quantity; $i++)
                    <div class="form-group">
                        <label>Código LIA da Unidade #{{ $i }}</label>
                        <input type="text" name="lia_codes[]" class="form-control" value="{{ old('lia_codes.'.($i-1)) }}" required>
                        @if($errors->has("lia_codes.".($i-1)))
                            <span style="color:red">{{ $errors->first("lia_codes.".($i-1)) }}</span>
                        @endif
                    </div>
                @endfor

                <button type="submit" class="btn btn-success" style="width: 140px;">Criar itens</button>
            </form>
    </div>
@endsection
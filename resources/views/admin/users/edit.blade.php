@php
@endphp
@extends('adminlte::page')

@section('title', 'Editar Permissões')

@section('content')
<br>
    <div class="d-flex flex-column">
        <form action="{{ route('user.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                @foreach ($user_types as $type)
                    <input type="radio" id={{ $type->id }} name="user_type_id" value={{ $type->id }} <?php if($user->user_type_id == $type->id){echo("checked");}?>>
                    <label for={{ $type->id }}>{{ $type->description }}</label><br>
                @endforeach
            </div>
            <button type="submit" class="btn btn-success">Atualizar Permissões</button>
        </form>
    </div>
@endsection
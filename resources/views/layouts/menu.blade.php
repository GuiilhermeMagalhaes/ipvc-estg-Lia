@extends('index')

@section('content')
<div class="row">
    @foreach ($categories as $category)
    <div class="col-sm-4">
        <a href="/categoria/{{ $category->id }}" class="card-link">
            <div class="card categoria-container" style="background-color: black;">
                <img src="{{ $category->image }}" class="imgcard" />
                <div class="nome">
                    <div class="nome-texto" style="font-size:x-large;">
                        {{ $category->description }}
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>
@endsection
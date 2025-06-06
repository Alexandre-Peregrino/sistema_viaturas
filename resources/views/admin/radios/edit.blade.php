@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Rádio</h2>

    <form action="{{ route('admin.radios.update', $radio->id) }}" method="POST">
        @method('PUT')
        @include('admin.radios.form')
    </form>
</div>
@endsection

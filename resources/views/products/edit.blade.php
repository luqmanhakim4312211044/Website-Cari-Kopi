@extends('layouts.dashboard')
@section('title', 'Edit Produk')
@section('panel')
    <div class="page-head"><h1>Edit produk</h1></div>
    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="panel panel-body">
        @csrf @method('PATCH')
        @include('products._form')
    </form>
@endsection

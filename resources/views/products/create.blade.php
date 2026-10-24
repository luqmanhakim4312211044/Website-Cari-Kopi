@extends('layouts.dashboard')
@section('title', 'Tambah Produk')
@section('panel')
    <div class="page-head"><h1>Tambah produk</h1></div>
    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="panel panel-body">
        @csrf
        @include('products._form')
    </form>
@endsection

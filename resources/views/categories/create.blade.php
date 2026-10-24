@extends('layouts.dashboard')
@section('title', 'Tambah Kategori')
@section('panel')
    <div class="page-head"><h1>Tambah kategori</h1></div>
    <form action="{{ route('categories.store') }}" method="POST" class="panel panel-body" style="max-width:32rem">
        @csrf
        @include('categories._form')
    </form>
@endsection

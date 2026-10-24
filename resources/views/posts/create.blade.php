@extends('layouts.dashboard')
@section('title', 'Tambah Cafe')
@section('panel')
    <div class="page-head"><h1>Tambah cafe</h1></div>
    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="panel panel-body">
        @csrf
        @include('posts._form')
    </form>
@endsection

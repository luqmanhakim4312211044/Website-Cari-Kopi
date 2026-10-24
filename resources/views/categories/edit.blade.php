@extends('layouts.dashboard')
@section('title', 'Edit Kategori')
@section('panel')
    <div class="page-head"><h1>Edit kategori</h1></div>
    <form action="{{ route('categories.update', $category) }}" method="POST" class="panel panel-body" style="max-width:32rem">
        @csrf @method('PUT')
        @include('categories._form')
    </form>
@endsection

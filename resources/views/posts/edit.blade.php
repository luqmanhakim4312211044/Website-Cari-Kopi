@extends('layouts.dashboard')
@section('title', 'Edit Cafe')
@section('panel')
    <div class="page-head"><h1>Edit cafe</h1></div>
    <form action="{{ route('posts.update', $post) }}" method="POST" enctype="multipart/form-data" class="panel panel-body">
        @csrf @method('PATCH')
        @include('posts._form')
    </form>
@endsection

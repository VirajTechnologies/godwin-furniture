@extends('layouts.admin')

@section('title', 'Edit Category')
@section('page_title', 'Edit '.$category->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categories</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">{{ $category->name }}</h5></div>
                <div class="card-body">@include('admin.categories._form')</div>
            </div>
        </div>
    </div>
@endsection

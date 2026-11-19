@extends('layouts.admin')

@section('title', 'Add Product')
@section('page_title', 'Add Product')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            @include('admin.products._form')
        </div>
    </div>
@endsection

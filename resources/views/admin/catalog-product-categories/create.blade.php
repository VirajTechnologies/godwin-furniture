@extends('layouts.admin')

@section('title', 'Add Product Category')
@section('page_title', 'Add Product Category')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.catalog-product-categories.index') }}">Product Categories</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    @include('admin.catalog-product-categories._form')
                </div>
            </div>
        </div>
    </div>
@endsection
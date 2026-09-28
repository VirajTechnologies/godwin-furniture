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
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Product Details</h5></div>
                <div class="card-body">@include('admin.products._form')</div>
            </div>
        </div>
    </div>
@endsection

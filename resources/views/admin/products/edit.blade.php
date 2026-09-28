@extends('layouts.admin')

@section('title', 'Edit Product')
@section('page_title', 'Edit '.$product->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">{{ $product->code }} · {{ $product->name }}</h5></div>
                <div class="card-body">@include('admin.products._form')</div>
            </div>
        </div>
    </div>
@endsection

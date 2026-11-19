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
            <div class="alert alert-light border">
                <span class="fw-medium">{{ $product->code }}</span> · {{ $product->name }}
                @if ($product->path() !== '')
                    <span class="text-muted">· {{ $product->path() }}</span>
                @endif
            </div>
            @include('admin.products._form')
        </div>
    </div>
@endsection

@extends('layouts.admin')

@section('title', 'Add Product Model')
@section('page_title', 'Masters')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.product-models.index') }}">Product Models</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
    @include('admin.partials.masters-tabs')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    @include('admin.product-models._form')
                </div>
            </div>
        </div>
    </div>
@endsection
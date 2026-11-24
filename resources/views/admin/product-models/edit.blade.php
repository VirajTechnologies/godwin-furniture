@extends('layouts.admin')

@section('title', 'Edit Product Model')
@section('page_title', 'Edit Product Model')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.product-models.index') }}">Product Models</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
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
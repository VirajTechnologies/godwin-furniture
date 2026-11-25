@extends('layouts.admin')

@section('title', 'Add Finish')
@section('page_title', 'Add Finish')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.product-finishes.index') }}">Finishes</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    @include('admin.product-finishes._form')
                </div>
            </div>
        </div>
    </div>
@endsection
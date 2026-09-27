@extends('layouts.admin')

@section('title', 'Add warehouse')
@section('page_title', 'Add warehouse')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">Warehouses</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Warehouse details</h5>
                </div>
                <div class="card-body">
                    @include('admin.warehouses._form')
                </div>
            </div>
        </div>
    </div>
@endsection

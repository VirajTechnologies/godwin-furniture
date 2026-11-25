@extends('layouts.admin')

@section('title', 'Add Upholstery')
@section('page_title', 'Add Upholstery')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.product-upholsteries.index') }}">Upholstery</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    @include('admin.product-upholsteries._form')
                </div>
            </div>
        </div>
    </div>
@endsection
@extends('layouts.admin')

@section('title', 'Edit Upholstery')
@section('page_title', 'Edit Upholstery')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.product-upholsteries.index') }}">Upholstery</a></li>
    <li class="breadcrumb-item active">Edit</li>
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
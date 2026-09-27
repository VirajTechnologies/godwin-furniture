@extends('layouts.admin')

@section('title', 'Edit warehouse')
@section('page_title', 'Edit '.$warehouse->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">Warehouses</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ $warehouse->code }} · {{ $warehouse->name }}</h5>
                </div>
                <div class="card-body">
                    @include('admin.warehouses._form')
                </div>
            </div>
        </div>
    </div>
@endsection

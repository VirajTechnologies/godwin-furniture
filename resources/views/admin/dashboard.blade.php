@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-4 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Warehouses</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-3 mb-0">{{ $warehouseCount }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Active</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-3 mb-0">{{ $activeCount }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Primary warehouse</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-3 mb-0">{{ $primaryWarehouse->name ?? 'Not set' }}</h4>
                    @if ($primaryWarehouse)
                        <p class="text-muted mb-0 mt-2">{{ $primaryWarehouse->code }}@if ($primaryWarehouse->district) · {{ $primaryWarehouse->district->district_name }}@endif</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Finished goods start here</h5>
                    <p class="text-muted mb-3">Godwin has one warehouse today. Add it as the primary warehouse. Further warehouses can be added from the same screen.</p>
                    <a href="{{ route('admin.warehouses.index') }}" class="btn btn-success">Manage warehouses</a>
                </div>
            </div>
        </div>
    </div>
@endsection

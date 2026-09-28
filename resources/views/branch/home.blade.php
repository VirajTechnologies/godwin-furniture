@extends('layouts.branch')

@section('title', 'Counter')
@section('page_title', 'Counter')

@section('breadcrumb')
    <li class="breadcrumb-item active">Counter</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Today</p>
                    <h4 class="mb-0">{{ $saleCount }} {{ $saleCount === 1 ? 'sale' : 'sales' }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Today's Total</p>
                    <h4 class="mb-0">₹{{ number_format((float) $saleTotal, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Branch</p>
                    <h4 class="mb-3">{{ $branch->name }}</h4>
                    <a href="{{ route('branch.sales.create') }}" class="btn btn-success">New Sale</a>
                </div>
            </div>
        </div>
    </div>
@endsection

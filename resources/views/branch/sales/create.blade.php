@extends('layouts.branch')

@section('title', 'New Sale')
@section('page_title', 'New Sale')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('branch.sales.index') }}">Sales</a></li>
    <li class="breadcrumb-item active">New</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Sale Details</h5></div>
                <div class="card-body">
                    @if ($stocks->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No stock at this branch</h5>
                            <p class="text-muted mb-0">Pieces arrive here only when a transfer from the warehouse is received.</p>
                        </div>
                    @else
                        @include('branch.sales._form')
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

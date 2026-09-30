@extends('layouts.branch')

@section('title', 'New Request')
@section('page_title', 'New Request')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('branch.stock-requests.index') }}">Request</a></li>
    <li class="breadcrumb-item active">New</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Ask Head Office For Stock</h5></div>
                <div class="card-body">
                    @include('branch.stock-requests._form')
                </div>
            </div>
        </div>
    </div>
@endsection

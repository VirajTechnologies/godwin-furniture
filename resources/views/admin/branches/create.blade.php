@extends('layouts.admin')

@section('title', 'Add Branch')
@section('page_title', 'Add Branch')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.branches.index') }}">Branches</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Branch Details</h5></div>
                <div class="card-body">@include('admin.branches._form')</div>
            </div>
        </div>
    </div>
@endsection

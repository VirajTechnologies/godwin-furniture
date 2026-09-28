@extends('layouts.admin')

@section('title', 'Add Employee')
@section('page_title', 'Add Employee')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Employee Details</h5></div>
                <div class="card-body">@include('admin.employees._form')</div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.admin')

@section('title', 'Edit Employee')
@section('page_title', 'Edit '.$employee->user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">{{ $employee->employee_code }} · {{ $employee->user->name }}</h5></div>
                <div class="card-body">@include('admin.employees._form')</div>
            </div>
        </div>
    </div>
@endsection

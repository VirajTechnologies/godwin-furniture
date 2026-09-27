@extends('layouts.admin')

@section('title', 'Add city')
@section('page_title', 'Add city')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.cities.index') }}">Cities</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">City details</h5></div>
                <div class="card-body">@include('admin.cities._form')</div>
            </div>
        </div>
    </div>
@endsection

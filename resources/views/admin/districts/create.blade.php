@extends('layouts.admin')

@section('title', 'Add district')
@section('page_title', 'Add district')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.districts.index') }}">Districts</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">District details</h5></div>
                <div class="card-body">@include('admin.districts._form')</div>
            </div>
        </div>
    </div>
@endsection

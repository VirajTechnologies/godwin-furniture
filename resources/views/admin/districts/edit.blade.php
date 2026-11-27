@extends('layouts.admin')

@section('title', 'Edit district')
@section('page_title', 'Masters')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.districts.index') }}">Districts</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    @include('admin.partials.masters-tabs')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">{{ $district->district_name }}</h5></div>
                <div class="card-body">@include('admin.districts._form')</div>
            </div>
        </div>
    </div>
@endsection

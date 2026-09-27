@extends('layouts.admin')

@section('title', 'Edit city')
@section('page_title', 'Edit '.$city->city_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.cities.index') }}">Cities</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">{{ $city->city_name }}</h5></div>
                <div class="card-body">@include('admin.cities._form')</div>
            </div>
        </div>
    </div>
@endsection
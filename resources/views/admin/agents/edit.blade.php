@extends('layouts.admin')

@section('title', 'Edit Agent')
@section('page_title', $agent->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.agents.index') }}">Agents</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">{{ $agent->name }}</h5></div>
                <div class="card-body">@include('admin.agents._form')</div>
            </div>
        </div>
    </div>
@endsection

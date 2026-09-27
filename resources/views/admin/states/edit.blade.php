@extends('layouts.admin')

@section('title', 'Edit state')
@section('page_title', 'Edit '.$state->state_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.states.index') }}">States</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">{{ $state->state_name }}</h5></div>
                <div class="card-body">
                    @include('admin.states._form')
                </div>
            </div>
        </div>
    </div>
@endsection

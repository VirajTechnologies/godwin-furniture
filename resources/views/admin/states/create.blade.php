@extends('layouts.admin')

@section('title', 'Add state')
@section('page_title', 'Add state')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.states.index') }}">States</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">State details</h5></div>
                <div class="card-body">
                    @include('admin.states._form')
                </div>
            </div>
        </div>
    </div>
@endsection

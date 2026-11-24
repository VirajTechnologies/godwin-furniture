@extends('layouts.admin')

@section('title', 'Edit Business Group')
@section('page_title', 'Edit Business Group')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.business-groups.index') }}">Business Groups</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    @include('admin.business-groups._form')
                </div>
            </div>
        </div>
    </div>
@endsection
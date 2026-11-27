@extends('layouts.admin')

@section('title', 'Edit Design Type')
@section('page_title', 'Masters')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.design-types.index') }}">Design Types</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    @include('admin.partials.masters-tabs')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    @include('admin.design-types._form')
                </div>
            </div>
        </div>
    </div>
@endsection
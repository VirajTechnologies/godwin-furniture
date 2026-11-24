@extends('layouts.admin')

@section('title', 'Edit Project')
@section('page_title', 'Edit Project')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.project-categories.index') }}">Projects</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    @include('admin.project-categories._form')
                </div>
            </div>
        </div>
    </div>
@endsection
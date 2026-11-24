@extends('layouts.admin')

@section('title', 'Add Project')
@section('page_title', 'Add Project')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.project-categories.index') }}">Projects</a></li>
    <li class="breadcrumb-item active">Add</li>
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
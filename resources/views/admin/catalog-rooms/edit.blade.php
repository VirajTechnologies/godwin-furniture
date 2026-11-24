@extends('layouts.admin')

@section('title', 'Edit Room')
@section('page_title', 'Edit Room')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.catalog-rooms.index') }}">Rooms</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    @include('admin.catalog-rooms._form')
                </div>
            </div>
        </div>
    </div>
@endsection
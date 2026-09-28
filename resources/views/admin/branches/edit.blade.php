@extends('layouts.admin')

@section('title', 'Edit Branch')
@section('page_title', 'Edit '.$branch->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.branches.index') }}">Branches</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">{{ $branch->code }} · {{ $branch->name }}</h5></div>
                <div class="card-body">@include('admin.branches._form')</div>
            </div>
        </div>
    </div>
@endsection

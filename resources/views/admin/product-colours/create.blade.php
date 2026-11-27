@extends('layouts.admin')

@section('title', 'Add Colour')
@section('page_title', 'Masters')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.product-colours.index') }}">Colours</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
    @include('admin.partials.masters-tabs')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    @include('admin.product-colours._form')
                </div>
            </div>
        </div>
    </div>
@endsection
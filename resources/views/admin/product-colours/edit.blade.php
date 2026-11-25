@extends('layouts.admin')

@section('title', 'Edit Colour')
@section('page_title', 'Edit Colour')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.product-colours.index') }}">Colours</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
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
@extends('layouts.admin')

@section('title', $order->code)
@section('page_title', $order->code)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Sales</a></li>
    <li class="breadcrumb-item active">{{ $order->code }}</li>
@endsection

@section('content')
    @include('branch.sales._receipt')
    <a href="{{ route('admin.orders.index') }}" class="btn btn-light">Back</a>
@endsection

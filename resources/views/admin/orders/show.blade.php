@extends('layouts.admin')

@section('title', $order->code)
@section('page_title', $order->code)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Sales</a></li>
    <li class="breadcrumb-item active">{{ $order->code }}</li>
@endsection

@section('content')
    @include('admin.orders._timeline')
    @include('branch.sales._receipt')

    @if ($order->isOnline() && $order->nextFulfilmentAction())
        <div class="card">
            <div class="card-body d-flex flex-wrap gap-2 align-items-center">
                <span class="text-muted me-2">Fulfilment:</span>
                @if ($order->nextFulfilmentAction() === 'confirm')
                    <form method="POST" action="{{ route('admin.orders.confirm', $order) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Confirm Order</button>
                    </form>
                @elseif ($order->nextFulfilmentAction() === 'dispatch')
                    <form method="POST" action="{{ route('admin.orders.dispatch', $order) }}" onsubmit="return confirm('Dispatch this order and deduct warehouse stock?');">
                        @csrf
                        <button type="submit" class="btn btn-primary">Dispatch (deduct stock)</button>
                    </form>
                @elseif ($order->nextFulfilmentAction() === 'complete')
                    <form method="POST" action="{{ route('admin.orders.complete', $order) }}">
                        @csrf
                        <button type="submit" class="btn btn-success">Mark Completed</button>
                    </form>
                @endif
            </div>
        </div>
    @endif

    <a href="{{ route('admin.orders.index') }}" class="btn btn-light">Back</a>
@endsection

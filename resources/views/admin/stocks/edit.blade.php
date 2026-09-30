@extends('layouts.admin')

@section('title', 'Edit Stock')
@section('page_title', 'Edit Stock')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.stocks.index') }}">Warehouse Stock</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">{{ $stock->product?->code }} · {{ $stock->warehouse?->name }}</h5></div>
                <div class="card-body">@include('admin.stocks._form')</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Recent Movements</h5></div>
                <div class="card-body">
                    @if ($movements->isEmpty())
                        <p class="text-muted mb-0">No movements yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Branch</th>
                                        <th>Change</th>
                                        <th>Balance</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($movements as $movement)
                                        <tr>
                                            <td>{{ $movement->created_at?->format('d M Y, h:i A') }}</td>
                                            <td>{{ $movement->label() }}</td>
                                            <td>{{ $movement->transfer?->branch?->name ?? '—' }}</td>
                                            <td>{{ $movement->quantity_change > 0 ? '+' : '' }}{{ $movement->quantity_change }}</td>
                                            <td>{{ $movement->quantity_after }}</td>
                                            <td>
                                                @if ($movement->transfer)
                                                    <a href="{{ route('admin.transfers.edit', $movement->transfer) }}">{{ $movement->note ?: $movement->transfer->code }}</a>
                                                @else
                                                    {{ $movement->note ?: '—' }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

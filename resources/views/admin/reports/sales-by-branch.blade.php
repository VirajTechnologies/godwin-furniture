@extends('layouts.admin')

@section('title', 'Sales By Branch')
@section('page_title', 'Sales By Branch')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sales By Branch</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Sales By Branch</h5></div>
                <div class="card-body">
                    @include('admin.reports._dates')
                    <p class="text-muted">Showrooms and shops. Commission is what the shops earned.</p>
                    @if ($rows->isEmpty())
                        <p class="text-muted mb-0">No sales in this period.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Type</th>
                                        <th>Place</th>
                                        <th>Bills</th>
                                        <th>Amount</th>
                                        <th>Commission</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $row)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $row->kind }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->bills }}</td>
                                            <td>₹{{ number_format((float) $row->amount, 2) }}</td>
                                            <td>₹{{ number_format((float) $row->commission, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4">Total</th>
                                        <th>₹{{ number_format((float) $amount, 2) }}</th>
                                        <th>₹{{ number_format((float) $commission, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

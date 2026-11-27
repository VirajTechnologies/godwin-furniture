@extends('layouts.admin')

@section('title', 'Custom Request Report')
@section('page_title', 'Reports & Analytics')

@section('breadcrumb')
    <li class="breadcrumb-item active">Custom Request Report</li>
@endsection

@section('content')
    @include('admin.partials.reports-tabs')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Custom Requests</h5></div>
                <div class="card-body">
                    @include('admin.reports._dates')
                    @if ($requests->isEmpty())
                        <p class="text-muted mb-0">No custom requests in this period.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SR No.</th>
                                        <th>Code</th>
                                        <th>Agent</th>
                                        <th>Model</th>
                                        <th>Source</th>
                                        <th>Status</th>
                                        <th>Requested</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requests as $customRequest)
                                        <tr>
                                            <td>{{ $requests->firstItem() + $loop->index }}</td>
                                            <td class="fw-medium"><a href="{{ route('admin.custom-requests.show', $customRequest) }}">{{ $customRequest->code }}</a></td>
                                            <td>{{ $customRequest->agent?->name ?? '—' }}</td>
                                            <td>{{ $customRequest->displayName() }}</td>
                                            <td>{{ $customRequest->isMatched() ? 'From catalog' : 'Not in catalog' }}</td>
                                            <td>{{ $customRequest->label() }}</td>
                                            <td>{{ $customRequest->created_at?->format('d M Y, h:i A') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $requests->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

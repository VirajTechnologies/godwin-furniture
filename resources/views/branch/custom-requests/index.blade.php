@extends('layouts.branch')

@section('title', 'Custom Requests')
@section('page_title', 'Custom Requests')

@section('breadcrumb')
    <li class="breadcrumb-item active">Custom Requests</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Custom Requests</h5></div>
                <div class="card-body">
                    @if ($requests->isEmpty())
                        <div class="text-center py-5">
                            <h5 class="mb-2">No custom requests yet</h5>
                            <p class="text-muted mb-0">Agent customization requests to this primary branch appear here.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Agent</th>
                                        <th>Model</th>
                                        <th>Status</th>
                                        <th>Requested</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requests as $customRequest)
                                        <tr>
                                            <td class="fw-medium">
                                                <a href="{{ route('branch.custom-requests.show', $customRequest) }}">{{ $customRequest->code }}</a>
                                            </td>
                                            <td>{{ $customRequest->agent?->name ?? '—' }}</td>
                                            <td>{{ $customRequest->displayName() }}</td>
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

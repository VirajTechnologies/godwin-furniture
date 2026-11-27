@extends('layouts.agent')

@section('title', $customRequest->code)
@section('page_title', $customRequest->code)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('agent.custom-requests.index') }}">Custom Requests</a></li>
    <li class="breadcrumb-item active">{{ $customRequest->code }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">{{ $customRequest->code }} · {{ $customRequest->label() }}</h5>
                    @if ($customRequest->isRequested())
                        <form method="POST" action="{{ route('agent.custom-requests.cancel', $customRequest) }}" onsubmit="return confirm('Cancel this request?')">
                            @csrf
                            <button type="submit" class="btn btn-soft-danger">Cancel Request</button>
                        </form>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Primary branch</label>
                            <input type="text" class="form-control" value="{{ $customRequest->branch?->name }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Requested</label>
                            <input type="text" class="form-control" value="{{ $customRequest->created_at?->format('d M Y, h:i A') }}" readonly>
                        </div>
                    </div>
                    @include('admin.custom-requests._details', ['customRequest' => $customRequest])
                    <a href="{{ route('agent.custom-requests.index') }}" class="btn btn-light mt-4">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection

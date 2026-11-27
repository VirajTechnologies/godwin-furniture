@extends('layouts.branch')

@section('title', $customRequest->code)
@section('page_title', $customRequest->code)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('branch.custom-requests.index') }}">Custom Requests</a></li>
    <li class="breadcrumb-item active">{{ $customRequest->code }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ $customRequest->code }} · {{ $customRequest->label() }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Agent</label>
                            <input type="text" class="form-control" value="{{ $customRequest->agent?->name }}" readonly>
                        </div>
                    </div>
                    @include('admin.custom-requests._details', ['customRequest' => $customRequest])
                    <a href="{{ route('branch.custom-requests.index') }}" class="btn btn-light mt-4">Back</a>
                </div>
            </div>

            @include('admin.custom-requests._status-actions', [
                'customRequest' => $customRequest,
                'statusUrl' => $statusUrl,
            ])
        </div>
    </div>
@endsection

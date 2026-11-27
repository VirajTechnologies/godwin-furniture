@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    @php
        $barMax = function (array $rows): float {
            $max = 0.0;
            foreach ($rows as $row) {
                $max = max($max, (float) ($row['value'] ?? 0));
            }

            return $max > 0 ? $max : 1.0;
        };
        $moneyBars = $barMax($monthlyRevenue);
        $branchBarsMax = $barMax($branchSales);
        $stockBarsMax = $barMax($locationStock);
        $channelBarsMax = $barMax($channelMix);
        $topBarsMax = $barMax($topProducts);
        $statusBarsMax = $barMax($orderStatusBars);
    @endphp

    {{-- Title + location (reference header) --}}
    <div class="row mb-3 align-items-center">
        <div class="col-lg-8">
            <h4 class="mb-0">Central Control Dashboard · {{ $filterLabel }}</h4>
        </div>
        <div class="col-lg-4">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex justify-content-lg-end">
                <select class="form-select" id="dashboard_location" name="location" onchange="this.form.submit()" style="max-width: 280px;">
                    <option value="all" @selected($filter['key'] === 'all')>Overall Business</option>
                    @foreach ($branches as $branch)
                        <option value="branch:{{ $branch->id }}" @selected($filter['key'] === 'branch:'.$branch->id)>{{ $branch->name }}</option>
                    @endforeach
                    @foreach ($warehouses as $warehouse)
                        <option value="warehouse:{{ $warehouse->id }}" @selected($filter['key'] === 'warehouse:'.$warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    {{-- KPI row: 8 cards, 4 across (reference .gr minmax ~210px) --}}
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Revenue</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-3 mb-0">₹{{ number_format((float) $totalRevenue, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Sales</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-3 mb-0">{{ $totalSalesCount }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Orders</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-3 mb-0">{{ $totalOrders }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Products</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-3 mb-0">{{ $totalProducts }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Stock</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-3 mb-0">{{ number_format($totalStock) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Customers</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-3 mb-0">{{ $totalCustomers }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Pending Orders</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-3 mb-0">{{ $pendingOrders }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Pending Deliveries</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-3 mb-0">{{ $pendingDeliveries }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart row: 6 cards, 3 across (reference .gr.g2 ≈ minmax 330px) --}}
    <div class="row">
        <div class="col-xl-4 col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">{{ $monthlyRevenueTitle }}</h5></div>
                <div class="card-body">
                    @foreach ($monthlyRevenue as $row)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="text-muted" style="width: 72px;">{{ $row['label'] }}</div>
                            <div class="progress flex-grow-1" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: {{ ($row['value'] / $moneyBars) * 100 }}%"></div>
                            </div>
                            <div class="fw-medium text-end" style="width: 100px;">₹{{ number_format($row['value'], 0) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Branch-wise sales</h5></div>
                <div class="card-body">
                    @forelse ($branchSales as $row)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="text-muted text-truncate" style="width: 120px;" title="{{ $row['label'] }}">{{ $row['label'] }}</div>
                            <div class="progress flex-grow-1" style="height: 10px;">
                                <div class="progress-bar bg-primary" style="width: {{ ($row['value'] / $branchBarsMax) * 100 }}%"></div>
                            </div>
                            <div class="fw-medium text-end" style="width: 100px;">₹{{ number_format($row['value'], 0) }}</div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No branch sales yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Warehouse / location stock</h5></div>
                <div class="card-body">
                    @forelse ($locationStock as $row)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="text-muted text-truncate" style="width: 120px;" title="{{ $row['label'] }}">{{ $row['label'] }}</div>
                            <div class="progress flex-grow-1" style="height: 10px;">
                                <div class="progress-bar bg-info" style="width: {{ ($row['value'] / $stockBarsMax) * 100 }}%"></div>
                            </div>
                            <div class="fw-medium text-end" style="width: 80px;">{{ number_format($row['value']) }}</div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No stock recorded.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Online vs showroom vs agent</h5></div>
                <div class="card-body">
                    @foreach ($channelMix as $row)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="text-muted" style="width: 110px;">{{ $row['label'] }}</div>
                            <div class="progress flex-grow-1" style="height: 10px;">
                                <div class="progress-bar bg-warning" style="width: {{ ($row['value'] / $channelBarsMax) * 100 }}%"></div>
                            </div>
                            <div class="fw-medium text-end" style="width: 100px;">₹{{ number_format($row['value'], 0) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Top selling products</h5></div>
                <div class="card-body">
                    @forelse ($topProducts as $row)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="text-muted text-truncate" style="width: 140px;" title="{{ $row['label'] }}">{{ \Illuminate\Support\Str::limit($row['label'], 14, '') }}</div>
                            <div class="progress flex-grow-1" style="height: 10px;">
                                <div class="progress-bar bg-secondary" style="width: {{ ($row['value'] / $topBarsMax) * 100 }}%"></div>
                            </div>
                            <div class="fw-medium text-end" style="width: 60px;">{{ $row['value'] }}</div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No product sales yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Order status</h5></div>
                <div class="card-body">
                    @foreach ($orderStatusBars as $row)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="text-muted" style="width: 140px;">{{ $row['label'] }}</div>
                            <div class="progress flex-grow-1" style="height: 10px;">
                                <div class="progress-bar" style="width: {{ ($row['value'] / $statusBarsMax) * 100 }}%"></div>
                            </div>
                            <div class="fw-medium text-end" style="width: 60px;">{{ $row['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Branch + warehouse metric cards, 3 across --}}
    <div class="row">
        @foreach ($branchCards as $card)
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">{{ $card['name'] }}</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Metric</th>
                                        <th class="text-end">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>Total sales</td><td class="text-end fw-medium">{{ $card['sales'] }}</td></tr>
                                    <tr><td>Revenue</td><td class="text-end fw-medium">₹{{ number_format($card['revenue'], 2) }}</td></tr>
                                    <tr><td>Available stock</td><td class="text-end fw-medium">{{ number_format($card['stock']) }}</td></tr>
                                    <tr><td>Products sold</td><td class="text-end fw-medium">{{ number_format($card['units']) }}</td></tr>
                                    <tr><td>Pending (balance due)</td><td class="text-end fw-medium">{{ $card['pendingBalance'] }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @foreach ($warehouseCards as $card)
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">{{ $card['name'] }}</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Metric</th>
                                        <th class="text-end">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>Total stock</td><td class="text-end fw-medium">{{ number_format($card['stock']) }}</td></tr>
                                    <tr><td>Incoming</td><td class="text-end fw-medium">{{ number_format($card['incoming']) }}</td></tr>
                                    <tr><td>Outgoing</td><td class="text-end fw-medium">{{ number_format($card['outgoing']) }}</td></tr>
                                    <tr><td>Pending transfers</td><td class="text-end fw-medium">{{ $card['pendingTransfers'] }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Bottom: Recent transactions + Recent activity, 2 across --}}
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Recent transactions</h5></div>
                <div class="card-body">
                    @if (count($recentTransactions) === 0)
                        <p class="text-muted mb-0">No transactions yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Branch</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentTransactions as $row)
                                        <tr>
                                            <td class="fw-medium"><a href="{{ $row['url'] }}">{{ $row['invoice'] }}</a></td>
                                            <td>{{ $row['branch'] }}</td>
                                            <td class="text-end">₹{{ number_format($row['amount'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Recent activity</h5></div>
                <div class="card-body">
                    @forelse ($recentActivity as $line)
                        <p class="mb-2 {{ $loop->last ? 'mb-0' : '' }}">{{ $line }}</p>
                    @empty
                        <p class="text-muted mb-0">No recent activity.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Essential ops (after reference layout) --}}
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Waiting at the showroom</h5>
                    <a href="{{ route('admin.transfers.index') }}" class="btn btn-sm btn-light">Transfers</a>
                </div>
                <div class="card-body">
                    @if ($draftTransferCount > 0)
                        <p class="text-muted small mb-3">{{ $draftTransferCount }} draft {{ $draftTransferCount === 1 ? 'transfer' : 'transfers' }} also open.</p>
                    @endif
                    @if ($waitingTransfers->isEmpty())
                        <p class="text-muted mb-0">No dispatched transfer is waiting to be received.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Destination</th>
                                        <th>Dispatched</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($waitingTransfers as $transfer)
                                        <tr>
                                            <td class="fw-medium"><a href="{{ route('admin.transfers.edit', $transfer) }}">{{ $transfer->code }}</a></td>
                                            <td>{{ $transfer->destinationName() }}</td>
                                            <td>{{ $transfer->dispatched_at?->format('d M Y, h:i A') ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Open stock requests</h5>
                    <a href="{{ route('admin.stock-requests.index') }}" class="btn btn-sm btn-light">Requests</a>
                </div>
                <div class="card-body">
                    @if ($openRequests->isEmpty())
                        <p class="text-muted mb-0">No shop is waiting for a transfer.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Requested By</th>
                                        <th>Requested</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($openRequests as $stockRequest)
                                        <tr>
                                            <td class="fw-medium"><a href="{{ route('admin.stock-requests.show', $stockRequest) }}">{{ $stockRequest->code }}</a></td>
                                            <td>{{ $stockRequest->holderName() }}</td>
                                            <td>{{ $stockRequest->created_at?->format('d M Y, h:i A') }}</td>
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

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Open custom requests</h5>
                    <a href="{{ route('admin.custom-requests.index') }}" class="btn btn-sm btn-light">Custom Requests</a>
                </div>
                <div class="card-body">
                    @if ($openCustomRequests->isEmpty())
                        <p class="text-muted mb-0">No custom request is waiting for review.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Agent</th>
                                        <th>Model</th>
                                        <th>Requested</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($openCustomRequests as $customRequest)
                                        <tr>
                                            <td class="fw-medium"><a href="{{ route('admin.custom-requests.show', $customRequest) }}">{{ $customRequest->code }}</a></td>
                                            <td>{{ $customRequest->agent?->name ?? '—' }}</td>
                                            <td>{{ $customRequest->displayName() }}</td>
                                            <td>{{ $customRequest->created_at?->format('d M Y, h:i A') }}</td>
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

@extends('layouts.app')
@section('title', 'Process Sale Return')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Process Sale Return / Refund</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('returns.index') }}" class="text-decoration-none">Returns</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

{{-- Step 1: Select Sale if not provided --}}
@if(!$sale)
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-receipt me-2 text-primary"></i>Step 1: Select Transaction to Return</h6>
                </div>
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('returns.create') }}">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Choose Recent Sale</label>
                            <select name="sale_id" class="form-select" required>
                                <option value="">-- Select Completed Sale Invoice --</option>
                                @foreach($sales as $s)
                                    <option value="{{ $s->id }}">
                                        {{ $s->invoice_number }} ({{ $s->created_at->format('d/m/Y H:i') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            Continue &rarr;
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@else
    {{-- Step 2: Select Items to return --}}
    <form action="{{ route('returns.store') }}" method="POST">
        @csrf
        <input type="hidden" name="sale_id" value="{{ $sale->id }}">

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-boxes me-2 text-warning"></i>Select Items to Return</h6>
                        <span class="badge bg-light text-dark border">Invoice: {{ $sale->invoice_number }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" style="font-size: .88rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Item</th>
                                    <th class="text-end">Sold Price</th>
                                    <th class="text-center">Sold Qty</th>
                                    <th style="width: 150px;">Return Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->saleItems as $index => $item)
                                    <tr>
                                        <td>
                                            <input type="hidden" name="items[{{ $index }}][sale_item_id]" value="{{ $item->id }}">
                                            <div class="fw-semibold text-dark">{{ $item->product_name }}</div>
                                            <div class="text-muted small">SKU: {{ $item->product_sku }}</div>
                                        </td>
                                        <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td>
                                            <input type="number"
                                                   name="items[{{ $index }}][quantity]"
                                                   class="form-control form-control-sm"
                                                   value="0"
                                                   min="0"
                                                   max="{{ $item->quantity }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-hand-holding-dollar me-2 text-primary"></i>Refund Options</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Refund Method <span class="text-danger">*</span></label>
                            <select name="refund_method" class="form-select" required>
                                <option value="cash">Cash (Tunai)</option>
                                <option value="store_credit">Store Credit / Saldo Pelanggan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Reason for Return</label>
                            <textarea name="reason" rows="2" class="form-control" placeholder="e.g. Expired, damaged packaging, wrong item" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Additional Notes</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="Optional notes..."></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning py-2 fw-semibold text-dark">
                                <i class="fas fa-check-circle me-1"></i> Process Refund & Restock
                            </button>
                            <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary py-2">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endif
@endsection

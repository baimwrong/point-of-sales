@extends('layouts.app')
@section('title', 'New Purchase Order')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Create Purchase Order</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}" class="text-decoration-none">Purchases</a></li>
                <li class="breadcrumb-item active" aria-current="page">New Order</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

@include('components.alert')

<form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
    @csrf
    <div class="row g-4">
        {{-- Left: Supplier & Items --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-truck me-2 text-primary"></i>Supplier Information</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Select Supplier</label>
                            <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                                <option value="">-- Direct / Walk-in Supplier --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id', request('supplier_id')) == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }} {{ $supplier->company ? '('.$supplier->company.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Order Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending (Draft / Not yet received)</option>
                                <option value="received" {{ old('status', 'received') === 'received' ? 'selected' : '' }}>Received (Directly add items to stock)</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Products Table --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-box-open me-2 text-primary"></i>Purchase Items</h6>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addItemRow()">
                        <i class="fas fa-plus me-1"></i> Add Row
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 250px;">Product <span class="text-danger">*</span></th>
                                    <th style="width: 130px;">Quantity <span class="text-danger">*</span></th>
                                    <th style="width: 170px;">Unit Cost (Rp) <span class="text-danger">*</span></th>
                                    <th style="width: 160px;" class="text-end">Subtotal (Rp)</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                {{-- Initial row --}}
                                <tr class="item-row">
                                    <td>
                                        <select name="items[0][product_id]" class="form-select product-select" required onchange="onProductSelect(this)">
                                            <option value="">-- Choose Product --</option>
                                            @foreach($products as $prod)
                                                <option value="{{ $prod->id }}" data-cost="{{ $prod->cost_price }}" data-unit="{{ $prod->unit?->abbreviation ?? 'pcs' }}">
                                                    {{ $prod->name }} (SKU: {{ $prod->sku }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" class="form-control qty-input" value="1" min="1" required oninput="recalculateRow(this)">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][unit_cost]" class="form-control cost-input" value="0" min="0" required oninput="recalculateRow(this)">
                                    </td>
                                    <td class="text-end fw-bold row-total">0</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Notes & Total --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-coins me-2 text-primary"></i>Payment & Summary</h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between fs-5 fw-bold mb-3">
                        <span>Total Cost</span>
                        <span class="text-primary" id="grandTotal">Rp 0</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount Paid (Rp)</label>
                        <input type="number" step="0.01" name="paid_amount" id="paidAmount" class="form-control" value="0" min="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes / Purchase Remarks</label>
                        <textarea name="notes" rows="3" class="form-control" placeholder="Invoice reference from vendor, delivery notes, etc.">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary py-2 fw-semibold">
                            <i class="fas fa-save me-1"></i> Save Purchase Order
                        </button>
                        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary py-2">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Hidden template for new rows --}}
<template id="rowTemplate">
    <tr class="item-row">
        <td>
            <select name="items[INDEX][product_id]" class="form-select product-select" required onchange="onProductSelect(this)">
                <option value="">-- Choose Product --</option>
                @foreach($products as $prod)
                    <option value="{{ $prod->id }}" data-cost="{{ $prod->cost_price }}" data-unit="{{ $prod->unit?->abbreviation ?? 'pcs' }}">
                        {{ $prod->name }} (SKU: {{ $prod->sku }})
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="items[INDEX][quantity]" class="form-control qty-input" value="1" min="1" required oninput="recalculateRow(this)">
        </td>
        <td>
            <input type="number" step="0.01" name="items[INDEX][unit_cost]" class="form-control cost-input" value="0" min="0" required oninput="recalculateRow(this)">
        </td>
        <td class="text-end fw-bold row-total">0</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>
@endsection

@section('scripts')
<script>
    let rowIndex = 1;

    function addItemRow() {
        const template = document.getElementById('rowTemplate').innerHTML;
        const html = template.replace(/INDEX/g, rowIndex);
        document.getElementById('itemsBody').insertAdjacentHTML('beforeend', html);
        rowIndex++;
    }

    function removeRow(btn) {
        const row = btn.closest('tr');
        if (document.querySelectorAll('.item-row').length > 1) {
            row.remove();
            recalculateGrandTotal();
        } else {
            alert('At least one item row is required.');
        }
    }

    function onProductSelect(select) {
        const option = select.options[select.selectedIndex];
        const cost = option.getAttribute('data-cost') || 0;
        const row = select.closest('tr');
        const costInput = row.querySelector('.cost-input');
        costInput.value = parseFloat(cost);
        recalculateRow(select);
    }

    function recalculateRow(element) {
        const row = element.closest('tr');
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
        const total = qty * cost;
        row.querySelector('.row-total').innerText = new Intl.NumberFormat('id-ID').format(total);
        recalculateGrandTotal();
    }

    function recalculateGrandTotal() {
        let grand = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
            grand += (qty * cost);
        });
        document.getElementById('grandTotal').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(grand);
    }
</script>
@endsection

@extends('layouts.app')
@section('title', 'System Settings')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">System &amp; Store Configuration</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Settings</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 mb-4">
        {{-- Navigation Tabs --}}
        <div class="card shadow-sm border-0">
            <div class="list-group list-group-flush rounded-3" id="settingsTab" role="tablist">
                <button class="list-group-item list-group-item-action active py-3 d-flex align-items-center gap-3 border-0" id="store-tab" data-bs-toggle="list" data-bs-target="#store-pane" type="button" role="tab">
                    <i class="fas fa-store text-primary"></i>
                    <div>
                        <div class="fw-semibold">Store Information</div>
                        <div class="text-muted small">Name, contact, address</div>
                    </div>
                </button>
                <button class="list-group-item list-group-item-action py-3 d-flex align-items-center gap-3 border-0" id="tax-tab" data-bs-toggle="list" data-bs-target="#tax-pane" type="button" role="tab">
                    <i class="fas fa-coins text-success"></i>
                    <div>
                        <div class="fw-semibold">Tax &amp; Currency</div>
                        <div class="text-muted small">Tax rate, currency symbol</div>
                    </div>
                </button>
                <button class="list-group-item list-group-item-action py-3 d-flex align-items-center gap-3 border-0" id="receipt-tab" data-bs-toggle="list" data-bs-target="#receipt-pane" type="button" role="tab">
                    <i class="fas fa-receipt text-warning"></i>
                    <div>
                        <div class="fw-semibold">Receipt &amp; Invoicing</div>
                        <div class="text-muted small">Prefixes, thermal receipt footer</div>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <div class="tab-content" id="settingsTabContent">
            {{-- Tab 1: Store Information --}}
            <div class="tab-pane fade show active" id="store-pane" role="tabpanel" aria-labelledby="store-tab">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-store me-2 text-primary"></i>Store Identity</h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('settings.updateStore') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Store / Business Name <span class="text-danger">*</span></label>
                                <input type="text" name="store_name" class="form-control" value="{{ old('store_name', $settings['store_name'] ?? 'My POS Store') }}" required>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone / WhatsApp</label>
                                    <input type="text" name="store_phone" class="form-control" value="{{ old('store_phone', $settings['store_phone'] ?? '') }}" placeholder="021-12345678">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Business Email</label>
                                    <input type="email" name="store_email" class="form-control" value="{{ old('store_email', $settings['store_email'] ?? '') }}" placeholder="store@example.com">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Store Address (printed on receipt)</label>
                                <textarea name="store_address" rows="3" class="form-control" placeholder="Jl. Example No. 1, Kota...">{{ old('store_address', $settings['store_address'] ?? '') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Store Info
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Tab 2: Tax & Currency --}}
            <div class="tab-pane fade" id="tax-pane" role="tabpanel" aria-labelledby="tax-tab">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-coins me-2 text-success"></i>Tax &amp; Currency Settings</h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('settings.updateTax') }}" method="POST">
                            @csrf
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tax Name <span class="text-danger">*</span></label>
                                    <input type="text" name="tax_name" class="form-control" value="{{ old('tax_name', $settings['tax_name'] ?? 'PPN') }}" placeholder="e.g. PPN, VAT" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tax Rate (%) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" name="tax_rate" class="form-control" value="{{ old('tax_rate', $settings['tax_rate'] ?? 11) }}" required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="form-text small text-muted">Applied by default in POS terminal checkout.</div>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Currency Symbol <span class="text-danger">*</span></label>
                                    <input type="text" name="currency_symbol" class="form-control" value="{{ old('currency_symbol', $settings['currency_symbol'] ?? 'Rp') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Currency Code <span class="text-danger">*</span></label>
                                    <input type="text" name="currency_code" class="form-control" value="{{ old('currency_code', $settings['currency_code'] ?? 'IDR') }}" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Save Tax &amp; Currency
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Tab 3: Receipt & Numbering --}}
            <div class="tab-pane fade" id="receipt-pane" role="tabpanel" aria-labelledby="receipt-tab">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-receipt me-2 text-warning"></i>Receipt &amp; Document Numbering</h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('settings.updateReceipt') }}" method="POST">
                            @csrf
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Sales Invoice Prefix <span class="text-danger">*</span></label>
                                    <input type="text" name="invoice_prefix" class="form-control" value="{{ old('invoice_prefix', $settings['invoice_prefix'] ?? 'INV') }}" required>
                                    <div class="form-text small text-muted">e.g. INV-20261010-0001</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Receipt Number Prefix <span class="text-danger">*</span></label>
                                    <input type="text" name="receipt_prefix" class="form-control" value="{{ old('receipt_prefix', $settings['receipt_prefix'] ?? 'RCT') }}" required>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Purchase Order Prefix <span class="text-danger">*</span></label>
                                    <input type="text" name="purchase_prefix" class="form-control" value="{{ old('purchase_prefix', $settings['purchase_prefix'] ?? 'PO') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Return Voucher Prefix <span class="text-danger">*</span></label>
                                    <input type="text" name="return_prefix" class="form-control" value="{{ old('return_prefix', $settings['return_prefix'] ?? 'RTN') }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Customer Loyalty Point Rate</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" name="loyalty_rate" class="form-control" value="{{ old('loyalty_rate', $settings['loyalty_rate'] ?? 1) }}" min="0">
                                    <span class="input-group-text">pt per Rp 1.000 spent</span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Thermal Receipt Footer Message</label>
                                <textarea name="receipt_footer" rows="2" class="form-control" placeholder="Thank you for shopping with us!">{{ old('receipt_footer', $settings['receipt_footer'] ?? 'Terima kasih telah berbelanja!') }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-warning text-dark">
                                <i class="fas fa-save me-1"></i> Save Receipt Config
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

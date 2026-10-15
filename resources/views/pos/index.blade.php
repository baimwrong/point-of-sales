@extends('layouts.app')
@section('title', 'POS Terminal')

@section('styles')
<style>
.pos-layout { display: flex; gap: 0; height: calc(100vh - 64px); margin: -28px -28px -16px; overflow: hidden; }
.pos-left { flex: 1; display: flex; flex-direction: column; border-right: 1px solid #e2e8f0; background: #f8fafc; overflow: hidden; }
.pos-right { width: 400px; flex-shrink: 0; display: flex; flex-direction: column; background: #fff; overflow: hidden; }
.pos-search-bar { padding: 14px 16px; background: #fff; border-bottom: 1px solid #e2e8f0; }
.pos-cat-tabs { display: flex; gap: 6px; padding: 10px 16px; background: #fff; border-bottom: 1px solid #e2e8f0; overflow-x: auto; flex-shrink: 0; scrollbar-width: none; }
.pos-cat-tabs::-webkit-scrollbar { display: none; }
.cat-tab { padding: 5px 14px; border-radius: 20px; border: 1.5px solid #e2e8f0; background: #fff; color: #64748b; font-size: .8rem; font-weight: 500; cursor: pointer; white-space: nowrap; transition: all .15s; }
.cat-tab.active, .cat-tab:hover { background: #2563eb; color: #fff; border-color: #2563eb; }
.product-grid { flex: 1; overflow-y: auto; padding: 12px; display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px; align-content: start; }
.product-card { background: #fff; border-radius: 12px; border: 2px solid #e2e8f0; cursor: pointer; transition: all .15s; overflow: hidden; position: relative; }
.product-card:hover { border-color: #2563eb; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37,99,235,.12); }
.product-card.in-cart { border-color: #10b981; background: #f0fdf4; }
.product-card.out-of-stock { opacity: .5; cursor: not-allowed; }
.product-card img { width: 100%; height: 90px; object-fit: cover; }
.product-card-body { padding: 8px 9px 10px; }
.product-card-name { font-size: .78rem; font-weight: 600; color: #1e293b; line-height: 1.3; margin-bottom: 2px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.product-card-price { font-size: .82rem; font-weight: 700; color: #2563eb; }
.product-stock-badge { position: absolute; top: 6px; right: 6px; font-size: .65rem; padding: 2px 6px; border-radius: 10px; font-weight: 700; }
.cart-header { padding: 14px 16px; border-bottom: 1px solid #e2e8f0; background: #fff; flex-shrink: 0; }
.cart-customer { padding: 10px 14px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; flex-shrink: 0; }
.cart-items { flex: 1; overflow-y: auto; }
.cart-item { display: flex; align-items: flex-start; gap: 8px; padding: 10px 14px; border-bottom: 1px solid #f1f5f9; }
.cart-item-name { font-size: .82rem; font-weight: 600; color: #1e293b; line-height: 1.3; }
.cart-item-price { font-size: .78rem; color: #64748b; }
.qty-controls { display: flex; align-items: center; gap: 4px; }
.qty-btn { width: 26px; height: 26px; border-radius: 6px; border: 1.5px solid #e2e8f0; background: #fff; font-size: .9rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .15s; }
.qty-btn:hover { background: #2563eb; color: #fff; border-color: #2563eb; }
.qty-input { width: 40px; text-align: center; border: 1.5px solid #e2e8f0; border-radius: 6px; font-size: .82rem; padding: 2px 0; }
.cart-summary { border-top: 1px solid #e2e8f0; padding: 12px 14px; background: #fff; flex-shrink: 0; }
.summary-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; font-size: .84rem; }
.summary-total { font-size: 1.1rem; font-weight: 700; color: #1e293b; }
.payment-section { padding: 12px 14px; border-top: 1px solid #e2e8f0; background: #fff; flex-shrink: 0; }
.payment-btn { flex: 1; padding: 7px 4px; border-radius: 8px; border: 2px solid #e2e8f0; background: #fff; font-size: .75rem; font-weight: 600; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 3px; transition: all .15s; }
.payment-btn.active { border-color: #2563eb; background: #eff6ff; color: #2563eb; }
.payment-btn i { font-size: 1rem; }
.btn-process { padding: 13px; font-size: 1rem; font-weight: 700; border-radius: 12px; border: none; background: linear-gradient(135deg, #10b981, #059669); color: #fff; cursor: pointer; transition: all .2s; }
.btn-process:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(16,185,129,.35); }
.btn-process:disabled { opacity: .6; cursor: not-allowed; transform: none; }
.btn-clear { padding: 10px; font-size: .85rem; font-weight: 600; border-radius: 10px; border: 2px solid #fecaca; background: #fff; color: #ef4444; cursor: pointer; transition: all .15s; }
.btn-clear:hover { background: #fef2f2; }
</style>
@endsection

@section('content')
<div class="pos-layout" x-data="posTerminal()" x-init="init()">

    {{-- ========== LEFT: Product Grid ========== --}}
    <div class="pos-left">

        {{-- Search Bar --}}
        <div class="pos-search-bar">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-barcode text-muted"></i>
                </span>
                <input type="text"
                       class="form-control border-start-0 ps-1"
                       placeholder="Search by name, SKU or scan barcode..."
                       x-model="searchQuery"
                       @input.debounce.300ms="filterProducts()"
                       @keydown.enter.prevent="handleBarcodeScan()"
                       x-ref="searchInput"
                       style="font-size:.9rem">
                <button class="btn btn-outline-secondary" @click="searchQuery=''; filterProducts()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- Category Tabs --}}
        <div class="pos-cat-tabs">
            <button class="cat-tab" :class="{ active: activeCategory === 'all' }" @click="filterByCategory('all')">
                All
            </button>
            @foreach($categories as $cat)
                <button class="cat-tab" :class="{ active: activeCategory === {{ $cat->id }} }" @click="filterByCategory({{ $cat->id }})">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>

        {{-- Product Grid --}}
        <div class="product-grid">
            <template x-for="product in filteredProducts" :key="product.id">
                <div class="product-card"
                     :class="{ 'in-cart': isInCart(product.id), 'out-of-stock': product.track_stock && product.stock_quantity <= 0 }"
                     @click="addToCart(product)">

                    <img :src="product.image_url" :alt="product.name"
                         onerror="this.src='/images/placeholder-product.png'"
                         style="display:block">

                    <span class="product-stock-badge"
                          :class="product.stock_quantity <= 0 ? 'bg-danger text-white' : (product.stock_quantity <= 5 ? 'bg-warning text-dark' : 'bg-light text-muted')">
                        <span x-text="product.track_stock ? product.stock_quantity : '∞'"></span>
                    </span>

                    <div class="product-card-body">
                        <div class="product-card-name" x-text="product.name"></div>
                        <div class="product-card-price">
                            Rp <span x-text="formatNum(product.effective_price)"></span>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="filteredProducts.length === 0">
                <div class="col-span-all text-center text-muted py-5" style="grid-column:1/-1">
                    <i class="fas fa-search fa-3x mb-3 opacity-25 d-block"></i>
                    <div>No products found</div>
                </div>
            </template>
        </div>
    </div>

    {{-- ========== RIGHT: Cart ========== --}}
    <div class="pos-right">

        {{-- Cart Header --}}
        <div class="cart-header d-flex align-items-center justify-content-between">
            <div>
                <div class="fw-bold" style="font-size:.95rem;color:#1e293b">
                    <i class="fas fa-shopping-cart text-primary me-2"></i>Order
                </div>
                <div class="text-muted" style="font-size:.78rem" x-text="cart.length + ' item(s)'"></div>
            </div>
            <button class="btn-clear" @click="clearCart()" x-show="cart.length > 0">
                <i class="fas fa-trash-alt me-1"></i>Clear
            </button>
        </div>

        {{-- Customer --}}
        <div class="cart-customer">
            <div class="d-flex align-items-center gap-2">
                <div style="width:32px;height:32px;background:#eff6ff;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fas fa-user text-primary" style="font-size:.8rem"></i>
                </div>
                <div class="flex-fill min-w-0">
                    <div class="fw-semibold text-truncate" style="font-size:.82rem" x-text="customerName"></div>
                    <div class="text-muted" style="font-size:.73rem">
                        <span x-show="customerId">Loyalty: <span x-text="loyaltyPoints"></span> pts</span>
                        <span x-show="!customerId">Guest customer</span>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-primary" @click="openCustomerModal()" style="font-size:.75rem;padding:3px 10px">
                    <i class="fas fa-search me-1"></i>Select
                </button>
                <button class="btn btn-sm btn-outline-secondary" @click="clearCustomer()" x-show="customerId" style="font-size:.75rem;padding:3px 8px">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- Cart Items --}}
        <div class="cart-items">
            <template x-if="cart.length === 0">
                <div class="text-center text-muted py-5">
                    <i class="fas fa-shopping-cart fa-3x mb-3 d-block opacity-15"></i>
                    <div style="font-size:.9rem">Cart is empty</div>
                    <div style="font-size:.8rem">Click a product to add it</div>
                </div>
            </template>

            <template x-for="(item, index) in cart" :key="item.id">
                <div class="cart-item">
                    <div class="flex-fill min-w-0">
                        <div class="cart-item-name" x-text="item.name"></div>
                        <div class="cart-item-price">
                            Rp <span x-text="formatNum(item.price)"></span> &times; <span x-text="item.qty"></span>
                            <span x-show="item.discount > 0" class="text-danger ms-1">
                                (-Rp <span x-text="formatNum(item.discount * item.qty)"></span>)
                            </span>
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="fw-bold text-primary" style="font-size:.85rem">
                            Rp <span x-text="formatNum(item.lineTotal)"></span>
                        </div>
                        <div class="qty-controls mt-1">
                            <button class="qty-btn" @click.stop="decrementQty(index)">−</button>
                            <input class="qty-input" type="number" :value="item.qty"
                                   @change.stop="setQty(index, $event.target.value)"
                                   min="1" :max="item.track_stock ? item.stock : 9999">
                            <button class="qty-btn" @click.stop="incrementQty(index)">+</button>
                            <button class="qty-btn ms-1" @click.stop="removeFromCart(index)" style="border-color:#fecaca;color:#ef4444">
                                <i class="fas fa-times" style="font-size:.65rem"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Order Summary --}}
        <div class="cart-summary">
            <div class="summary-row">
                <span class="text-muted">Subtotal</span>
                <span>Rp <span x-text="formatNum(subtotal)"></span></span>
            </div>

            {{-- Discount --}}
            <div class="summary-row align-items-start">
                <span class="text-muted">Discount</span>
                <div class="d-flex gap-1 align-items-center">
                    <select class="form-select form-select-sm" x-model="discountType" style="width:80px;font-size:.75rem">
                        <option value="none">None</option>
                        <option value="fixed">Fixed</option>
                        <option value="percent">%</option>
                    </select>
                    <input x-show="discountType !== 'none'"
                           type="number" class="form-control form-control-sm" x-model.number="discountValue"
                           style="width:70px;font-size:.75rem" min="0"
                           :placeholder="discountType === 'percent' ? '%' : 'Rp'">
                    <span class="text-danger" x-show="discountAmount > 0">-Rp <span x-text="formatNum(discountAmount)"></span></span>
                </div>
            </div>

            <div class="summary-row">
                <span class="text-muted">Tax ({{ $taxRate }}%)</span>
                <span>Rp <span x-text="formatNum(taxAmount)"></span></span>
            </div>

            <hr class="my-2">
            <div class="summary-row summary-total">
                <span>TOTAL</span>
                <span class="text-primary">Rp <span x-text="formatNum(total)"></span></span>
            </div>
        </div>

        {{-- Payment --}}
        <div class="payment-section">
            <div class="d-flex gap-2 mb-3">
                <button class="payment-btn" :class="{ active: paymentMethod === 'cash' }"    @click="setPayment('cash')">
                    <i class="fas fa-money-bill-wave"></i><span>Cash</span>
                </button>
                <button class="payment-btn" :class="{ active: paymentMethod === 'card' }"    @click="setPayment('card')">
                    <i class="fas fa-credit-card"></i><span>Card</span>
                </button>
                <button class="payment-btn" :class="{ active: paymentMethod === 'transfer' }"@click="setPayment('transfer')">
                    <i class="fas fa-building-columns"></i><span>Transfer</span>
                </button>
                <button class="payment-btn" :class="{ active: paymentMethod === 'qris' }"   @click="setPayment('qris')">
                    <i class="fas fa-qrcode"></i><span>QRIS</span>
                </button>
            </div>

            <div x-show="paymentMethod === 'cash'" class="mb-3">
                <label class="form-label mb-1" style="font-size:.8rem;font-weight:600">Amount Tendered</label>
                <div class="input-group">
                    <span class="input-group-text" style="font-size:.85rem">Rp</span>
                    <input type="number" class="form-control" x-model.number="amountTendered"
                           :min="total" step="1000" style="font-size:.9rem">
                </div>
                    <template x-for="amt in quickCashOptions()" :key="amt">
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="amountTendered = amt"
                                style="font-size:.72rem;padding:3px 8px" x-text="'Rp ' + formatNum(amt)">
                        </button>
                    </template>
                    <button type="button" class="btn btn-sm btn-outline-primary" @click="amountTendered = total" style="font-size:.72rem;padding:3px 8px">Exact</button>
                </div>
                <div class="mt-2 p-2 rounded" style="background:#f0fdf4" x-show="changeAmount >= 0 && amountTendered > 0">
                    <div class="d-flex justify-content-between fw-semibold">
                        <span class="text-muted" style="font-size:.82rem">Change</span>
                        <span class="text-success" style="font-size:.95rem">Rp <span x-text="formatNum(changeAmount)"></span></span>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <input type="text" class="form-control form-control-sm" x-model="notes" placeholder="Order notes (optional)" style="font-size:.82rem">
            </div>

            <div class="d-flex gap-2">
                <button class="btn-process flex-fill" @click="processTransaction()"
                        :disabled="cart.length === 0 || processing || (paymentMethod === 'cash' && amountTendered < total)">
                    <span x-show="!processing">
                        <i class="fas fa-check-circle me-1"></i>Process Sale
                    </span>
                    <span x-show="processing">
                        <i class="fas fa-spinner fa-spin me-1"></i>Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========== Customer Modal ========== --}}
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2 px-3">
                <h6 class="modal-title mb-0" id="customerModalLabel">Select Customer</h6>
                <button type="button" class="btn-close btn-sm" @click="closeCustomerModal()" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <input type="text" class="form-control form-control-sm mb-2"
                       id="customerSearchInput"
                       placeholder="Search name or phone..."
                       x-model="customerSearch"
                       @input.debounce.300ms="searchCustomers()"
                       style="font-size:.85rem">
                <div style="max-height:300px;overflow-y:auto">
                    <template x-for="c in customerResults" :key="c.id">
                        <div class="d-flex align-items-center gap-2 p-2 rounded cursor-pointer"
                             style="cursor:pointer"
                             @click="selectCustomer(c)"
                             :class="customerId === c.id ? 'bg-primary text-white' : 'hover-bg'">
                            <div>
                                <div class="fw-semibold" style="font-size:.84rem" x-text="c.name"></div>
                                <div style="font-size:.75rem;opacity:.7" x-text="c.phone || c.email || '-'"></div>
                            </div>
                        </div>
                    </template>
                    <div x-show="customerResults.length === 0 && customerSearch.length > 0" class="text-center text-muted py-3" style="font-size:.82rem">
                        No customers found
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========== Receipt Modal ========== --}}
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-success text-white py-2 px-3">
                <h6 class="modal-title mb-0"><i class="fas fa-check-circle me-2"></i>Sale Completed!</h6>
            </div>
            <div class="modal-body p-3" x-show="lastReceipt">
                <div class="text-center mb-3">
                    <div class="fw-bold text-success" style="font-size:1.1rem" x-text="lastReceipt?.invoice_number"></div>
                    <div class="text-muted" style="font-size:.82rem">Invoice Number</div>
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size:.85rem">
                    <span class="text-muted">Total</span>
                    <span class="fw-bold">Rp <span x-text="lastReceipt ? formatNum(lastReceipt.total_amount) : 0"></span></span>
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size:.85rem" x-show="paymentMethod === 'cash'">
                    <span class="text-muted">Change</span>
                    <span class="fw-bold text-success">Rp <span x-text="lastReceipt ? formatNum(lastReceipt.change_amount) : 0"></span></span>
                </div>
            </div>
            <div class="modal-footer py-2 px-3 gap-2">
                <a :href="lastReceipt ? '{{ url('pos/receipt') }}/' + lastReceipt.sale_id : '#'"
                   target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-print me-1"></i>Print
                </a>
                <button class="btn btn-sm btn-primary" @click="startNewSale()">
                    <i class="fas fa-plus me-1"></i>New Sale
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function posTerminal() {
    return {
        // State
        allProducts: [],
        filteredProducts: [],
        categories: [],
        activeCategory: 'all',
        searchQuery: '',
        cart: [],
        customerId: null,
        customerName: 'Walk-in Customer',
        loyaltyPoints: 0,
        discountType: 'none',
        discountValue: 0,
        paymentMethod: 'cash',
        amountTendered: 0,
        notes: '',
        processing: false,
        taxRate: {{ $taxRate }},
        customerSearch: '',
        customerResults: [],
        lastReceipt: null,
        receiptModal: null,
        customerModal: null,

        // Computed
        get subtotal() {
            return this.cart.reduce((sum, item) => sum + item.lineTotal, 0);
        },
        get discountAmount() {
            if (this.discountType === 'fixed')   return Math.min(this.discountValue, this.subtotal);
            if (this.discountType === 'percent') return this.subtotal * (this.discountValue / 100);
            return 0;
        },
        get taxAmount() {
            return Math.round((this.subtotal - this.discountAmount) * (this.taxRate / 100));
        },
        get total() {
            return Math.round(this.subtotal - this.discountAmount + this.taxAmount);
        },
        get changeAmount() {
            return Math.max(0, this.amountTendered - this.total);
        },

        // Init
        async init() {
            await this.loadProducts();
            this.receiptModal  = new bootstrap.Modal(document.getElementById('receiptModal'));
            this.customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
            this.$nextTick(() => this.$refs.searchInput?.focus());
        },

        async loadProducts() {
            try {
                const r = await fetch('{{ route("pos.search") }}?q=');
                this.allProducts = await r.json();
                this.filteredProducts = [...this.allProducts];
            } catch(e) { console.error('Failed to load products', e); }
        },

        filterProducts() {
            const q = this.searchQuery.toLowerCase().trim();
            this.filteredProducts = this.allProducts.filter(p => {
                const matchCat = this.activeCategory === 'all' || p.category_id == this.activeCategory;
                const matchQ   = !q || p.name.toLowerCase().includes(q) || (p.sku||'').toLowerCase().includes(q) || (p.barcode||'').includes(q);
                return matchCat && matchQ;
            });
        },

        filterByCategory(catId) {
            this.activeCategory = catId;
            this.filterProducts();
        },

        handleBarcodeScan() {
            const q = this.searchQuery.trim();
            if (!q) return;
            const match = this.allProducts.find(p => p.barcode === q || p.sku === q);
            if (match) {
                this.addToCart(match);
                this.searchQuery = '';
                this.filteredProducts = [...this.allProducts];
            }
        },

        isInCart(productId) {
            return this.cart.some(i => i.id === productId);
        },

        addToCart(product) {
            if (product.track_stock && product.stock_quantity <= 0) {
                Swal.fire({ icon: 'warning', title: 'Out of Stock', text: `"${product.name}" is out of stock.`, timer: 2000, showConfirmButton: false });
                return;
            }
            const existing = this.cart.find(i => i.id === product.id);
            if (existing) {
                if (product.track_stock && existing.qty >= product.stock_quantity) {
                    Swal.fire({ icon: 'warning', title: 'Max Stock Reached', text: `Only ${product.stock_quantity} units available.`, timer: 2000, showConfirmButton: false });
                    return;
                }
                existing.qty++;
                this.recalcItem(existing);
            } else {
                const item = {
                    id: product.id, name: product.name, sku: product.sku, barcode: product.barcode,
                    price: product.effective_price, qty: 1, stock: product.stock_quantity,
                    track_stock: product.track_stock, discount: 0, discount_type: 'none', discount_value: 0,
                    lineTotal: product.effective_price,
                };
                this.cart.push(item);
            }
        },

        recalcItem(item) {
            const raw = item.price * item.qty;
            let disc = 0;
            if (item.discount_type === 'fixed')   disc = item.discount_value * item.qty;
            if (item.discount_type === 'percent') disc = raw * (item.discount_value / 100);
            item.discount   = disc / item.qty;
            item.lineTotal  = Math.max(0, raw - disc);
        },

        removeFromCart(index) { this.cart.splice(index, 1); },

        incrementQty(index) {
            const item = this.cart[index];
            if (item.track_stock && item.qty >= item.stock) return;
            item.qty++;
            this.recalcItem(item);
        },
        decrementQty(index) {
            const item = this.cart[index];
            if (item.qty <= 1) { this.removeFromCart(index); return; }
            item.qty--;
            this.recalcItem(item);
        },
        setQty(index, val) {
            const item = this.cart[index];
            const qty  = parseInt(val) || 1;
            item.qty = Math.min(Math.max(1, qty), item.track_stock ? item.stock : 9999);
            this.recalcItem(item);
        },

        clearCart() {
            if (!this.cart.length) return;
            Swal.fire({
                title: 'Clear Cart?',
                text: 'All items will be removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, clear it'
            }).then(r => { if (r.isConfirmed) { this.cart = []; this.discountType = 'none'; this.discountValue = 0; } });
        },

        setPayment(method) {
            this.paymentMethod = method;
            if (method !== 'cash') this.amountTendered = this.total;
        },

        quickCashOptions() {
            const t = Math.ceil(this.total);
            if (t <= 0) return [];
            const options = new Set();
            const denoms = [10000, 20000, 50000, 100000];
            denoms.forEach(d => {
                if (d >= t) options.add(d);
                else {
                    const nextRound = Math.ceil(t / d) * d;
                    if (nextRound > t) options.add(nextRound);
                }
            });
            options.add(Math.ceil(t / 50000) * 50000);
            options.add(Math.ceil(t / 100000) * 100000);
            return Array.from(options).filter(v => v >= t).sort((a,b) => a - b).slice(0, 4);
        },

        openCustomerModal() {
            this.customerModal.show();
            this.$nextTick(() => document.getElementById('customerSearchInput')?.focus());
        },

        closeCustomerModal() {
            this.customerModal.hide();
        },

        async searchCustomers() {
            if (this.customerSearch.length < 2) { this.customerResults = []; return; }
            try {
                const r = await fetch('{{ route("customers.search") }}?q=' + encodeURIComponent(this.customerSearch));
                this.customerResults = await r.json();
            } catch(e) {}
        },

        selectCustomer(c) {
            this.customerId    = c.id;
            this.customerName  = c.name;
            this.loyaltyPoints = c.loyalty_points || 0;
            this.customerSearch    = '';
            this.customerResults   = [];
            this.customerModal.hide();
        },

        clearCustomer() {
            this.customerId = null; this.customerName = 'Walk-in Customer'; this.loyaltyPoints = 0;
        },

        async processTransaction() {
            if (!this.cart.length || this.processing) return;
            if (this.paymentMethod === 'cash' && this.amountTendered < this.total) {
                Swal.fire({ icon: 'warning', title: 'Insufficient Amount', text: 'Tendered amount must be ≥ total.' });
                return;
            }
            this.processing = true;
            try {
                const payload = {
                    items: this.cart.map(i => ({
                        id: i.id, qty: i.qty, price: i.price,
                        discount_type: i.discount_type, discount_value: i.discount_value,
                    })),
                    customer_id:    this.customerId,
                    payment_method: this.paymentMethod,
                    paid_amount:    this.paymentMethod === 'cash' ? this.amountTendered : this.total,
                    discount_type:  this.discountType,
                    discount_value: this.discountValue,
                    notes:          this.notes,
                };

                const r = await fetch('{{ route("pos.transaction") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                });

                const data = await r.json();
                if (data.success) {
                    this.lastReceipt = data;
                    this.cart = [];
                    this.discountType = 'none'; this.discountValue = 0;
                    this.amountTendered = 0; this.notes = '';
                    this.clearCustomer();
                    this.receiptModal.show();
                    await this.loadProducts(); // Refresh stock
                } else {
                    Swal.fire({ icon: 'error', title: 'Transaction Failed', text: data.message || 'Transaction could not be completed.' });
                }
            } catch(e) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'An unexpected error occurred. Please try again.' });
            } finally {
                this.processing = false;
            }
        },

        startNewSale() {
            this.receiptModal.hide();
            this.lastReceipt = null;
            this.$nextTick(() => this.$refs.searchInput?.focus());
        },

        formatNum(val) {
            return new Intl.NumberFormat('id-ID').format(Math.round(val || 0));
        },
    };
}
</script>
@endsection

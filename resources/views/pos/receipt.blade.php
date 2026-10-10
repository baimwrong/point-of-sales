<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->invoice_number }} &mdash; {{ $settings['store_name'] ?? 'POS' }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #f1f5f9;
            color: #000;
            padding: 20px 0;
            font-size: 13px;
            line-height: 1.35;
        }
        .receipt-container {
            width: 80mm;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 16px 14px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            border-radius: 4px;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 12px;
        }
        .store-title {
            font-size: 17px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .receipt-divider {
            border-top: 1px dashed #475569;
            margin: 8px 0;
        }
        .double-divider {
            border-top: 1px double #000;
            margin: 8px 0;
        }
        .receipt-table {
            width: 100%;
            margin-bottom: 6px;
        }
        .receipt-table td, .receipt-table th {
            padding: 2px 0;
            vertical-align: top;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .action-bar {
            width: 80mm;
            margin: 0 auto 15px;
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .action-bar {
                display: none !important;
            }
            .receipt-container {
                box-shadow: none;
                padding: 10px 8px;
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <button onclick="window.print()" class="btn btn-primary btn-sm">
        <i class="fas fa-print me-1"></i> Print Receipt
    </button>
    <a href="{{ route('pos.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back to POS
    </a>
</div>

<div class="receipt-container">
    <div class="receipt-header">
        <div class="store-title">{{ $settings['store_name'] ?? 'POS STORE' }}</div>
        <div>{{ $settings['store_address'] ?? 'Point of Sales Store' }}</div>
        @if(!empty($settings['store_phone']))
            <div>Telp: {{ $settings['store_phone'] }}</div>
        @endif
    </div>

    <div class="receipt-divider"></div>

    <table style="width: 100%; font-size: 12px;">
        <tr>
            <td>No. Transaksi</td>
            <td class="text-end"><strong>{{ $sale->invoice_number }}</strong></td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td class="text-end">{{ $sale->created_at->format('d/m/Y H:i:s') }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td class="text-end">{{ $sale->user?->name ?? 'Staff' }}</td>
        </tr>
        <tr>
            <td>Pelanggan</td>
            <td class="text-end">{{ $sale->customer?->name ?? 'Walk-in Customer' }}</td>
        </tr>
    </table>

    <div class="receipt-divider"></div>

    <table class="receipt-table" style="font-size: 12px;">
        <thead>
            <tr>
                <th style="width: 50%;">Item</th>
                <th class="text-center" style="width: 20%;">Qty</th>
                <th class="text-end" style="width: 30%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $item)
                <tr>
                    <td colspan="3" style="padding-top: 4px; font-weight: 600;">
                        {{ $item->product_name }}
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 11px; color: #555;">
                        @ {{ number_format($item->unit_price, 0, ',', '.') }}
                        @if($item->discount_amount > 0)
                            <span style="color: #c00;">(-{{ number_format($item->discount_amount, 0, ',', '.') }})</span>
                        @endif
                    </td>
                    <td class="text-center" style="font-size: 11px;">
                        {{ $item->quantity }}
                    </td>
                    <td class="text-end" style="font-size: 12px;">
                        {{ number_format($item->total_price, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="receipt-divider"></div>

    <table style="width: 100%; font-size: 12px;">
        <tr>
            <td>Subtotal</td>
            <td class="text-end">{{ $settings['currency_symbol'] ?? 'Rp' }} {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($sale->discount_amount > 0)
            <tr>
                <td>Diskon ({{ $sale->discount_type === 'percent' ? 'Persen' : 'Nominal' }})</td>
                <td class="text-end">-{{ $settings['currency_symbol'] ?? 'Rp' }} {{ number_format($sale->discount_amount, 0, ',', '.') }}</td>
            </tr>
        @endif
        @if($sale->tax_amount > 0)
            <tr>
                <td>Pajak ({{ $sale->tax_rate }}%)</td>
                <td class="text-end">{{ $settings['currency_symbol'] ?? 'Rp' }} {{ number_format($sale->tax_amount, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr style="font-size: 14px; font-weight: bold;">
            <td style="padding-top: 4px;">TOTAL</td>
            <td class="text-end" style="padding-top: 4px;">{{ $settings['currency_symbol'] ?? 'Rp' }} {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
        </tr>
        <tr style="border-top: 1px dashed #aaa;">
            <td style="padding-top: 4px;">Metode Pembayaran</td>
            <td class="text-end" style="padding-top: 4px; text-transform: uppercase;"><strong>{{ $sale->payment_method }}</strong></td>
        </tr>
        <tr>
            <td>Bayar / Tunai</td>
            <td class="text-end">{{ $settings['currency_symbol'] ?? 'Rp' }} {{ number_format($sale->paid_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td class="text-end"><strong>{{ $settings['currency_symbol'] ?? 'Rp' }} {{ number_format($sale->change_amount, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    @if(!empty($sale->notes))
        <div class="receipt-divider"></div>
        <div style="font-size: 11px;">
            <strong>Catatan:</strong> {{ $sale->notes }}
        </div>
    @endif

    <div class="double-divider"></div>

    <div class="text-center" style="font-size: 11px; margin-top: 10px;">
        <div>{{ $settings['receipt_footer'] ?? 'Terima kasih atas kunjungan Anda!' }}</div>
        <div style="margin-top: 4px; color: #666;">--- Simpan struk ini sebagai bukti pembayaran ---</div>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', () => {
        // Automatically prompt print dialog on load if desired
        // window.print();
    });
</script>
</body>
</html>

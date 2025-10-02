<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supply Inventory Report</title>
    <style>
        @page {
            margin: 1cm 1.5cm;
            size: A4;
        }
        
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }

        .logo-section {
            margin-bottom: 10px;
        }

        .org-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 3px;
        }

        .org-address {
            font-size: 9px;
            color: #666;
            margin-bottom: 8px;
        }

        .report-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 8px 0;
            color: #1e40af;
        }

        .report-subtitle {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }

        .meta-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            font-size: 9px;
        }

        .meta-left, .meta-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .meta-right {
            text-align: right;
        }

        .summary-section {
            margin-bottom: 20px;
        }

        .summary-title {
            font-size: 12px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 3px;
        }

        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .summary-row {
            display: table-row;
        }

        .summary-cell {
            display: table-cell;
            width: 25%;
            padding: 8px;
            border: 1px solid #e5e7eb;
            text-align: center;
            background-color: #f8fafc;
        }

        .summary-label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .summary-value {
            font-size: 12px;
            font-weight: bold;
            color: #1e40af;
        }

        .table-section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .detailed-section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 12px;
            text-transform: uppercase;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 5px;
            background-color: #f8fafc;
            padding: 8px;
            border-radius: 4px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-bottom: 15px;
        }

        .data-table th {
            background-color: #1e40af;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 4px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-mono {
            font-family: 'DejaVu Sans Mono', Courier, monospace;
        }

        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-active {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background-color: #f3f4f6;
            color: #374151;
        }

        .low-stock {
            color: #dc2626;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            font-size: 8px;
            color: #666;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 5px;
            background-color: white;
        }

        .page-break {
            page-break-before: always;
        }

        .no-break {
            page-break-inside: avoid;
        }

        .certification {
            margin-top: 30px;
            border: 1px solid #2563eb;
            padding: 15px;
            background-color: #f8fafc;
        }

        .cert-title {
            font-size: 10px;
            font-weight: bold;
            color: #1e40af;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .signature-section {
            display: table;
            width: 100%;
            margin-top: 20px;
        }

        .signature-block {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 0 10px;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            height: 30px;
            margin-bottom: 5px;
        }

        .signature-label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-section">
            <div class="org-name">Quezon City Public Library</div>
            <div class="org-address">
                Quezon City Hall Complex, Elliptical Road, Diliman, Quezon City<br>
                Tel: (02) 8988-4242 | Email: info@qcpubliclibrary.gov.ph
            </div>
        </div>
        
        <div class="report-title">Supply Inventory Report</div>
        <div class="report-subtitle">Consumable Supplies & Materials Inventory Status</div>
    </div>

    <div class="meta-info">
        <div class="meta-left">
            <strong>Report Generated:</strong> {{ $generated_at->format('F j, Y g:i A') }}<br>
            <strong>Generated By:</strong> {{ $user->name }} ({{ ucfirst(str_replace('_', ' ', $user->role)) }})<br>
            <strong>Branch/Department:</strong> {{ $branch->name ?? 'Main Branch' }}<br>
            <strong>Filters Applied:</strong> Category: {{ $filters['category'] }}, Status: {{ $filters['status'] }}
        </div>
        <div class="meta-right">
            <strong>Report Type:</strong> Supply Inventory Summary<br>
            <strong>Period:</strong> As of {{ $generated_at->format('F j, Y') }}<br>
            <strong>Total Records:</strong> {{ number_format($summary['total_items']) }}<br>
            <strong>COA Compliant:</strong> Yes
        </div>
    </div>

    <div class="summary-section no-break">
        <div class="summary-title">Executive Summary</div>
        
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell">
                    <div class="summary-label">Total Items</div>
                    <div class="summary-value">{{ number_format($summary['total_items']) }}</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-label">Total Units</div>
                    <div class="summary-value">{{ number_format($summary['on_hand_units']) }}</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-label">Total Value</div>
                    <div class="summary-value">₱{{ number_format($summary['on_hand_value'], 2) }}</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-label">Low Stock Items</div>
                    <div class="summary-value">{{ number_format($summary['low_stock_items']) }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($summary['by_category']->count() > 0)
    <div class="table-section no-break">
        <div class="section-title">Summary by Category</div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Category Name</th>
                    <th style="width: 15%;" class="text-center">Items</th>
                    <th style="width: 20%;" class="text-right">Total Units</th>
                    <th style="width: 25%;" class="text-right">Total Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary['by_category'] as $category)
                <tr>
                    <td>{{ $category->category->name ?? 'Uncategorized' }}</td>
                    <td class="text-center">{{ number_format($category->count) }}</td>
                    <td class="text-right">{{ number_format($category->total_stock) }}</td>
                    <td class="text-right">₱{{ number_format($category->total_value, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="page-break"></div>

    <div class="table-section">
        <div class="detailed-section-title">
            📋 Detailed Supply Inventory Listing
            <span style="font-size: 10px; font-weight: normal; float: right;">
                Total Items: {{ number_format($supplies->count()) }}
            </span>
        </div>
        
        @if($supplies->count() > 0)
        <div style="margin-bottom: 8px; font-size: 9px; color: #666;">
            <strong>Note:</strong> Items marked in <span style="color: #dc2626; font-weight: bold;">red</span> are below minimum stock levels and require attention.
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 10%;">Supply #</th>
                    <th style="width: 32%;">Description</th>
                    <th style="width: 13%;">Category</th>
                    <th style="width: 8%;" class="text-right">On Hand</th>
                    <th style="width: 7%;" class="text-right">Min Stock</th>
                    <th style="width: 10%;" class="text-right">Unit Cost</th>
                    <th style="width: 12%;" class="text-right">Total Value</th>
                    <th style="width: 8%;" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @php $totalValue = 0; @endphp
                @foreach($supplies as $index => $supply)
                @php $itemValue = $supply->current_stock * $supply->unit_cost; $totalValue += $itemValue; @endphp
                <tr style="{{ $index % 2 === 0 ? 'background-color: #f8fafc;' : '' }}">
                    <td class="font-mono">{{ $supply->supply_number }}</td>
                    <td>
                        {{ $supply->description }}
                        @if($supply->sku)
                        <br><small style="color: #666; font-size: 7px;">SKU: {{ $supply->sku }}</small>
                        @endif
                    </td>
                    <td>{{ $supply->category->name ?? 'Uncategorized' }}</td>
                    <td class="text-right {{ $supply->current_stock < $supply->min_stock ? 'low-stock' : '' }}">
                        {{ number_format($supply->current_stock) }}
                        @if($supply->current_stock < $supply->min_stock)
                        <br><small style="color: #dc2626; font-size: 6px;">⚠️ LOW</small>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($supply->min_stock) }}</td>
                    <td class="text-right">₱{{ number_format($supply->unit_cost, 2) }}</td>
                    <td class="text-right">₱{{ number_format($itemValue, 2) }}</td>
                    <td class="text-center">
                        <span class="status-badge status-{{ $supply->status }}">
                            {{ strtoupper($supply->status) }}
                        </span>
                    </td>
                </tr>
                @endforeach
                
                <!-- Total Row -->
                <tr style="border-top: 2px solid #2563eb; background-color: #e0f2fe; font-weight: bold;">
                    <td colspan="6" class="text-right" style="padding: 8px;">
                        <strong>GRAND TOTAL ({{ number_format($supplies->count()) }} items):</strong>
                    </td>
                    <td class="text-right" style="padding: 8px;">
                        <strong>₱{{ number_format($totalValue, 2) }}</strong>
                    </td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        @else
        <div style="text-align: center; padding: 20px; color: #666; font-style: italic;">
            No supply items found matching the selected criteria.
        </div>
        @endif
    </div>

    <div class="certification no-break">
        <div class="cert-title">Certification</div>
        <p style="font-size: 9px; margin-bottom: 10px;">
            I hereby certify that this Supply Inventory Report is accurate and complete as per the records 
            maintained by the Quezon City Public Library. This report has been prepared in accordance with 
            Commission on Audit (COA) guidelines and Government Accounting Manual requirements.
        </p>
        
        <div class="signature-section">
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">Prepared By<br>{{ $user->name }}</div>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">Reviewed By<br>Supply Officer</div>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">Approved By<br>Branch Head</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div>
            Supply Inventory Report | Generated on {{ $generated_at->format('F j, Y g:i A') }} | 
            Quezon City Public Library | Page <span class="pagenum"></span>
        </div>
    </div>
</body>
</html>
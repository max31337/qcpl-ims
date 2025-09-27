<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Assets Report</title>
  <style>
    @page { margin: 30px 40px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
    header { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
    header img { height: 36px; }
    h1 { font-size: 16px; margin: 0; }
    .sub { color: #6b7280; font-size: 11px; }
    .meta { margin: 8px 0 12px; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #d1d5db; padding: 6px; }
    th { background: #f3f4f6; text-align: left; }
    tfoot td { border: none; font-size: 10px; color: #6b7280; }
    .center { text-align: center; }
  </style>
</head>
<body>
  <header>
    @php $logo = public_path('Quezon_City_Public_Library_logo.png'); @endphp
    @if(file_exists($logo))
      <img src="{{ $logo }}" alt="QCPL Logo" />
    @endif
    <div>
      <h1>Quezon City Public Library</h1>
      <div class="sub">Assets Report</div>
    </div>
  </header>

  <div class="meta">
    <div><strong>Date Range:</strong> {{ $from }} to {{ $to }}</div>
    @if(!empty($filters))
      <div><strong>Filters:</strong>
        @php
          $f = [];
          if($filters['category']) $f[] = 'Category ID: '.$filters['category'];
          if($filters['status']) $f[] = 'Status: '.$filters['status'];
          if($filters['branch']) $f[] = 'Branch ID: '.$filters['branch'];
          if($filters['division']) $f[] = 'Division ID: '.$filters['division'];
          if($filters['section']) $f[] = 'Section ID: '.$filters['section'];
        @endphp
        {{ $f ? implode('; ', $f) : 'None' }}
      </div>
    @endif
    <div><strong>Generated:</strong> {{ now()->format('Y-m-d H:i') }}</div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Property #</th>
        <th>Description</th>
        <th>Category</th>
        <th class="center">Qty</th>
        <th class="center">Unit Cost</th>
        <th class="center">Total Cost</th>
        <th>Status</th>
        <th>Branch</th>
        <th>Division</th>
        <th>Section</th>
        <th>Date Acquired</th>
      </tr>
    </thead>
    <tbody>
      @forelse($assets as $a)
      <tr>
        <td>{{ $a->property_number }}</td>
        <td>{{ $a->description }}</td>
        <td>{{ optional($a->category)->name }}</td>
        <td class="center">{{ $a->quantity }}</td>
        <td class="center">{{ number_format((float)$a->unit_cost, 2) }}</td>
        <td class="center">{{ number_format((float)$a->total_cost, 2) }}</td>
        <td>{{ $a->status }}</td>
        <td>{{ optional($a->currentBranch)->name }}</td>
        <td>{{ optional($a->currentDivision)->name }}</td>
        <td>{{ optional($a->currentSection)->name }}</td>
        <td>{{ optional($a->date_acquired)?->format('Y-m-d') }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="11" class="center">{{ $noDataMessage ?? 'No data available for this report.' }}</td>
      </tr>
      @endforelse
    </tbody>
  </table>

  <footer>
    <table style="width:100%; margin-top:10px;">
      <tr>
        <td class="sub">This report is system-generated and intended for administrative and COA audit purposes.</td>
        <td class="sub" style="text-align:right;">Page <span class="pagenum"></span></td>
      </tr>
    </table>
  </footer>
  <script type="text/php">
    if (isset($pdf)) {
      $x = 540; $y = 820;
      $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
      $font = $fontMetrics->get_font("DejaVu Sans", "normal");
      $size = 9; $color = [0,0,0];
      $pdf->page_text($x, $y, $text, $font, $size, $color);
    }
  </script>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Assets Report</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h1 { font-size: 18px; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 6px; }
    th { background: #f3f4f6; text-align: left; }
    .meta { margin-bottom: 10px; }
  </style>
</head>
<body>
  <h1>Assets Report</h1>
  <div class="meta">
    <div>Date Range: {{ $from }} to {{ $to }}</div>
    <div>Generated: {{ now()->format('Y-m-d H:i') }}</div>
  </div>
  <table>
    <thead>
      <tr>
        <th>Property #</th>
        <th>Description</th>
        <th>Category</th>
        <th>Qty</th>
        <th>Unit Cost</th>
        <th>Total Cost</th>
        <th>Status</th>
        <th>Branch</th>
        <th>Division</th>
        <th>Section</th>
        <th>Date Acquired</th>
      </tr>
    </thead>
    <tbody>
      @foreach($assets as $a)
      <tr>
        <td>{{ $a->property_number }}</td>
        <td>{{ $a->description }}</td>
        <td>{{ optional($a->category)->name }}</td>
        <td>{{ $a->quantity }}</td>
        <td>{{ number_format((float)$a->unit_cost, 2) }}</td>
        <td>{{ number_format((float)$a->total_cost, 2) }}</td>
        <td>{{ $a->status }}</td>
        <td>{{ optional($a->currentBranch)->name }}</td>
        <td>{{ optional($a->currentDivision)->name }}</td>
        <td>{{ optional($a->currentSection)->name }}</td>
        <td>{{ optional($a->date_acquired)?->format('Y-m-d') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>

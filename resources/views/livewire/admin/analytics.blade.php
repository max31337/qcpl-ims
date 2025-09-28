<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Admin Analytics</h1>
      <p class="text-muted-foreground">Deep dives into assets, supplies, and transfers</p>
    </div>
  </div>

  <x-ui.card class="p-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
      <div>
        <x-ui.label>View Period</x-ui.label>
        <select class="w-full border rounded h-9 px-2" wire:model.live="period">
          <option value="alltime">All Time</option>
          <option value="monthly">Monthly View</option>
          <option value="yearly">Yearly View</option>
        </select>
      </div>
      <div x-show="$wire.period !== 'alltime'">
        <x-ui.label>Year</x-ui.label>
        <select class="w-full border rounded h-9 px-2" wire:model.live="selectedYear">
          @for($year = now()->year; $year >= now()->year - 5; $year--)
            <option value="{{ $year }}">{{ $year }}</option>
          @endfor
        </select>
      </div>
    </div>
  </x-ui.card>

  {{-- KPI summary --}}
  <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
    <x-ui.card class="p-4"><p class="text-xs text-muted-foreground">Assets</p><p class="text-2xl font-semibold">{{ number_format($kpis['assetsTotal'] ?? 0) }}</p></x-ui.card>
    <x-ui.card class="p-4"><p class="text-xs text-muted-foreground">Asset value</p><p class="text-2xl font-semibold">₱{{ number_format($kpis['assetsValue'] ?? 0,2) }}</p></x-ui.card>
    <x-ui.card class="p-4"><p class="text-xs text-muted-foreground">Supply SKUs</p><p class="text-2xl font-semibold">{{ number_format($kpis['suppliesSkus'] ?? 0) }}</p></x-ui.card>
    <x-ui.card class="p-4"><p class="text-xs text-muted-foreground">Supplies value</p><p class="text-2xl font-semibold">₱{{ number_format($kpis['suppliesValue'] ?? 0,2) }}</p></x-ui.card>
    <x-ui.card class="p-4"><p class="text-xs text-muted-foreground">Transfers</p><p class="text-2xl font-semibold">{{ number_format($kpis['transfersInRange'] ?? 0) }}</p></x-ui.card>
  </div>

  {{-- Time series trio --}}
  <x-ui.card class="p-4">
    <h2 class="text-lg font-semibold mb-4">Monthly activity (last 12 months)</h2>
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      {{-- Assets line --}}
      <div>
        <h3 class="font-medium mb-2">Assets created</h3>
        @php
          $vals = $assetsMonthly ?? []; $labels = $labels ?? []; $maxV = max($vals ?: [1]);
          $w=560;$h=180;$pl=30;$pr=10;$pt=10;$pb=24; $iW=$w-$pl-$pr;$iH=$h-$pt-$pb;
          $n=max(count($vals)-1,1); $pts=[]; foreach($vals as $i=>$v){ $x=$pl+($n?$iW*($i/$n):0); $y=$pt+($maxV?$iH*(1-($v/$maxV)):$iH); $pts[]=$x.','.$y; }
        @endphp
        <div class="overflow-x-auto">
          <svg viewBox="0 0 {{ $w }} {{ $h }}" class="w-full max-w-full">
            <rect x="0" y="0" width="{{ $w }}" height="{{ $h }}" fill="transparent" />
            @for($i=0;$i<=4;$i++) @php $gy=$pt+$iH*($i/4); @endphp <line x1="{{ $pl }}" y1="{{ $gy }}" x2="{{ $w-$pr }}" y2="{{ $gy }}" stroke="#e5e7eb" stroke-width="1" /> @endfor
            @if(count($pts)>=2)<polyline fill="none" stroke="#0ea5e9" stroke-width="2" points="{{ implode(' ', $pts) }}" />@endif
            @php $tickEvery=max(intval(ceil(count($labels)/6)),1); @endphp
            @foreach($labels as $i=>$lab) @if($i%$tickEvery===0) @php $x=$pl+($n?$iW*($i/$n):0); @endphp <text x="{{ $x }}" y="{{ $h-6 }}" font-size="10" text-anchor="middle" fill="#6b7280">{{ $lab }}</text> @endif @endforeach
          </svg>
        </div>
      </div>

      {{-- Supplies bars --}}
      <div>
        <h3 class="font-medium mb-2">Supplies added</h3>
        @php
          $sVals=$suppliesMonthly ?? []; $sLabels=$labels ?? []; $sMax=max($sVals ?: [1]);
          $bw=560;$bh=180;$pl=30;$pr=10;$pt=10;$pb=24;$iW=$bw-$pl-$pr;$iH=$bh-$pt-$pb;$count=max(count($sVals),1);$step=$iW/$count;$barW=max($step*0.6,4);
        @endphp
        <div class="overflow-x-auto">
          <svg viewBox="0 0 {{ $bw }} {{ $bh }}" class="w-full max-w-full">
            <rect x="0" y="0" width="{{ $bw }}" height="{{ $bh }}" fill="transparent" />
            @for($i=0;$i<=4;$i++) @php $gy=$pt+$iH*($i/4); @endphp <line x1="{{ $pl }}" y1="{{ $gy }}" x2="{{ $bw-$pr }}" y2="{{ $gy }}" stroke="#e5e7eb" stroke-width="1" /> @endfor
            @foreach($sVals as $i=>$v) @php $x=$pl+$step*$i+($step-$barW)/2; $h=$sMax?$iH*($v/$sMax):0; $y=$pt+($iH-$h); @endphp <rect x="{{ $x }}" y="{{ $y }}" width="{{ $barW }}" height="{{ $h }}" fill="#6366f1" rx="2" /> @endforeach
            @php $tickEvery=max(intval(ceil(count($sLabels)/6)),1); @endphp
            @foreach($sLabels as $i=>$lab) @if($i%$tickEvery===0) @php $tx=$pl+$step*$i+($step/2); @endphp <text x="{{ $tx }}" y="{{ $bh-6 }}" font-size="10" text-anchor="middle" fill="#6b7280">{{ $lab }}</text> @endif @endforeach
          </svg>
        </div>
      </div>

      {{-- Transfers bars --}}
      <div>
        <h3 class="font-medium mb-2">Transfers</h3>
        @php
          $tVals=$transfersMonthly ?? []; $tLabels=$labels ?? []; $tMax=max($tVals ?: [1]);
          $bw=560;$bh=180;$pl=30;$pr=10;$pt=10;$pb=24;$iW=$bw-$pl-$pr;$iH=$bh-$pt-$pb;$count=max(count($tVals),1);$step=$iW/$count;$barW=max($step*0.6,4);
        @endphp
        <div class="overflow-x-auto">
          <svg viewBox="0 0 {{ $bw }} {{ $bh }}" class="w-full max-w-full">
            <rect x="0" y="0" width="{{ $bw }}" height="{{ $bh }}" fill="transparent" />
            @for($i=0;$i<=4;$i++) @php $gy=$pt+$iH*($i/4); @endphp <line x1="{{ $pl }}" y1="{{ $gy }}" x2="{{ $bw-$pr }}" y2="{{ $gy }}" stroke="#e5e7eb" stroke-width="1" /> @endfor
            @foreach($tVals as $i=>$v) @php $x=$pl+$step*$i+($step-$barW)/2; $h=$tMax?$iH*($v/$tMax):0; $y=$pt+($iH-$h); @endphp <rect x="{{ $x }}" y="{{ $y }}" width="{{ $barW }}" height="{{ $h }}" fill="#10b981" rx="2" /> @endforeach
            @php $tickEvery=max(intval(ceil(count($tLabels)/6)),1); @endphp
            @foreach($tLabels as $i=>$lab) @if($i%$tickEvery===0) @php $tx=$pl+$step*$i+($step/2); @endphp <text x="{{ $tx }}" y="{{ $bh-6 }}" font-size="10" text-anchor="middle" fill="#6b7280">{{ $lab }}</text> @endif @endforeach
          </svg>
        </div>
      </div>
    </div>
  </x-ui.card>

  {{-- Distributions & rankings --}}
  <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <x-ui.card class="p-4">
      <h3 class="font-medium mb-2">Assets by status</h3>
      @php
        $map=['active'=>'#16a34a','condemn'=>'#f59e0b','disposed'=>'#ef4444'];
        $sum=max(array_sum($assetsByStatus?->toArray() ?? []),1); $acc=0; $segments=[];
        foreach(($assetsByStatus ?? []) as $st=>$cnt){ $pct=$cnt/$sum*100; $segments[] = ($map[$st]??'#16a34a').' '.$acc.'% '.($acc+$pct).'%'; $acc+=$pct; }
        $bg=$segments? 'conic-gradient('.implode(', ',$segments).')' : '#e5e7eb';
      @endphp
      <div class="flex items-center gap-4">
        <div class="relative h-36 w-36 rounded-full" style="background: {{ $bg }}"><div class="absolute inset-4 bg-white rounded-full border"></div></div>
        <div class="text-sm space-y-1">
          <div><span class="inline-block h-2 w-2 rounded-full" style="background:#16a34a"></span> Active {{ $assetsByStatus['active'] ?? 0 }}</div>
          <div><span class="inline-block h-2 w-2 rounded-full" style="background:#f59e0b"></span> Condemn {{ $assetsByStatus['condemn'] ?? 0 }}</div>
          <div><span class="inline-block h-2 w-2 rounded-full" style="background:#ef4444"></span> Disposed {{ $assetsByStatus['disposed'] ?? 0 }}</div>
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-4">
      <h3 class="font-medium mb-2">Stock health</h3>
      @php
        $total=max(($stockOut??0)+($stockLow??0)+($stockOk??0),1);
        $pOut=($stockOut??0)/$total*100; $pLow=($stockLow??0)/$total*100; $pOk=($stockOk??0)/$total*100;
        $acc=0; $segs=[]; if($pOut>0){$segs[]='#ef4444 '.$acc.'% '.($acc+=$pOut).'%';} if($pLow>0){$segs[]='#f59e0b '.$acc.'% '.($acc+=$pLow).'%';} if($pOk>0){$segs[]='#16a34a '.$acc.'% '.($acc+=$pOk).'%';}
        $bg=$segs? 'conic-gradient('.implode(', ',$segs).')' : '#e5e7eb';
      @endphp
      <div class="flex items-center gap-4">
        <div class="relative h-36 w-36 rounded-full" style="background: {{ $bg }}"><div class="absolute inset-4 bg-white rounded-full border"></div></div>
        <div class="text-sm space-y-1">
          <div><span class="inline-block h-2 w-2 rounded-full" style="background:#ef4444"></span> Out {{ $stockOut ?? 0 }}</div>
          <div><span class="inline-block h-2 w-2 rounded-full" style="background:#f59e0b"></span> Low {{ $stockLow ?? 0 }}</div>
          <div><span class="inline-block h-2 w-2 rounded-full" style="background:#16a34a"></span> OK {{ $stockOk ?? 0 }}</div>
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-4">
      <h3 class="font-medium mb-2">Top branches by asset count</h3>
      <x-ui.table>
        <thead><tr><th class="px-3 py-2">Branch</th><th class="px-3 py-2 w-48">Bar</th><th class="px-3 py-2 w-20 text-right">Count</th></tr></thead>
        <tbody>
          @php $vals=$assetsCountByBranch?->pluck('c')->all() ?? []; $mx=max($vals ?: [1]); @endphp
          @forelse($assetsCountByBranch as $r)
            <tr class="border-t">
              <td class="px-3 py-2 text-sm">{{ $r->name }}</td>
              <td class="px-3 py-2 text-sm"><div class="h-2 w-full bg-muted rounded"><div class="h-2 bg-primary rounded" style="width: {{ $mx>0 ? (($r->c ?? 0)/$mx*100) : 0 }}%"></div></div></td>
              <td class="px-3 py-2 text-sm text-right">{{ number_format($r->c ?? 0) }}</td>
            </tr>
          @empty
            <tr><td colspan="3" class="px-3 py-4 text-sm text-muted-foreground">No data</td></tr>
          @endforelse
        </tbody>
      </x-ui.table>
    </x-ui.card>

    <x-ui.card class="p-4">
      <h3 class="font-medium mb-2">Top branches by asset value</h3>
      <x-ui.table>
        <thead><tr><th class="px-3 py-2">Branch</th><th class="px-3 py-2 w-48">Bar</th><th class="px-3 py-2 w-24 text-right">Value</th></tr></thead>
        <tbody>
          @php $vals=$assetsValueByBranch?->pluck('v')->all() ?? []; $mx=max($vals ?: [1]); @endphp
          @forelse($assetsValueByBranch as $r)
            <tr class="border-t">
              <td class="px-3 py-2 text-sm">{{ $r->name }}</td>
              <td class="px-3 py-2 text-sm"><div class="h-2 w-full bg-muted rounded"><div class="h-2 bg-primary rounded" style="width: {{ $mx>0 ? (($r->v ?? 0)/$mx*100) : 0 }}%"></div></div></td>
              <td class="px-3 py-2 text-sm text-right">₱{{ number_format($r->v ?? 0, 2) }}</td>
            </tr>
          @empty
            <tr><td colspan="3" class="px-3 py-4 text-sm text-muted-foreground">No data</td></tr>
          @endforelse
        </tbody>
      </x-ui.table>
    </x-ui.card>

    <x-ui.card class="p-4">
      <h3 class="font-medium mb-2">Assets by category (pie)</h3>
      @php
        $vals=$assetsValueByCategory?->pluck('v')->all() ?? []; $names=$assetsValueByCategory?->pluck('name')->all() ?? [];
        $sum=array_sum($vals); $acc=0; $segs=[]; $palette=['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#84cc16','#f97316','#14b8a6','#eab308'];
        foreach($vals as $i=>$v){ $pct=$sum?($v/$sum*100):0; if($pct<=0) continue; $color=$palette[$i % count($palette)]; $segs[]=$color.' '.$acc.'% '.($acc+=$pct).'%'; }
        $bg=$segs? 'conic-gradient('.implode(', ',$segs).')' : '#e5e7eb';
      @endphp
      <div class="flex items-center gap-4">
        <div class="relative h-36 w-36 rounded-full" style="background: {{ $bg }}"><div class="absolute inset-10 bg-white rounded-full border"></div></div>
        <div class="text-sm grid grid-cols-1 gap-1">
          @foreach($assetsValueByCategory as $i=>$r)
            @php $color=$palette[$i % count($palette)]; @endphp
            <div class="flex items-center gap-2"><span class="inline-block h-2 w-2 rounded-full" style="background: {{ $color }}"></span> {{ $r->name }} (₱{{ number_format($r->v ?? 0,2) }})</div>
          @endforeach
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-4 xl:col-span-2">
      <h3 class="font-medium mb-2">Asset value by category</h3>
      <x-ui.table>
        <thead><tr><th class="px-3 py-2">Category</th><th class="px-3 py-2 w-48">Bar</th><th class="px-3 py-2 w-24 text-right">Value</th></tr></thead>
        <tbody>
          @php $vals=$assetsValueByCategory?->pluck('v')->all() ?? []; $mx=max($vals ?: [1]); @endphp
          @forelse($assetsValueByCategory as $r)
            <tr class="border-t">
              <td class="px-3 py-2 text-sm">{{ $r->name }}</td>
              <td class="px-3 py-2 text-sm"><div class="h-2 w-full bg-muted rounded"><div class="h-2 bg-primary rounded" style="width: {{ $mx>0 ? (($r->v ?? 0)/$mx*100) : 0 }}%"></div></div></td>
              <td class="px-3 py-2 text-sm text-right">₱{{ number_format($r->v ?? 0, 2) }}</td>
            </tr>
          @empty
            <tr><td colspan="3" class="px-3 py-4 text-sm text-muted-foreground">No data</td></tr>
          @endforelse
        </tbody>
      </x-ui.table>
    </x-ui.card>

    <x-ui.card class="p-4 xl:col-span-2">
      <h3 class="font-medium mb-2">Top transfer routes</h3>
      <x-ui.table>
        <thead><tr><th class="px-3 py-2">From → To</th><th class="px-3 py-2 w-20 text-right">Count</th></tr></thead>
        <tbody>
          @forelse($topRoutes as $r)
            <tr class="border-t">
              <td class="px-3 py-2 text-sm">{{ $r->origin_name ?? ('Branch #'.$r->origin_branch_id) }} → {{ $r->current_name ?? ('Branch #'.$r->current_branch_id) }}</td>
              <td class="px-3 py-2 text-sm text-right">{{ $r->c }}</td>
            </tr>
          @empty
            <tr><td colspan="2" class="px-3 py-4 text-sm text-muted-foreground">No transfers</td></tr>
          @endforelse
        </tbody>
      </x-ui.table>
    </x-ui.card>
  </div>

  
</div>

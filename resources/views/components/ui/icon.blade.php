@props([
  'name' => 'circle',
  'class' => '',
  'size' => 'md',
  'strokeWidth' => 2,
])

@php
  $sizeClasses = [
    'xs' => 'h-3 w-3',
    'sm' => 'h-4 w-4', 
    'md' => 'h-5 w-5',
    'lg' => 'h-6 w-6',
    'xl' => 'h-8 w-8',
  ];
  
  $defaultSize = $sizeClasses[$size] ?? $sizeClasses['md'];
  $iconClass = $class ?: $defaultSize;
@endphp

@php
  // Minimal Lucide icon map used across the app; add more as needed
  $paths = [
  // ...existing code...
  'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
    'layout-dashboard' => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
    'boxes' => '<path d="M7.5 4.27 12 6.16l4.5-1.89"/><path d="M12 6.16v5.68"/><path d="M7.5 7.27 12 9.16l4.5-1.89"/><path d="M3 7v10l9 4 9-4V7"/><path d="M3 7 12 3l9 4"/>',
    'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m22 2-5 10-3-3-2 5"/>',
    'log-out' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/>',
    'log-in' => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10,17 15,12 10,7"/><line x1="15" y1="12" x2="3" y2="12"/>',
    'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
    'printer' => '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/><path d="M6 18h12"/>',
    'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
    'file-spreadsheet' => '<path d="M4 22h16"/><path d="M4 2h10l6 6v12a2 2 0 0 1-2 2H4z"/><path d="M14 2v6h6"/><path d="M8 13h2"/><path d="M14 13h2"/><path d="M8 17h2"/><path d="M14 17h2"/>',
    'check' => '<polyline points="20 6 9 17 4 12"/>',
    'x' => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
    'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    'user-plus' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>',
    'refresh-ccw' => '<path d="M3 2v6h6"/><path d="M21 12A9 9 0 1 1 6 5.3L3 8"/><path d="M21 22v-6h-6"/><path d="M3 12a9 9 0 0 0 15 6.7L21 16"/>',
    'trash-2' => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
    'user-check' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/>',
    'user-x' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m17 9 5 5"/><path d="m22 9-5 5"/>',
    'shield-check' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
    'shield-x' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m14.5 9.5-5 5"/><path d="m9.5 9.5 5 5"/>',
    'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
    'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
    'info' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
    'circle' => '<circle cx="12" cy="12" r="10"/>',
    // Added commonly used Lucide icons
    'package' => '<path d="M16.5 9.4 7.5 4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 2 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 22 16Z"/><path d="M3.27 6.96 12 12.01l8.73-5.05"/><path d="M12 22V12"/>',
    'plus' => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
    'arrow-up-down' => '<path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/>',
    'pencil' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4Z"/>',
    'arrow-left' => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
  'bar-chart' => '<path d="M3 3v18h18"/><rect x="7" y="13" width="3" height="5" rx="1"/><rect x="12" y="9" width="3" height="9" rx="1"/><rect x="17" y="5" width="3" height="13" rx="1"/>' ,
      'line-chart' => '<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>',
      'pie-chart' => '<path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/>',
      'building' => '<rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/>',
  'packages' => '<path d="m7.5 4.27 4.5 1.89 4.5-1.89M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
  'box' => '<path d="M3 7v10l9 4 9-4V7l-9-4-9 4z"/><path d="M12 12v9"/>',
  'credit-card' => '<rect x="1" y="4" width="22" height="14" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
  'dollar-sign' => '<path d="M12 1v2"/><path d="M12 21v2"/><path d="M17 5a4 4 0 0 0-8 0c0 2 2 3 4 3s4 1 4 3c0 3-3 4-4 4-2 0-4 1-4 3"/>',
      'arrow-right' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
      'layout' => '<rect width="18" height="7" x="3" y="3" rx="1"/><rect width="9" height="7" x="3" y="14" rx="1"/><rect width="5" height="7" x="16" y="14" rx="1"/>',
      // Additional for dashboard
      'activity' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
      // Transfer and movement icons
      'transfer' => '<path d="M21 6H3"/><path d="m5 12-2 2 2 2"/><path d="m3 14 2-2-2-2"/><path d="M21 18H3"/><path d="m19 12 2-2-2-2"/><path d="m21 10-2 2 2 2"/>',
  // alias used in analytics header
  'shuffle' => '<path d="M21 6H3"/><path d="m5 12-2 2 2 2"/><path d="m3 14 2-2-2-2"/><path d="M21 18H3"/><path d="m19 12 2-2-2-2"/><path d="m21 10-2 2 2 2"/>',
      'history' => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>',
      'smartphone' => '<rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/>',
      'tablet' => '<rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>',
      'monitor' => '<rect width="20" height="14" x="2" y="3" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>',
      'bot' => '<rect width="18" height="10" x="3" y="11" rx="2"/><circle cx="12" cy="5" r="2"/><path d="m19 13-2 3-2-3m4-3a4 4 0 0 0-8 0"/><path d="M8 21V10a4 4 0 1 1 8 0v11"/>',
      'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-10 5L2 7"/>',
      'calendar' => '<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>',
      'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>',
      'filter' => '<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>',
      'eye' => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
      'list' => '<line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/>',
      'grid-3x3' => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/><path d="M15 3v18"/><path d="M3 9h18"/><path d="M3 15h18"/>',
      'chevron-up' => '<path d="m18 15-6-6-6 6"/>',
      'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
      'file-text' => '<path d="M4 2h10l6 6v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v6h6"/><path d="M8 12h8"/><path d="M8 16h8"/><path d="M8 8h2"/>',
      'document' => '<path d="M4 2h10l6 6v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v6h6"/>',
      'clipboard' => '<rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>',
      'alert-triangle' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="m12 17.02.01 0"/>',
      'x-circle' => '<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>',
  ];
  $path = $paths[$name] ?? $paths['circle'];
@endphp

<svg {{ $attributes->merge(['class' => $iconClass]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $strokeWidth }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
  {!! $path !!}
  <title class="sr-only">{{ $name }}</title>
  </svg>

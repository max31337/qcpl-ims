# QCPL-IMS - Quezon City Public Library Inventory Management System

## Project Overview
A Laravel-based inventory management system using the TALL stack (Tailwind CSS, Alpine.js, Laravel, Livewire) for managing assets and supplies across all Quezon City Public Library branches.

## Technology Stack
- **Laravel 10+** (Backend Framework)
- **Livewire 3** (Frontend Components)
- **Alpine.js** (JavaScript Framework)
- **Tailwind CSS** (Styling)
- **MySQL** (Database)

## Project Structure Setup

### Initial Laravel Setup
```bash
composer create-project laravel/laravel qcpl-ims
cd qcpl-ims
composer require livewire/livewire
composer require laravel/breeze
php artisan breeze:install blade
npm install
npm install alpinejs
npm run dev
```

## User Roles & Permissions

### Role Hierarchy
1. **Admin** - Full system access (CRUD everything)
2. **Staff** - Add, update, delete, generate reports
3. **Supply Officer** - Manage supplies only (CRUD + reports)
4. **Property Officer** - Manage assets/properties only (CRUD + reports)
5. **Observer/MIS** - View-only access + user logs

### Branch Access Control
- **Main Branch** - Can see all branches
- **Other Branches** - Can only see their own data + transfers involving them
- Each branch has its own isolated data scope

## Database Schema Design

### Core Tables

#### users
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('firstname');
    $table->string('middlename')->nullable();
    $table->string('lastname');
    $table->string('username')->unique();
    $table->string('email')->unique();
    $table->string('employee_id')->unique();
    $table->enum('role', ['admin', 'staff', 'supply_officer', 'property_officer', 'observer']);
    $table->foreignId('branch_id')->constrained();
    $table->foreignId('division_id')->constrained();
    $table->foreignId('section_id')->constrained();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->boolean('is_active')->default(true);
    $table->rememberToken();
    $table->timestamps();
});
```

#### branches
```php
Schema::create('branches', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->string('district');
    $table->text('address');
    $table->boolean('is_main')->default(false);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### divisions
```php
Schema::create('divisions', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->foreignId('branch_id')->constrained();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### sections
```php
Schema::create('sections', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->foreignId('division_id')->constrained();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### categories
```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->enum('type', ['asset', 'supply']);
    $table->boolean('is_default')->default(false);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Seed default categories
// Assets: Furnitures, Books, IT Equipment, Office Equipment
// Supplies: Add some initial categories (recommended)
// Example seeder snippet:
Category::insert([
  ['name' => 'Office Supplies', 'type' => 'supply', 'is_default' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
  ['name' => 'Janitorial', 'type' => 'supply', 'is_default' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
  ['name' => 'Printing & Paper', 'type' => 'supply', 'is_default' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
  ['name' => 'Pantry', 'type' => 'supply', 'is_default' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
]);
// Note: The Supply creation form lists only categories where type = 'supply'. If nothing shows, seed or create supply categories first (Admin > Categories).
```

#### assets (Properties)
```php
Schema::create('assets', function (Blueprint $table) {
    $table->id();
  $table->string('property_number')->unique(); // e.g., 2025-0004
  // One physical item per row; shared attributes now live on asset_groups
  $table->foreignId('asset_group_id')->nullable()->constrained('asset_groups');
    
    // Current Location
    $table->foreignId('current_branch_id')->constrained('branches');
    $table->foreignId('current_division_id')->constrained('divisions');
    $table->foreignId('current_section_id')->constrained('sections');
    
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
});
```

#### asset_groups (Shared asset attributes)
Holds fields common to identical items so they can be grouped visually while keeping per-item rows and histories.
```php
Schema::create('asset_groups', function (Blueprint $table) {
  $table->id();
  $table->string('description');
  $table->foreignId('category_id')->constrained();
  $table->date('date_acquired');
  $table->decimal('unit_cost', 12, 2);
  $table->enum('status', ['active','condemn','disposed']);
  $table->enum('source', ['qc_property','donation']);
  $table->string('image_path')->nullable();
  $table->foreignId('created_by')->constrained('users');
  $table->timestamps();
});
```

#### supplies
```php
Schema::create('supplies', function (Blueprint $table) {
    $table->id();
    $table->string('supply_number')->unique(); // e.g., SUP-001
    $table->text('description');
    $table->foreignId('category_id')->constrained();
    $table->integer('current_stock');
    $table->integer('min_stock');
    $table->decimal('unit_cost', 12, 2);
    $table->enum('status', ['active', 'inactive']);
    $table->foreignId('branch_id')->constrained();
    $table->foreignId('created_by')->constrained('users');
    $table->timestamp('last_updated')->useCurrent();
    $table->timestamps();
});
```

// Recommended extension for stock auditability and balance checks
// Supply movements (Kardex) — enables running balance, valuation, and reconciliation
Schema::create('supply_movements', function (Blueprint $table) {
  $table->id();
  $table->foreignId('supply_id')->constrained();
  $table->foreignId('branch_id')->constrained();
  $table->enum('type', ['receive','issue','adjust_in','adjust_out','transfer_in','transfer_out']);
  $table->integer('quantity'); // positive values only; use type to indicate direction
  $table->decimal('unit_cost', 12, 2)->nullable(); // cost for receive/transfer_in (used for moving average)
  $table->string('reference_no')->nullable();
  $table->text('remarks')->nullable();
  $table->foreignId('created_by')->constrained('users');
  $table->timestamp('created_at');
  $table->index(['supply_id','branch_id','created_at']);
});

#### asset_transfer_histories
```php
Schema::create('asset_transfer_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('asset_id')->constrained();
    $table->date('transfer_date');
    
    // Origin
    $table->foreignId('origin_branch_id')->constrained('branches');
    $table->foreignId('origin_division_id')->constrained('divisions');
    $table->foreignId('origin_section_id')->constrained('sections');
    
    // Previous Location
    $table->foreignId('previous_branch_id')->nullable()->constrained('branches');
    $table->foreignId('previous_division_id')->nullable()->constrained('divisions');
    $table->foreignId('previous_section_id')->nullable()->constrained('sections');
    
    // Current Location
    $table->foreignId('current_branch_id')->constrained('branches');
    $table->foreignId('current_division_id')->constrained('divisions');
    $table->foreignId('current_section_id')->constrained('sections');
    
    $table->text('remarks')->nullable();
    $table->foreignId('transferred_by')->constrained('users');
    $table->timestamps();
});
```

#### activity_logs
```php
Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->string('action'); // create, update, delete, transfer, etc.
    $table->string('model'); // Asset, Supply, User, etc.
    $table->unsignedBigInteger('model_id');
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->text('description');
    $table->timestamp('created_at');
});
```

## Models & Relationships

### User Model
```php
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'employee_id', 'role', 'branch_id', 
        'division_id', 'section_id', 'password', 'is_active'
    ];

    public function branch() { return $this->belongsTo(Branch::class); }
    public function division() { return $this->belongsTo(Division::class); }
    public function section() { return $this->belongsTo(Section::class); }
    
    // Role checking methods
    public function isAdmin() { return $this->role === 'admin'; }
    public function isStaff() { return $this->role === 'staff'; }
    public function isSupplyOfficer() { return $this->role === 'supply_officer'; }
    public function isPropertyOfficer() { return $this->role === 'property_officer'; }
    public function isObserver() { return $this->role === 'observer'; }
    public function isMainBranch() { return $this->branch->is_main; }
}
```

### Asset Model
```php
class Asset extends Model
{
  // Each row = one physical item with a unique property_number
  protected $fillable = [
    'property_number', 'asset_group_id',
    'current_branch_id','current_division_id','current_section_id','created_by'
  ];

    protected $casts = [
    // legacy casts retained where needed during staged migration
    ];

    public function category() { return $this->belongsTo(Category::class); }
    public function currentBranch() { return $this->belongsTo(Branch::class, 'current_branch_id'); }
    public function currentDivision() { return $this->belongsTo(Division::class, 'current_division_id'); }
    public function currentSection() { return $this->belongsTo(Section::class, 'current_section_id'); }
    public function transferHistories() { return $this->hasMany(AssetTransferHistory::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }

    // Generate property number
    public static function generatePropertyNumber() {
        $year = date('Y');
        $lastAsset = self::where('property_number', 'like', $year . '-%')
                        ->orderBy('property_number', 'desc')
                        ->first();
        
        if ($lastAsset) {
            $lastNumber = (int)substr($lastAsset->property_number, 5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $year . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    public function group(): BelongsTo { return $this->belongsTo(AssetGroup::class, 'asset_group_id'); }
}

### AssetGroup Model
```php
class AssetGroup extends Model
{
    protected $fillable = [
        'description','category_id','date_acquired','unit_cost','status','source','image_path','created_by'
    ];
    public function assets(): HasMany { return $this->hasMany(Asset::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
}
```

## One property number per item, grouped UI

- Creation/update: Every physical item gets a unique year-based property number (e.g., 2025-0004). When creating N identical items, the form creates N Asset rows under one AssetGroup.
- Listing: Asset Management shows groups (one card/row per `asset_group`) with a count badge (e.g., “10 items”). Clicking a group opens a modal listing all items/property numbers, like:
  - 2025-0004 Penguin Classics: Noli Me Tangere (Touch Me Not) by Jose Rizal
  - 2025-0005 Penguin Classics: Noli Me Tangere (Touch Me Not) by Jose Rizal
  - 2025-0006 Penguin Classics: Noli Me Tangere (Touch Me Not) by Jose Rizal
- Histories: Transfers are recorded per item; each asset maintains its own `AssetTransferHistory` timeline.
- Scoping: Always apply `Asset::scopeForUser(User)` to the items relation when querying groups to preserve branch isolation.

### Grouped queries (example)
```php
$groups = AssetGroup::with('category')
  ->withCount(['assets as items_count' => fn($q) => $q->forUser($user)])
  ->whereHas('assets', fn($q) => $q->forUser($user))
  ->orderByDesc('created_at')
  ->paginate(12);
```

### Creation flow (simplified)
1) Find or create an `AssetGroup` from shared fields (description, category, date_acquired, unit_cost, status, source, image).
2) Create N `Asset` rows with `asset_group_id` set, each with a generated unique `property_number` and its own location.

### Migration strategy
- Staged: Add `asset_groups` and `assets.asset_group_id`; backfill links from existing assets; do not drop legacy columns until all UIs/reports are updated. This repo uses a backfill-safe migration for that.
```

## Livewire Components Structure

### Asset Management Components
```
app/Http/Livewire/Assets/
├── AssetList.php          // Main asset listing with filters
├── AssetForm.php          // Create/Edit asset form
├── AssetTransfer.php      // Transfer asset to another location
├── AssetHistory.php       // View transfer history
└── AssetReports.php       // Generate and export reports
```

### Supply Management Components
```
app/Http/Livewire/Supplies/
├── SupplyList.php         // Main supply listing
├── SupplyForm.php         // Create/Edit supply form
├── StockAdjustment.php    // Adjust stock levels
└── SupplyReports.php      // Generate and export reports
// Recommended additions
// ├── Receive.php         // Record stock receipts (increments and moving average)
// ├── Issue.php           // Record issues/consumptions (decrements)
// └── StockCard.php       // Per-supply movement history + running balance (Kardex)
```

## Supply Management (End-to-End)

This section defines a complete, auditable process for supplies: master data, stock movements, balances, valuation, alerts, and reports. It aligns with QCPL’s branch isolation and roles.

### 1) Master Data: Supplies and Categories
- Supplies represent a SKU (e.g., Bond Paper A4 80gsm, Hand Soap 500ml).
- Each supply belongs to a Category with type = 'supply'. Seed at least a few supply categories; otherwise, the Supply form’s Category dropdown will be empty.
- Fields on `supplies`: supply_number (SUP-001), description, category_id, branch_id (owning branch), unit_cost (default/last cost), current_stock, min_stock, status, last_updated.

Notes on branch_id:
- If supplies are tracked per-branch, create the supply under that branch and keep `branch_id` fixed. For inter-branch transfers, record a pair of movements (transfer_out at origin, transfer_in at target), and update each branch’s stock separately.

### 2) Transactions: Supply Movements (Kardex)

Define a `supply_movements` table to record every stock event. This enables:
- Running balance (by branch), traceable history, and reconciliation.
- Valuation using Moving Average Cost (recommended).

Movement types and effects:
- receive (IN): quantity increases; sets or updates moving average using `unit_cost`.
- transfer_in (IN): increases; `unit_cost` comes from origin or last known average.
- issue (OUT): decreases; cost taken from current moving average.
- adjust_in (IN) / adjust_out (OUT): manual corrections; include remarks.
- transfer_out (OUT): decreases at origin.

Business rules:
- Prevent negative stock: do not allow OUT to drive running balance below zero.
- Consistency: after each movement, update `supplies.current_stock` and `supplies.last_updated` for fast list queries.
- Logging: write an `activity_logs` row for auditing (who did what and when).

### 3) Balances and Valuation

- Running balance per branch is the signed sum of movements:
  balance = (receive + transfer_in + adjust_in) - (issue + transfer_out + adjust_out)
- Maintain `supplies.current_stock` as the current on-hand for quick UI. Periodically reconcile with movements.
- Valuation method: Moving Average Cost
  - On receive/transfer_in, recompute moving average:
    new_avg = (old_qty*old_avg + in_qty*in_cost) / (old_qty + in_qty)
  - `supplies.unit_cost` may store the current moving average for reporting; or keep a dedicated `moving_avg_cost` field.
- Inventory value = current_stock * moving_avg_cost.

### 4) Low Stock and Out of Stock

Criteria:
- Low Stock: current_stock < min_stock
- Out of Stock: current_stock = 0

Queries (branch-scoped):
```php
$user = auth()->user();
$low = Supply::forUser($user)
  ->whereColumn('current_stock','<','min_stock')
  ->orderByRaw('(current_stock - min_stock) asc')
  ->get(['id','supply_number','description','current_stock','min_stock']);

$out = Supply::forUser($user)
  ->where('current_stock', 0)
  ->get(['id','supply_number','description']);
```

UI guidance:
- On SupplyList, highlight low/out-of-stock rows and provide quick links to Receive/Adjust.
- Add filters for status, category, and low/out flags.

### 5) Reports and Balance Checking

Core reports:
- Stock Card (Kardex) — per supply, chronological movements with running balance and (if implemented) moving average and line values.
- Low Stock Report — list of supplies below min_stock, with suggested reorder qty (min_stock - current_stock).
- Out of Stock Report — list of zero on-hand.
- Inventory Valuation — by branch and category; totals for units and value.
- Balance Check — reconciles `supplies.current_stock` versus sum of movements; lists discrepancies.

Example: Inventory Valuation (branch-scoped)
```php
$user = auth()->user();
$q = Supply::forUser($user);
$byCategory = $q->selectRaw('category_id, COUNT(*) items, SUM(current_stock) units, SUM(current_stock * unit_cost) value')
  ->groupBy('category_id')
  ->with('category:id,name')
  ->get();
$totals = $q->selectRaw('COUNT(*) items, SUM(current_stock) units, SUM(current_stock * unit_cost) value')->first();
```

Example: Balance Check vs Movements
```php
// Expected balance from movements (by supply and branch)
$expected = DB::table('supply_movements')
  ->select('supply_id','branch_id')
  ->selectRaw("SUM(CASE WHEN type IN ('receive','transfer_in','adjust_in') THEN quantity ELSE 0 END) - \n            SUM(CASE WHEN type IN ('issue','transfer_out','adjust_out') THEN quantity ELSE 0 END) AS balance")
  ->groupBy('supply_id','branch_id');

$discrepancies = DB::table('supplies as s')
  ->joinSub($expected, 'e', function($j) { $j->on('e.supply_id','=','s.id')->on('e.branch_id','=','s.branch_id'); })
  ->whereColumn('s.current_stock','<>','e.balance')
  ->get(['s.id','s.supply_number','s.description','s.current_stock','e.balance']);
```

Exports:
- Use Maatwebsite\Excel or streamed CSV for large datasets. Always apply `Supply::forUser()` and paginate when rendering tables.

PDFs:
- Keep to simple tables for reliability. Avoid images if GD is unavailable.

### 6) Livewire Flows (Recommended)

- SupplyList: search, filters (status/category/low/out), pagination, quick actions (Receive, Issue, Adjust, Edit, Reports).
- SupplyForm: create/edit SKU; auto-generate `supply_number` (SUP-001), choose Category (type='supply').
- Receive: date, reference_no, supplier (optional), qty, unit_cost, remarks → records movement and updates stock and moving average.
- Issue: date, reference_no, destination (optional), qty, remarks → records movement and updates stock.
- Adjust: delta qty (positive/negative), reason/remarks → movement + update; prevent negative ending.
- StockCard: shows movements with running balance and value; export CSV/XLSX.
- SupplyReports: filters + KPI tiles (items, units on hand, on-hand value) + tables (by category/branch) + exports (CSV/XLSX).

### 7) Roles and Scoping

- Access
  - Admin, Supply Officer: full supply management.
  - Observer: read-only reports.
- Scoping
  - Always use `Supply::forUser($user)` in queries to enforce branch isolation. Main-branch admins/observers see all.

### 8) Data Integrity and Performance

- Prevent negative stock on OUT operations.
- Indexes: supplies(branch_id), supplies(status), supplies(last_updated); movements(supply_id, branch_id, created_at).
- Reconciliation task: schedule a nightly job to compare `supplies.current_stock` vs movement sums and log discrepancies.

### 9) Testing (Supplies)

Feature tests
- Create/Edit supply with category type 'supply'.
- Stock receive/issue/adjust flows; ensure branch scoping and negative checks.
- Low and out-of-stock listings.
- Reports export and totals match calculated values.

Unit tests
- Movement math (running balance, moving average).
- ScopeForUser behavior across roles and branches.

### Admin Components
```
app/Http/Livewire/Admin/
├── UserManagement.php     // Manage users
├── BranchManagement.php   // Manage branches
├── CategoryManagement.php // Manage categories
└── ActivityLogs.php       // View system logs
```

## Key Features Implementation

### 1. Role-Based Access Control
```php
// Middleware: CheckRole
public function handle($request, Closure $next, ...$roles)
{
    if (!in_array(auth()->user()->role, $roles)) {
        abort(403, 'Unauthorized');
    }
    return $next($request);
}

// Usage in routes
Route::middleware(['auth', 'check.role:admin,staff'])->group(function () {
    Route::get('/assets', AssetList::class)->name('assets.index');
});
```

### 2. Branch Data Isolation
```php
// Scope for branch-specific data
class Asset extends Model
{
    public function scopeForUser($query, User $user)
    {
        if ($user->isMainBranch() && ($user->isAdmin() || $user->isObserver())) {
            return $query; // Can see all
        }
        
        return $query->where('current_branch_id', $user->branch_id);
    }
}
```

### 3. Transfer System
```php
// AssetTransfer Livewire Component
class AssetTransfer extends Component
{
    public $asset;
    public $target_branch_id;
    public $target_division_id;
    public $target_section_id;
    public $remarks;

    public function transfer()
    {
        // Validate permissions and data
        $this->validate();
        
        // Create transfer history
        AssetTransferHistory::create([
            'asset_id' => $this->asset->id,
            'transfer_date' => now(),
            'origin_branch_id' => $this->asset->currentBranch->id,
            'origin_division_id' => $this->asset->currentDivision->id,
            'origin_section_id' => $this->asset->currentSection->id,
            'previous_branch_id' => $this->asset->current_branch_id,
            'previous_division_id' => $this->asset->current_division_id,
            'previous_section_id' => $this->asset->current_section_id,
            'current_branch_id' => $this->target_branch_id,
            'current_division_id' => $this->target_division_id,
            'current_section_id' => $this->target_section_id,
            'remarks' => $this->remarks,
            'transferred_by' => auth()->id()
        ]);
        
        // Update asset location
        $this->asset->update([
            'current_branch_id' => $this->target_branch_id,
            'current_division_id' => $this->target_division_id,
            'current_section_id' => $this->target_section_id
        ]);
        
        // Log activity
        activity()
            ->performedOn($this->asset)
            ->causedBy(auth()->user())
            ->log('Asset transferred');
    }
}
```

### 4. Reporting System
```php
// Generate Excel reports
use Maatwebsite\Excel\Facades\Excel;

class AssetReports extends Component
{
    public function exportAssets()
    {
        return Excel::download(new AssetsExport, 'assets-report.xlsx');
    }
}

// AssetsExport class
class AssetsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Asset::with(['category', 'currentBranch', 'currentDivision', 'currentSection'])
                   ->forUser(auth()->user())
                   ->get();
    }

    public function headings(): array
    {
        return [
            'Property Number', 'Description', 'Category', 'Quantity',
            'Unit Cost', 'Total Cost', 'Status', 'Branch', 'Division', 'Section'
        ];
    }
}
```

### 5. Image Upload Handling
```php
// In AssetForm Livewire Component
public $image;

public function updatedImage()
{
    $this->validate(['image' => 'image|max:2048']);
}

public function save()
{
    $data = $this->validate();
    
    if ($this->image) {
        $data['image_path'] = $this->image->store('assets', 'public');
    }
    
    Asset::create($data);
}
```

## Frontend Components (Blade + Alpine.js)

### Asset List Component
```html
<div x-data="{ 
    filters: { 
        search: '', 
        category: '', 
        status: '', 
        branch: '' 
    } 
}">
    <!-- Filters -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <input type="text" 
               x-model="filters.search"
               wire:model.debounce.300ms="search"
               placeholder="Search assets..."
               class="form-input">
        
        <select x-model="filters.category" 
                wire:model="categoryFilter"
                class="form-select">
            <option value="">All Categories</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Asset Grid/Table -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($assets as $asset)
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-start mb-4">
                    <span class="text-sm text-gray-500">{{ $asset->property_number }}</span>
                    <span class="px-2 py-1 rounded text-xs 
                        {{ $asset->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $asset->status === 'condemn' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $asset->status === 'disposed' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst($asset->status) }}
                    </span>
                </div>
                
                @if($asset->image_path)
                    <img src="{{ Storage::url($asset->image_path) }}" 
                         alt="{{ $asset->description }}"
                         class="w-full h-32 object-cover rounded mb-4">
                @endif
                
                <h3 class="font-semibold text-lg mb-2">{{ $asset->description }}</h3>
                <p class="text-gray-600 text-sm mb-2">{{ $asset->category->name }}</p>
                <p class="text-gray-600 text-sm">
                    {{ $asset->currentBranch->name }} - 
                    {{ $asset->currentDivision->name }} - 
                    {{ $asset->currentSection->name }}
                </p>
                
                <div class="mt-4 flex justify-between">
                    <span class="font-bold text-lg">₱{{ number_format($asset->total_cost, 2) }}</span>
                    <div class="flex space-x-2">
                        @can('update-asset')
                            <button wire:click="edit({{ $asset->id }})" 
                                    class="text-blue-600 hover:text-blue-800">
                                Edit
                            </button>
                        @endcan
                        
                        @can('transfer-asset')
                            <button wire:click="transfer({{ $asset->id }})" 
                                    class="text-green-600 hover:text-green-800">
                                Transfer
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{ $assets->links() }}
</div>
```

## Key Development Guidelines

1. **Use Livewire for all dynamic interactions**
2. **Implement proper authorization using Gates and Policies**
3. **Use Alpine.js for client-side interactivity**
4. **Follow Laravel naming conventions**
5. **Implement comprehensive logging for audit trails**
6. **Use form requests for validation**
7. **Implement proper error handling and user feedback**
8. **Use queues for heavy operations like report generation**
9. **Implement proper file upload security**
10. **Use Laravel's built-in features (validation, pagination, etc.)**

## Testing Strategy

### Feature Tests
- User authentication and authorization
- Asset CRUD operations
- Transfer functionality
- Report generation
- Branch data isolation

### Unit Tests
- Model relationships
- Business logic methods
- Permission checks
- Data validation

## Deployment Considerations

1. **Environment Configuration**
   - Set up proper database credentials
   - Configure file storage (local/S3)
   - Set up mail configuration for notifications

2. **Security**
   - Implement HTTPS
   - Use Laravel Sanctum for API authentication
   - Regular security updates

3. **Performance**
   - Database indexing for search fields
   - Image optimization
   - Caching strategies
   - Queue workers for background jobs

## Additional Packages to Consider

```bash
composer require spatie/laravel-permission
composer require maatwebsite/laravel-excel  
composer require spatie/laravel-activitylog
composer require intervention/image
composer require barryvdh/laravel-dompdf
```

## Analytics Dashboards (Role-Based)

- Built with Livewire 3; always scope queries via Model::forUser(auth()->user()).
- Common filters: date range, branch/division/section, category, status.
- Cache aggregates per filter key for 10 minutes (Cache::remember).

Components
- Admin: app/Http/Livewire/Admin/AnalyticsDashboard.php
- Staff: app/Http/Livewire/Staff/Analytics.php
- Supply Officer: app/Http/Livewire/Supplies/SupplyAnalytics.php
- Property Officer: app/Http/Livewire/Assets/AssetAnalytics.php

Routes
```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::get('/analytics', \App\Http\Livewire\Admin\AnalyticsDashboard::class)
        ->middleware('check.role:admin,observer')->name('analytics.admin');

    Route::get('/analytics/staff', \App\Http\Livewire\Staff\Analytics::class)
        ->middleware('check.role:staff')->name('analytics.staff');

    Route::get('/analytics/supplies', \App\Http\Livewire\Supplies\SupplyAnalytics::class)
        ->middleware('check.role:supply_officer')->name('analytics.supplies');

    Route::get('/analytics/assets', \App\Http\Livewire\Assets\AssetAnalytics::class)
        ->middleware('check.role:property_officer')->name('analytics.assets');
});
```

Admin (global)
```php
$assetsByStatus = Asset::selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c','status');
$assetsValue = Asset::sum('total_cost');
$supplySkus = Supply::count();
$lowStock = Supply::whereColumn('current_stock','<','min_stock')->count();
$suppliesValue = Supply::selectRaw('SUM(current_stock*unit_cost) v')->value('v');
$monthlyAssets = Asset::selectRaw("DATE_FORMAT(created_at,'%Y-%m') m, COUNT(*) c")->groupBy('m')->orderBy('m')->get();
$topRoutes = AssetTransferHistory::selectRaw('origin_branch_id,current_branch_id,COUNT(*) c')
  ->groupBy('origin_branch_id','current_branch_id')->orderByDesc('c')->limit(5)->get();
```

Staff (branch-scoped)
```php
$user = auth()->user();
$byStatus = Asset::forUser($user)->selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c','status');
$incoming = AssetTransferHistory::where('current_branch_id',$user->branch_id)
  ->whereBetween('transfer_date',[$from,$to])->count();
$outgoing = AssetTransferHistory::where('origin_branch_id',$user->branch_id)
  ->whereBetween('transfer_date',[$from,$to])->count();
$lowStockList = Supply::where('branch_id',$user->branch_id)
  ->whereColumn('current_stock','<','min_stock')
  ->orderByRaw('(current_stock - min_stock) asc')->limit(10)->get();
```

Supply Officer
```php
$low = Supply::whereColumn('current_stock','<','min_stock')->count();
$out = Supply::where('current_stock',0)->count();
$onHandUnits = Supply::sum('current_stock');
$onHandValue = Supply::selectRaw('SUM(current_stock*unit_cost) v')->value('v');
$byCategory = Supply::selectRaw('category_id, COUNT(*) items, SUM(current_stock*unit_cost) value')
  ->groupBy('category_id')->with('category:id,name')->get();
$recent = Supply::orderByDesc('last_updated')->limit(10)->get(['id','description','current_stock','min_stock','last_updated']);
```

Property Officer
```php
$q = Asset::forUser(auth()->user());
$byStatus = $q->selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c','status');
$totalValue = $q->sum('total_cost');
$byYear = $q->selectRaw('YEAR(date_acquired) y, COUNT(*) cnt, SUM(total_cost) val')
  ->groupBy('y')->orderBy('y')->get();
$in = AssetTransferHistory::where('current_branch_id',auth()->user()->branch_id)
  ->whereBetween('transfer_date',[$from,$to])->count();
$out = AssetTransferHistory::where('origin_branch_id',auth()->user()->branch_id)
  ->whereBetween('transfer_date',[$from,$to])->count();
```

Scaffold (Livewire)
```bash
php artisan make:livewire Admin/AnalyticsDashboard
php artisan make:livewire Staff/Analytics
php artisan make:livewire Supplies/SupplyAnalytics
php artisan make:livewire Assets/AssetAnalytics
```

Indexes (performance)
- assets: index(status), index(date_acquired), index(current_branch_id)
- supplies: index(branch_id), index(status), index(last_updated)
- asset_transfer_histories: index(transfer_date), index(origin_branch_id), index(current_branch_id)

## Shadcn UI Style Integration (Tailwind + Blade)

Goal
- Use shadcn/ui-inspired tokens and utilities with Blade/Livewire.
- Keep Tailwind as the base; add CSS variables, animations, and reusable Blade components.

Packages
```bash
npm i -D tailwindcss-animate
```

Tailwind config (tokens + plugin)
```js
// filepath: c:\Users\navar\my-projects\qcpl-ims\tailwind.config.js
// ...existing code...
export default {
  darkMode: ["class"],
  content: [
    "./resources/views/**/*.blade.php",
    "./resources/js/**/*.js",
    "./app/Http/Livewire/**/*.php",
  ],
  theme: {
    container: { center: true, padding: "2rem" },
    extend: {
      colors: {
        border: "hsl(var(--border))",
        input: "hsl(var(--input))",
        ring: "hsl(var(--ring))",
        background: "hsl(var(--background))",
        foreground: "hsl(var(--foreground))",
        primary: {
          DEFAULT: "hsl(var(--primary))",
          foreground: "hsl(var(--primary-foreground))",
        },
        secondary: {
          DEFAULT: "hsl(var(--secondary))",
          foreground: "hsl(var(--secondary-foreground))",
        },
        destructive: {
          DEFAULT: "hsl(var(--destructive))",
          foreground: "hsl(var(--destructive-foreground))",
        },
        muted: {
          DEFAULT: "hsl(var(--muted))",
          foreground: "hsl(var(--muted-foreground))",
        },
        accent: {
          DEFAULT: "hsl(var(--accent))",
          foreground: "hsl(var(--accent-foreground))",
        },
        popover: {
          DEFAULT: "hsl(var(--popover))",
          foreground: "hsl(var(--popover-foreground))",
        },
        card: {
          DEFAULT: "hsl(var(--card))",
          foreground: "hsl(var(--card-foreground))",
        },
      },
      borderRadius: {
        lg: "var(--radius)",
        md: "calc(var(--radius) - 2px)",
        sm: "calc(var(--radius) - 4px)",
      },
      keyframes: {
        "accordion-down": { from: { height: 0 }, to: { height: "var(--radix-accordion-content-height)" } },
        "accordion-up": { from: { height: "var(--radix-accordion-content-height)" }, to: { height: 0 } },
      },
      animation: {
        "accordion-down": "accordion-down 0.2s ease-out",
        "accordion-up": "accordion-up 0.2s ease-out",
      },
    },
  },
  plugins: [require("tailwindcss-animate")],
}
```

Base CSS variables (light/dark)
```css
// filepath: c:\Users\navar\my-projects\qcpl-ims\resources\css\app.css
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  :root {
    --background: 0 0% 100%;
    --foreground: 222.2 84% 4.9%;
    --card: 0 0% 100%;
    --card-foreground: 222.2 84% 4.9%;
    --popover: 0 0% 100%;
    --popover-foreground: 222.2 84% 4.9%;
    --primary: 221.2 83.2% 53.3%;
    --primary-foreground: 210 40% 98%;
    --secondary: 210 40% 96.1%;
    --secondary-foreground: 222.2 47.4% 11.2%;
    --muted: 210 40% 96.1%;
    --muted-foreground: 215.4 16.3% 46.9%;
    --accent: 210 40% 96.1%;
    --accent-foreground: 222.2 47.4% 11.2%;
    --destructive: 0 84.2% 60.2%;
    --destructive-foreground: 0 0% 98%;
    --border: 214.3 31.8% 91.4%;
    --input: 214.3 31.8% 91.4%;
    --ring: 221.2 83.2% 53.3%;
    --radius: 0.5rem;
  }
  .dark {
    --background: 222.2 84% 4.9%;
    --foreground: 210 40% 98%;
    --card: 222.2 84% 4.9%;
    --card-foreground: 210 40% 98%;
    --popover: 222.2 84% 4.9%;
    --popover-foreground: 210 40% 98%;
    --primary: 217.2 91.2% 59.8%;
    --primary-foreground: 222.2 47.4% 11.2%;
    --secondary: 217.2 32.6% 17.5%;
    --secondary-foreground: 210 40% 98%;
    --muted: 217.2 32.6% 17.5%;
    --muted-foreground: 215 20.2% 65.1%;
    --accent: 217.2 32.6% 17.5%;
    --accent-foreground: 210 40% 98%;
    --destructive: 0 62.8% 30.6%;
    --destructive-foreground: 210 40% 98%;
    --border: 217.2 32.6% 17.5%;
    --input: 217.2 32.6% 17.5%;
    --ring: 224.3 76.3% 48%;
  }
  * { @apply border-border; }
  body { @apply bg-background text-foreground; }
}
```

Reusable UI components (Blade)
```bash
php artisan make:component UI/Button
php artisan make:component UI/Card
php artisan make:component UI/Badge
php artisan make:component UI/Input
php artisan make:component UI/Table
```

Button
```blade
// filepath: c:\Users\navar\my-projects\qcpl-ims\resources\views\components\ui\button.blade.php
@props([
  'variant' => 'primary',
  'size' => 'default',
])

@php
$base = 'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium
  ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2
  focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50';

$variants = [
  'primary' => 'bg-primary text-primary-foreground hover:bg-primary/90',
  'secondary' => 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
  'destructive' => 'bg-destructive text-destructive-foreground hover:bg-destructive/90',
  'ghost' => 'hover:bg-accent hover:text-accent-foreground',
  'outline' => 'border border-input bg-background hover:bg-accent hover:text-accent-foreground',
];

$sizes = [
  'sm' => 'h-9 px-3',
  'default' => 'h-10 px-4 py-2',
  'lg' => 'h-11 px-8',
];
@endphp

<button {{ $attributes->merge(['class' => "$base {$variants[$variant]} {$sizes[$size]}"]) }}>
  {{ $slot }}
</button>
```

Card
```blade
// filepath: c:\Users\navar\my-projects\qcpl-ims\resources\views\components\ui\card.blade.php
<div {{ $attributes->merge(['class' => 'rounded-lg border bg-card text-card-foreground shadow-sm']) }}>
  {{ $slot }}
</div>
```

Badge
```blade
// filepath: c:\Users\navar\my-projects\qcpl-ims\resources\views\components\ui\badge.blade.php
@props(['variant' => 'default'])

@php
$base = 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold';
$variants = [
  'default' => 'bg-primary text-primary-foreground',
  'secondary' => 'bg-secondary text-secondary-foreground',
  'outline' => 'border-border text-foreground',
  'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
  'success' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
  'danger' => 'bg-destructive text-destructive-foreground',
];
@endphp

<span {{ $attributes->merge(['class' => "$base {$variants[$variant]}"]) }}>
  {{ $slot }}
</span>
```

Input
```blade
// filepath: c:\Users\navar\my-projects\qcpl-ims\resources\views\components\ui\input.blade.php
@props(['type' => 'text'])
<input
  type="{{ $type }}"
  {{ $attributes->merge([
    'class' => 'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm
                ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium
                placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2
                focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50'
  ]) }}
/>
```

Table (wrapper)
```blade
// filepath: c:\Users\navar\my-projects\qcpl-ims\resources\views\components\ui\table.blade.php
<div class="overflow-x-auto rounded-md border">
  <table class="w-full caption-bottom text-sm">
    {{ $slot }}
  </table>
</div>
```

Example: Shadcn-styled Asset List (Blade + Livewire)
```blade
{{-- resources/views/livewire/assets/asset-list.blade.php --}}
<div class="space-y-6">
  {{-- Filters --}}
  <x-ui-card class="p-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <x-ui-input wire:model.debounce.300ms="search" placeholder="Search assets..." />
      <select wire:model="categoryFilter"
              class="h-10 w-full rounded-md border border-input bg-background px-3 text-sm
                     focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
        <option value="">All Categories</option>
        @foreach($categories as $category)
          <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
      </select>
      <select wire:model="statusFilter"
              class="h-10 w-full rounded-md border border-input bg-background px-3 text-sm
                     focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="condemn">Condemn</option>
        <option value="disposed">Disposed</option>
      </select>
      <x-ui-button wire:click="resetFilters" variant="outline">Reset</x-ui-button>
    </div>
  </x-ui-card>

  {{-- Grid --}}
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($assets as $asset)
      <x-ui-card class="p-6">
        <div class="flex items-start justify-between mb-3">
          <span class="text-xs text-muted-foreground">{{ $asset->property_number }}</span>
          <x-ui-badge
            :variant="$asset->status === 'active' ? 'success' : ($asset->status === 'condemn' ? 'warning' : 'danger')">
            {{ ucfirst($asset->status) }}
          </x-ui-badge>
        </div>

        @if($asset->image_path)
          <img src="{{ Storage::url($asset->image_path) }}" alt="{{ $asset->description }}"
               class="mb-4 h-32 w-full rounded-md object-cover border" />
        @endif

        <h3 class="mb-1 text-lg font-semibold">{{ $asset->description }}</h3>
        <p class="mb-2 text-sm text-muted-foreground">{{ $asset->category->name }}</p>
        <p class="text-sm text-muted-foreground">
          {{ $asset->currentBranch->name }} • {{ $asset->currentDivision->name }} • {{ $asset->currentSection->name }}
        </p>

        <div class="mt-4 flex items-center justify-between">
          <span class="text-lg font-bold">₱{{ number_format($asset->total_cost, 2) }}</span>
          <div class="flex gap-2">
            @can('update-asset')
              <x-ui-button wire:click="edit({{ $asset->id }})" variant="secondary" size="sm">Edit</x-ui-button>
            @endcan
            @can('transfer-asset')
              <x-ui-button wire:click="transfer({{ $asset->id }})" size="sm">Transfer</x-ui-button>
            @endcan
          </div>
        </div>
      </x-ui-card>
    @endforeach
  </div>

  <div>
    {{ $assets->links() }}
  </div>
</div>
```

Analytics tiles (shadcn-styled)
- Use a 4-col responsive grid. Each KPI as a Card with muted label and bold value.

```blade
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
  <x-ui-card class="p-4">
    <p class="text-sm text-muted-foreground">Total Assets</p>
    <p class="mt-2 text-2xl font-bold">{{ number_format($totalAssets) }}</p>
  </x-ui-card>
  <x-ui-card class="p-4">
    <p class="text-sm text-muted-foreground">Low-stock Supplies</p>
    <p class="mt-2 text-2xl font-bold">{{ number_format($lowStock) }}</p>
  </x-ui-card>
  <x-ui-card class="p-4">
    <p class="text-sm text-muted-foreground">Assets Value</p>
    <p class="mt-2 text-2xl font-bold">₱{{ number_format($assetsValue, 2) }}</p>
  </x-ui-card>
  <x-ui-card class="p-4">
    <p class="text-sm text-muted-foreground">Supplies Value</p>
    <p class="mt-2 text-2xl font-bold">₱{{ number_format($suppliesValue, 2) }}</p>
  </x-ui-card>
</div>
```

Notes
- Keep focus-visible ring styles on interactive elements to match shadcn accessibility.
- Prefer cards, badges, outline buttons, and subtle borders to achieve the look.
- Dark mode: toggle html.classList.add('dark') for dark palette via CSS variables.


This guide provides a comprehensive foundation for building the QCPL-IMS system. Follow Laravel best practices and use this structure to guide GitHub Copilot in generating the appropriate code for each component.

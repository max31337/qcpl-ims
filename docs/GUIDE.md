# QCPL-IMS - Quezon City Public Library Inventory Management System

Lean Livewire via Services + Repository layers. Strict branch scoping and transfer history-first flow preserved.

## Project Overview
Laravel + Livewire (TALL) app for managing assets and supplies across QCPL branches with clear separation of concerns:
- Repositories: data access and scoping
- Services: business rules, transactions, logging
- Livewire: thin controllers/view-models

## Technology Stack
- Laravel 10+
- Livewire 3
- Alpine.js
- Tailwind CSS
- MySQL (prod), SQLite (local ok)
- Maatwebsite\Excel, Spatie Activity Log

## Setup (recommended)
```bash
composer create-project laravel/laravel qcpl-ims
cd qcpl-ims
composer require laravel/breeze --dev
php artisan breeze:install livewire
composer update -W
npm install && npm run dev
cp .env.example .env && php artisan key:generate
# for SQLite quick start
mkdir -p database && type nul > database\database.sqlite
php artisan migrate && php artisan storage:link && php artisan serve
```

## Architecture: Repositories + Services

Goals
- Livewire components stay lean (no data access or business rules).
- All queries branch/role scoped via repository methods (forUser).
- Services enforce invariants (e.g., history-first transfers, non-negative stock), wrap writes in transactions, and log activity.

Structure
```
app/
├── Repositories/
│   ├── Contracts/
│   │   ├── AssetRepository.php
│   │   ├── AssetGroupRepository.php
│   │   ├── SupplyRepository.php
│   │   ├── SupplyMovementRepository.php
│   │   ├── AssetTransferHistoryRepository.php
│   │   └── CategoryRepository.php
│   └── Eloquent/
│       ├── EloquentAssetRepository.php
│       ├── EloquentAssetGroupRepository.php
│       ├── EloquentSupplyRepository.php
│       ├── EloquentSupplyMovementRepository.php
│       └── EloquentAssetTransferHistoryRepository.php
├── Services/
│   ├── Assets/AssetReadService.php
│   ├── Assets/AssetWriteService.php
│   ├── Assets/AssetTransferService.php
│   ├── Supplies/SupplyReadService.php
│   ├── Supplies/SupplyMovementService.php
│   └── Reporting/ExportService.php
└── Providers/RepositoryServiceProvider.php
```

Bind interfaces to Eloquent in a single provider
```php
// app/Providers/RepositoryServiceProvider.php
class RepositoryServiceProvider extends ServiceProvider {
  public function register(): void {
    $this->app->bind(Contracts\AssetRepository::class, Eloquent\EloquentAssetRepository::class);
    $this->app->bind(Contracts\AssetGroupRepository::class, Eloquent\EloquentAssetGroupRepository::class);
    $this->app->bind(Contracts\SupplyRepository::class, Eloquent\EloquentSupplyRepository::class);
    $this->app->bind(Contracts\SupplyMovementRepository::class, Eloquent\EloquentSupplyMovementRepository::class);
    $this->app->bind(Contracts\AssetTransferHistoryRepository::class, Eloquent\EloquentAssetTransferHistoryRepository::class);
    $this->app->bind(Contracts\CategoryRepository::class, Eloquent\EloquentCategoryRepository::class);
  }
}
// config/app.php: add Providers\RepositoryServiceProvider::class
```

### Repository contracts (examples)
```php
// app/Repositories/Contracts/AssetRepository.php
interface AssetRepository {
  public function paginateForUser(User $user, array $filters = [], int $perPage = 12): LengthAwarePaginator;
  public function findForUser(User $user, int $id): ?Asset;
  public function createMany(array $rows): Collection; // create N Asset rows
  public function updateLocation(Asset $asset, int $branchId, int $divisionId, int $sectionId): void;
  public function nextPropertyNumber(): string; // delegates to Asset::generatePropertyNumber()
}

// app/Repositories/Contracts/AssetGroupRepository.php
interface AssetGroupRepository {
  public function upsert(array $data): AssetGroup;
  public function paginateGroupsForUser(User $user, array $filters = [], int $perPage = 12): LengthAwarePaginator;
}

// app/Repositories/Contracts/SupplyRepository.php
interface SupplyRepository {
  public function paginateForUser(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator;
  public function findForUser(User $user, int $id): ?Supply;
  public function updateStockAndCost(int $id, int $newStock, float $movingAvg): void;
}

// app/Repositories/Contracts/SupplyMovementRepository.php
interface SupplyMovementRepository {
  public function record(array $data): SupplyMovement;
  public function sumBalanceBySupplyAndBranch(int $supplyId, int $branchId): int;
}
```

Eloquent implementation must enforce branch scoping
```php
// app/Repositories/Eloquent/EloquentAssetGroupRepository.php
class EloquentAssetGroupRepository implements AssetGroupRepository {
  public function paginateGroupsForUser(User $user, array $filters = [], int $perPage = 12): LengthAwarePaginator {
    return AssetGroup::query()
      ->with('category:id,name')
      ->withCount(['assets as items_count' => fn($q) => $q->forUser($user)])
      ->when($filters['search'] ?? null, fn($q,$s) => $q->where('description','like',"%$s%"))
      ->when($filters['category_id'] ?? null, fn($q,$c) => $q->where('category_id',$c))
      ->whereHas('assets', fn($q) => $q->forUser($user))
      ->orderByDesc('created_at')
      ->paginate($perPage);
  }
}
```

### Services (business logic)

Assets: create grouped items and transfer with history-first
```php
// app/Services/Assets/AssetWriteService.php
class AssetWriteService {
  public function __construct(
    private AssetRepository $assets,
    private AssetGroupRepository $groups,
    private AssetTransferHistoryRepository $transfers,
  ) {}

  public function createGroupWithItems(array $groupData, int $quantity, array $location, User $actor): AssetGroup {
    return DB::transaction(function() use ($groupData,$quantity,$location,$actor) {
      Gate::authorize('create', Asset::class);

      $group = $this->groups->upsert([
        ...Arr::only($groupData, ['description','category_id','date_acquired','unit_cost','status','source','image_path']),
        'created_by' => $actor->id,
      ]);

      $rows = [];
      for ($i=0; $i<$quantity; $i++) {
        $rows[] = [
          'property_number'   => $this->assets->nextPropertyNumber(),
          'asset_group_id'    => $group->id,
          'current_branch_id' => $location['branch_id'],
          'current_division_id'=> $location['division_id'],
          'current_section_id'=> $location['section_id'],
          'created_by'        => $actor->id,
          'created_at'        => now(),
          'updated_at'        => now(),
        ];
      }
      $this->assets->createMany($rows);

      activity()->causedBy($actor)->performedOn($group)->withProperties(['qty'=>$quantity])->log('asset_group.create_with_items');
      return $group;
    });
  }
}

// app/Services/Assets/AssetTransferService.php
class AssetTransferService {
  public function __construct(
    private AssetRepository $assets,
    private AssetTransferHistoryRepository $histories,
  ) {}

  public function transfer(int $assetId, array $target, ?string $remarks, User $actor): void {
    DB::transaction(function() use ($assetId,$target,$remarks,$actor) {
      $asset = $this->assets->findForUser($actor, $assetId) ?? throw new ModelNotFoundException();
      Gate::authorize('transfer', $asset);

      $this->histories->create([
        'asset_id'            => $asset->id,
        'transfer_date'       => now()->toDateString(),
        'origin_branch_id'    => $asset->current_branch_id,
        'origin_division_id'  => $asset->current_division_id,
        'origin_section_id'   => $asset->current_section_id,
        'previous_branch_id'  => $asset->current_branch_id,
        'previous_division_id'=> $asset->current_division_id,
        'previous_section_id' => $asset->current_section_id,
        'current_branch_id'   => $target['branch_id'],
        'current_division_id' => $target['division_id'],
        'current_section_id'  => $target['section_id'],
        'remarks'             => $remarks,
        'transferred_by'      => $actor->id,
        'created_at'          => now(), 'updated_at' => now(),
      ]);

      $this->assets->updateLocation($asset, $target['branch_id'], $target['division_id'], $target['section_id']);

      activity()->causedBy($actor)->performedOn($asset)->withProperties(['to'=>$target])->log('asset.transfer');
    });
  }
}
```

Supplies: moving average and non-negative enforcement
```php
// app/Services/Supplies/SupplyMovementService.php
class SupplyMovementService {
  public function __construct(
    private SupplyRepository $supplies,
    private SupplyMovementRepository $movements
  ) {}

  public function receive(int $supplyId, int $branchId, int $qty, float $unitCost, array $meta, User $actor): void {
    DB::transaction(function() use ($supplyId,$branchId,$qty,$unitCost,$meta,$actor) {
      $supply = $this->supplies->findForUser($actor, $supplyId) ?? throw new ModelNotFoundException();
      Gate::authorize('update', $supply);

      $current = $supply->current_stock;
      $oldAvg  = $supply->unit_cost; // or moving_avg_cost if separate
      $newAvg  = (($current * $oldAvg) + ($qty * $unitCost)) / max(1, ($current + $qty));

      $this->movements->record([
        'supply_id' => $supplyId, 'branch_id' => $branchId, 'type' => 'receive',
        'quantity' => $qty, 'unit_cost' => $unitCost, 'reference_no' => $meta['ref'] ?? null,
        'remarks' => $meta['remarks'] ?? null, 'created_by' => $actor->id, 'created_at' => now(),
      ]);

      $this->supplies->updateStockAndCost($supplyId, $current + $qty, $newAvg);
      activity()->causedBy($actor)->performedOn($supply)->withProperties(['qty'=>$qty,'unit_cost'=>$unitCost])->log('supply.receive');
    });
  }

  public function issue(int $supplyId, int $branchId, int $qty, array $meta, User $actor): void {
    DB::transaction(function() use ($supplyId,$branchId,$qty,$meta,$actor) {
      $supply = $this->supplies->findForUser($actor, $supplyId) ?? throw new ModelNotFoundException();
      Gate::authorize('update', $supply);

      $after = $supply->current_stock - $qty;
      if ($after < 0) throw ValidationException::withMessages(['quantity' => 'Insufficient stock']);

      $this->movements->record([
        'supply_id' => $supplyId, 'branch_id' => $branchId, 'type' => 'issue',
        'quantity' => $qty, 'remarks' => $meta['remarks'] ?? null, 'created_by' => $actor->id, 'created_at' => now(),
      ]);

      $this->supplies->updateStockAndCost($supplyId, $after, $supply->unit_cost);
      activity()->causedBy($actor)->performedOn($supply)->withProperties(['qty'=>$qty])->log('supply.issue');
    });
  }
}
```

### Livewire components (lean)

Asset list (grouped UI)
```php
// app/Http/Livewire/Assets/AssetList.php
class AssetList extends Component {
  public string $search = '';
  public ?int $categoryFilter = null;

  public function boot(AssetReadService $svc) { $this->svc = $svc; }

  public function render() {
    $groups = $this->svc->paginateGroups(
      auth()->user(),
      ['search'=>$this->search, 'category_id'=>$this->categoryFilter],
      perPage: 12
    );
    return view('livewire.assets.asset-list', compact('groups'));
  }
}
```

Asset transfer
```php
// app/Http/Livewire/Assets/AssetTransfer.php
class AssetTransfer extends Component {
  public int $assetId;
  public ?int $target_branch_id = null, $target_division_id = null, $target_section_id = null;
  public ?string $remarks = null;

  protected $rules = [
    'target_branch_id' => 'required|exists:branches,id',
    'target_division_id' => 'required|exists:divisions,id',
    'target_section_id' => 'required|exists:sections,id',
    'remarks' => 'nullable|string|max:500',
  ];

  public function boot(AssetTransferService $svc) { $this->svc = $svc; }

  public function transfer() {
    $this->validate();
    $this->svc->transfer(
      $this->assetId,
      ['branch_id'=>$this->target_branch_id,'division_id'=>$this->target_division_id,'section_id'=>$this->target_section_id],
      $this->remarks,
      auth()->user()
    );
    $this->dispatch('transferred');
  }
}
```

Supply receive
```php
class Receive extends Component {
  public int $supplyId, $qty; public float $unit_cost; public ?string $reference_no = null, $remarks = null;

  protected $rules = ['qty'=>'required|integer|min:1','unit_cost'=>'required|numeric|min:0'];

  public function boot(SupplyMovementService $svc) { $this->svc = $svc; }

  public function save() {
    $this->validate();
    $this->svc->receive($this->supplyId, auth()->user()->branch_id, $this->qty, $this->unit_cost, [
      'ref'=>$this->reference_no,'remarks'=>$this->remarks
    ], auth()->user());
    $this->dispatch('received');
  }
}
```

### Reporting and exports
```php
class ExportService {
  public function __construct(private AssetGroupRepository $groups, private SupplyRepository $supplies) {}

  public function assetsForExport(User $user, array $filters = []): Collection {
    // pull minimal columns; always branch-scope inside repos
    return $this->groups->paginateGroupsForUser($user, $filters, perPage: PHP_INT_MAX)->getCollection();
  }
}
```

Excel export uses ExportService, never queries models directly.

## Domain rules preserved

- Strict data isolation: repositories enforce forUser(User) on every read/write path. Main-branch admins/observers see all; others are branch-scoped.
- Assets denormalize current_branch_id/current_division_id/current_section_id.
- Transfers write AssetTransferHistory first, then mutate asset location, inside one DB transaction.
- Supplies use supply_movements for auditable stock changes; prevent negative balances; moving average on receive/transfer_in.
- Activity logging in services after successful transactions.

## Models and scopes

Keep model scopes for reuse within repositories
```php
// app/Models/Concerns/ScopesForUser.php
trait ScopesForUser {
  public function scopeForUser(Builder $q, User $user): Builder {
    if ($user->isMainBranch() && ($user->isAdmin() || $user->isObserver())) return $q;
    if ($this instanceof Asset)   return $q->where('current_branch_id', $user->branch_id);
    if ($this instanceof Supply)  return $q->where('branch_id', $user->branch_id);
    return $q;
  }
}
```

## Livewire patterns

- Inputs: wire:model.debounce.300ms on filters/search.
- Authorization: Policies/Gates; call Gate::authorize in services for sensitive ops.
- Avoid inline role checks in Blade; use @can/policies.

## Analytics (service-backed)

- Services prepare aggregates (scoped) and cache results for 10 minutes by filter key using Cache::remember.
- Components call services and render tiles/charts.

## Testing

- Bind fakes/mocks to repository interfaces in tests to isolate Livewire from DB.
- Service tests cover transactions, scoping, invariants (history-first, non-negative stock).
- Feature tests assert policies and events/logs.

Example mocking
```php
$this->app->bind(AssetGroupRepository::class, fn() => Mockery::mock(AssetGroupRepository::class)
  ->shouldReceive('paginateGroupsForUser')->andReturn(new LengthAwarePaginator(...))->getMock());
```

## Notes

- Images: validate image|max:2048; store under storage/app/public/assets; render via Storage::url().
- Exports: Maatwebsite\Excel FromCollection + WithHeadings; use services/repositories and always enforce forUser().
- UI: Tailwind + Alpine; status badges: green=active, yellow=condemn, red=disposed.

This layered approach keeps Livewire components thin, centralizes business rules, and preserves QCPL isolation and auditability.

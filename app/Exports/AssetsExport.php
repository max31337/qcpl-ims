<?php

namespace App\Exports;

use App\Models\Asset;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected Authenticatable $user,
        protected ?int $branchId = null,
        protected ?int $divisionId = null,
        protected ?int $sectionId = null,
        protected ?int $categoryId = null,
        protected ?string $status = null,
        protected ?string $search = null,
        protected ?string $from = null,
        protected ?string $to = null,
    ) {}

    public function collection()
    {
        $q = Asset::with(['category','currentBranch','currentDivision','currentSection'])
            ->forUser($this->user);

        if ($this->branchId) $q->where('current_branch_id', $this->branchId);
        if ($this->divisionId) $q->where('current_division_id', $this->divisionId);
        if ($this->sectionId) $q->where('current_section_id', $this->sectionId);
        if ($this->categoryId) $q->where('category_id', $this->categoryId);
        if ($this->status) $q->where('status', $this->status);
        if ($this->search) {
            $s = "%{$this->search}%";
            $q->where(function($w) use ($s) {
                $w->where('property_number','like',$s)
                  ->orWhere('description','like',$s);
            });
        }
        if ($this->from) $q->whereDate('date_acquired', '>=', Carbon::parse($this->from));
        if ($this->to) $q->whereDate('date_acquired', '<=', Carbon::parse($this->to));

        return $q->orderBy('property_number')->get();
    }

    public function headings(): array
    {
        return [
            'Property Number','Description','Category','Quantity','Unit Cost','Total Cost','Status','Source',
            'Date Acquired','Branch','Division','Section'
        ];
    }

    public function map($asset): array
    {
        return [
            $asset->property_number,
            $asset->description,
            optional($asset->category)->name,
            $asset->quantity,
            number_format((float)$asset->unit_cost, 2, '.', ''),
            number_format((float)$asset->total_cost, 2, '.', ''),
            $asset->status,
            $asset->source,
            optional($asset->date_acquired)?->format('Y-m-d'),
            optional($asset->currentBranch)->name,
            optional($asset->currentDivision)->name,
            optional($asset->currentSection)->name,
        ];
    }
}

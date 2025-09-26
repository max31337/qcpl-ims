<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $districts = [
            'District 1' => [
                'Project 7', 'Project 8', 'Balingasa', 'Masambong', 'Bagong Pag-asa', 'Nayong Kanluran', 'N.S. Amoranto', 'Sto. Cristo',
            ],
            'District 2' => [
                'Payatas Lupang Pangako', 'Payatas Landfill', 'Bagong Silangan', 'Holy Spirit', 'MRB Commonwealth',
            ],
            'District 3' => [
                'Greater Project 4', 'Escopa III', 'Tagumpay', 'Matandang Balara', 'Camp Aguinaldo',
            ],
            'District 4' => [
                'Cubao', 'San Isidro-Galas', 'Krus Na Ligas', 'UP Campus - Pook Amorsolo', 'UP Campus - Pook Dagohoy',
            ],
            'District 5' => [
                'Novaliches', 'Lagro', 'North Fairview', 'Sta. Lucia', 'Bagbag', 'Pasong Putik',
            ],
            'District 6' => [
                'Talipapa', 'Sagana Homes I', 'Tandang Sora', 'Culiat', 'Balong Bato',
            ],
        ];

        foreach ($districts as $district => $branches) {
            // Extract district number for code prefix
            preg_match('/(\d+)/', $district, $m);
            $dnum = $m[1] ?? 'X';
            foreach ($branches as $name) {
                $base = strtoupper(preg_replace('/[^A-Z0-9]+/', '', Str::ascii($name)));
                $code = 'D' . $dnum . '-' . $base;

                Branch::firstOrCreate(
                    ['code' => $code],
                    [
                        'name' => $name,
                        'district' => $district,
                        'address' => 'Quezon City - ' . $name,
                        'is_main' => false,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}

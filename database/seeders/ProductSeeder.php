<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'reference'    => 'K0307-01',
                'name'         => 'PRECICE dCK Phosphorylation Assay Kit',
                'description'  => 'The PRECICE dCK Phosphorylation Assay Kit is designed to measure the activity of deoxycytidine kinase (dCK) in biological samples. This kit provides a convenient and reliable method for assessing dCK activity, which is important for understanding nucleoside metabolism and its implications in various diseases.',
                'product_unit' => '(1 plate, 96 assays)',
                'unit_price'   => 530.00,
                'page_url'     => null,
            ],
            [
                'reference'    => 'WEB-001',
                'name'         => 'Website Design',
                'description'  => 'Full responsive website design, up to 5 pages.',
                'product_unit' => 'project',
                'unit_price'   => 1500.00,
                'page_url'     => null,
            ],
            [
                'reference'    => 'DEV-001',
                'name'         => 'Custom Development',
                'description'  => 'Custom feature development, billed per hour.',
                'product_unit' => 'hour',
                'unit_price'   => 85.00,
                'page_url'     => null,
            ],
        ];

        foreach ($products as $product) {
            DB::table('products')->updateOrInsert(
                ['reference' => $product['reference']],
                array_merge($product, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}

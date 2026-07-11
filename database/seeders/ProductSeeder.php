<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'reference'    => 'K0709-04-2',
                'name'         => 'PRECICE® PRPP-S Assay Kit',
                'product_unit' => '10 mL of reaction mixture (half 96-well plate)',
                'unit_price'   => 515.00,
                'page_url'     => 'convenient-assay-kits/prpp-s-assay-kit',
            ],
            [
                'reference'    => 'K0709-01-2',
                'name'         => 'PRECICE® HPRT Assay Kit',
                'product_unit' => '10 mL, 24 analyses (8 samples in triplicate)',
                'unit_price'   => 420.00,
                'page_url'     => 'convenient-assay-kits/hprt-assay-kit',
            ],
            [
                'reference'    => 'K0709-05-2',
                'name'         => 'AMP Deaminase Assay Kit',
                'product_unit' => '24 analyses (10 ml reaction buffer)',
                'unit_price'   => 510.00,
                'page_url'     => 'convenient-assay-kits/ampda-assay-kit',
            ],
            [
                'reference'    => 'K0709-06-2',
                'name'         => 'ITP-ase Assay Kit',
                'product_unit' => '10 mL of reaction mixture (half 96-well plate)',
                'unit_price'   => 440.00,
                'page_url'     => 'convenient-assay-kits/itp-ase-assay-kit',
            ],
            [
                'reference'    => 'K0507-02',
                'name'         => 'ADK Phosphorylation Assay Kit',
                'product_unit' => '1 plate (96 assays)',
                'unit_price'   => 530.00,
                'page_url'     => 'convenient-assay-kits/adk-phosphorylation-assay-kit',
            ],
            [
                'reference'    => 'K0307-01',
                'name'         => 'PRECICE® dCK Phosphorylation Assay Kit',
                'product_unit' => '1 plate (96 assays)',
                'unit_price'   => 530.00,
                'page_url'     => 'convenient-assay-kits/dck-phosphorylation-assay-kit',
            ],
            [
                'reference'    => 'K0507-01.01',
                'name'         => 'Adk Assay Kit',
                'product_unit' => '1 plate (96 assays)',
                'unit_price'   => 530.00,
                'page_url'     => 'convenient-assay-kits/adk-assay-kit',
            ],
            [
                'reference'    => 'E-Nov8 25',
                'name'         => 'FMN-Reductase',
                'product_unit' => '25 Units',
                'unit_price'   => 440.00,
                'page_url'     => 'active-purified-enzymes/fmn-reductase',
            ],
            [
                'reference'    => 'E-Nov8 50',
                'name'         => 'FMN-Reductase',
                'product_unit' => '50 Units',
                'unit_price'   => 705.00,
                'page_url'     => 'active-purified-enzymes/fmn-reductase',
            ],
            [
                'reference'    => 'E-Nov8 100',
                'name'         => 'FMN-Reductase',
                'product_unit' => '100 Units',
                'unit_price'   => 825.00,
                'page_url'     => 'active-purified-enzymes/fmn-reductase',
            ],
            [
                'reference'    => 'K0700-003-12',
                'name'         => 'Fish Freshness Assay Kit',
                'product_unit' => '12 samples (microplate reader)',
                'unit_price'   => 420.00,
                'page_url'     => 'dietary-nucleotides-assay-kits/fish-freshness-assay-kit',
            ],
            [
                'reference'    => 'E-Nov5-100',
                'name'         => 'Human Adenosine Kinase',
                'product_unit' => '100 mUnits',
                'unit_price'   => 355.00,
                'page_url'     => 'active-purified-enzymes/recombinant-adenosine-kinase',
            ],
            [
                'reference'    => 'E-Nov5-200',
                'name'         => 'Human Adenosine Kinase',
                'product_unit' => '200 mUnits',
                'unit_price'   => 625.00,
                'page_url'     => 'active-purified-enzymes/recombinant-adenosine-kinase',
            ],
            [
                'reference'    => 'E-Nov3-500',
                'name'         => 'Human Deoxycytidine Kinase',
                'product_unit' => '500 mUnits',
                'unit_price'   => 295.00,
                'page_url'     => 'active-purified-enzymes/recombinant-deoxycytidine-kinase',
            ],
            [
                'reference'    => 'E-Nov3-2000',
                'name'         => 'Human Deoxycytidine Kinase',
                'product_unit' => '2 Units',
                'unit_price'   => 945.00,
                'page_url'     => 'active-purified-enzymes/recombinant-deoxycytidine-kinase',
            ],
            [
                'reference'    => 'S1200-04',
                'name'         => 'HPLC-UV Cellular Nucleotides Analysis Service',
                'product_unit' => 'per sample',
                'unit_price'   => 350.00,
                'page_url'     => 'analytical-services/cellular-nucleotides-analysis',
            ],
            [
                'reference'    => 'E-Nov6-50',
                'name'         => "Human cytosolic 5'-nucleotidase II",
                'product_unit' => '50 mUnits',
                'unit_price'   => 350.00,
                'page_url'     => 'active-purified-enzymes/cn-ii',
            ],
            [
                'reference'    => 'E-Nov6-100',
                'name'         => "Human cytosolic 5'-nucleotidase II",
                'product_unit' => '100 mUnits',
                'unit_price'   => 560.00,
                'page_url'     => 'active-purified-enzymes/cn-ii',
            ],
            [
                'reference'    => '0700-06-12',
                'name'         => 'PRECICE® Fishmeal IMP Assay Kit',
                'product_unit' => 'microplate reader, 12 samples',
                'unit_price'   => 190.00,
                'page_url'     => 'freshness-assay-kits/fishmeal-imp-assay-kit',
            ],
            [
                'reference'    => '0700-06-10',
                'name'         => 'PRECICE® Fishmeal IMP Assay Kit',
                'product_unit' => 'spectrophotometer, 10 samples',
                'unit_price'   => 250.00,
                'page_url'     => 'freshness-assay-kits/fishmeal-imp-assay-kit',
            ],
            [
                'reference'    => 'E-Nov1-100',
                'name'         => 'Human IMPDH Type 2',
                'product_unit' => '100 mUnits',
                'unit_price'   => 295.00,
                'page_url'     => 'active-purified-enzymes/human-recombinant-impdh',
            ],
            [
                'reference'    => 'E-Nov1-250',
                'name'         => 'Human IMPDH Type 2',
                'product_unit' => '250 mUnits',
                'unit_price'   => 550.00,
                'page_url'     => 'active-purified-enzymes/human-recombinant-impdh',
            ],
            [
                'reference'    => 'Screening E-Nov1',
                'name'         => 'Human Recombinant IMPDH2 Screening Service',
                'product_unit' => 'IC50 determination per compound (duplicate, with MPA control)',
                'unit_price'   => 590.00,
                'page_url'     => 'active-purified-enzymes/human-recombinant-impdh',
            ],
        ];

        foreach ($products as $data) {
            Product::updateOrCreate(
                ['reference' => $data['reference']],
                $data
            );
        }

        $this->command->info('23 products seeded.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $templateCount = DocumentType::where('active', true)->count();
        $customerCount = Customer::count();
        $productCount  = Product::count();
        $documentCount = Document::count();
        $recentDocs    = Document::with(['documentType', 'customer'])
                            ->latest()->take(8)->get();

        // Per-type stats: count by status for each document type
        $typeStats = DocumentType::where('active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($type) {
                $docs = Document::where('document_type_id', $type->id)
                    ->selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status');

                return [
                    'type'     => $type,
                    'total'    => $docs->sum(),
                    'statuses' => $docs->toArray(),
                ];
            })
            ->filter(fn($s) => $s['total'] > 0);

        return view('dashboard', compact(
            'templateCount',
            'customerCount',
            'productCount',
            'documentCount',
            'recentDocs',
            'typeStats'
        ));
    }
}

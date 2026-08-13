<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->paginate(20);
        return view('products.index', compact('products'));
    }

    /**
     * Return products as JSON (used by the searchable form picker).
     * Supports ?q= to filter by reference / name.
     */
    public function list(Request $request)
    {
        $query = Product::query()->orderBy('name');

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $products = $query->limit(50)->get([
            'id', 'reference', 'name', 'description', 'product_unit', 'unit_price',
        ]);

        return response()->json($products->map(function ($p) {
            return [
                'id'               => $p->id,
                'reference'        => $p->reference,
                'name'             => $p->name,
                'description'      => $p->description,
                'product_unit'     => $p->product_unit,
                'unit_price'       => $p->unit_price,
                'formatted_price'  => $p->formatted_price,
            ];
        }));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference'    => ['nullable', 'string', 'max:80'],
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'product_unit' => ['nullable', 'string', 'max:255'],
            'unit_price'   => ['required', 'numeric', 'min:0'],
            'page_url'     => ['nullable', 'url', 'max:255'],
        ]);

        $product = Product::create($validated);

        if ($request->expectsJson()) {
            return response()->json($product, 201);
        }

        return redirect()->route('products.index')->with('success', 'Product saved.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'reference'    => ['nullable', 'string', 'max:80'],
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'product_unit' => ['nullable', 'string', 'max:255'],
            'unit_price'   => ['required', 'numeric', 'min:0'],
            'page_url'     => ['nullable', 'url', 'max:255'],
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Product updated.');
    }
}

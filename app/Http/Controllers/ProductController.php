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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference'    => ['required', 'string', 'max:80'],
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
            'reference'    => ['required', 'string', 'max:80'],
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

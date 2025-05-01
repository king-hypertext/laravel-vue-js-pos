<?php

namespace App\Http\Controllers;

use App\Imports\ProductsImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('prices', 'currentPrice')->orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data =  $request->validate([
            'name' => 'required|unique:products,name|string|max:255',
            'price' => 'required|numeric|min:0.01|gt:cost_price',
            'cost_price' => 'required|numeric|min:0.01|lte:price',
            'reorder_level' => 'nullable|integer',
            'location' => 'nullable|string|max:255',
            'expiry_date' => 'required|date',
            'bar_code' => 'nullable|string|max:255',
            'category' => ['nullable', 'string', 'max:255'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            // 'description' => 'nullable|string',
        ]);

        // $data['expiry_date'] = now()->addYears(2);
        $product = Product::create($data);
        $product->prices()->create(
            [
                'price' => $request->price,
                'cost_price' => $request->cost_price ?? 0
            ]
        );
        // $products = Product::with('currentPrice','prices')->orderByDesc('created')->get();
        return response()->json([
            'success' => true,
            'data' => $product->fresh('currentPrice', 'prices'),
            // 'data' => $products
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return $product->load('currentPrice');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $data =  $request->validate([
            'name' => 'required|string|max:255|unique:products,name,' . $product->id,
            'price' => 'required|numeric|min:0.01|gt:cost_price',
            'cost_price' => 'required|numeric|min:0.01|lte:price',
            'reorder_level' => 'nullable|integer',
            'location' => 'nullable|string|max:255',
            'expiry_date' => 'required|date',
            'bar_code' => 'nullable|string|max:255',
            'category' => ['nullable', 'string', 'max:255'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
        ]);

        $product->update($data);
        $product->prices()->updateOrCreate([
            'price' => $request->price ?? $product->currentPrice?->price,
            'cost_price' => $request->cost_price ?? $product->currentPrice?->cost_price
        ]);
        return response()->json([
            'success' => true,
            'data' => $product->load('currentPrice')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        abort_unless(request()->user()->role == 'manager', 403, 'You are not allowed to perform this action');
        $product->delete();
        return response()->noContent();
        // return redirect()->route('products.index')->with('success', 'Item deleted successfully.');
    }
    public function productExists(Request $request, Product $product)
    {
        $name = $request->name;
        if (!$name) {
            return false;
        }
        $data = $product->firstWhere('name', $name)->exists();
        // dd($data);
        if (!$data) {
            return false;
        }
        return true;
    }
    public function importProduct(Request $request)
    {
        $data = $request->validate([
            'template' => 'required|file|mimes:xlsx,xls',
        ]);
        $xlsx = $request->file('template')->store('imports', 'public');
        (new ProductsImport)->import(storage_path('app/public/' . $xlsx));
        return redirect(route('products.index'))->with('success', 'Product successfully imported');
    }
    public function updateQuantity(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:1'
        ]);
        $product->increment('quantity', $request->quantity);
        return response()->json([
            'success' => true,
            'message' => 'quantity updated succesfully',
            'quantity' => $product->quantity
        ]);
    }
    public function updatePrice(Request $request, Product $product)
    {
        $request->validate([
            'price' => 'required|numeric|min:1'
        ]);
        if ($product->currentPrice) {
            $product->currentPrice->price = $request->price;
            $product->currentPrice->save();
        } else {
            $product->prices()->updateOrCreate([
                'price' => $request->price
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'price updated succesfully',
            'price' => $product->currentPrice->price
        ]);
    }
    public function updateName(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        $product->update([
            'name' => $request->name
        ]);
        return response()->json([
            'success' => true,
            'message' => 'product name updated succesfully',
            'name' => $product->name
        ]);
    }
}

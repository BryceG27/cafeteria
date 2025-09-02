<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductType;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Products/Index', [
            'products' => Product::with('category', 'type')->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Products/Create', [
            'categories' => Category::where('is_active', true)->get(),
            'types' => ProductType::where('is_active', true)->get(),
            'product' => new Product()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Product::validate($request);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = uniqid() . '_' . $image->getClientOriginalName();
            $image->move(public_path('products'), $imageName);
            $validate['image'] = 'products/' . $imageName;
        }

        Product::create($validate);

        return redirect()->route('products.index')->with('message', 'Prodotto creato con successo.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return Inertia::render('Products/Edit', [
            'categories' => Category::where('is_active', true)->get(),
            'types' => ProductType::where('is_active', true)->get(),
            'product' => $product->load('category', 'type')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validate = Product::validate($request);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = uniqid() . '_' . $image->getClientOriginalName();
            $image->move(public_path('products'), $imageName);
            $validate['image'] = 'products/' . $imageName;
        }

        $product->update($validate);

        return redirect()->route('products.index')->with('message', 'Prodotto aggiornato con successo.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->back()->with('message', 'Prodotto eliminato con successo.');
    }

    public function toggle_active(Product $product) {
        $product->update([
            'is_active' => !$product->is_active
        ]);

        return redirect()->back()->with('success', 'Stato prodotto aggiornato con successo.');
    }
}

<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Menu;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Menus/Index', [
            'menus' => Menu::orderBy('start_date', 'desc')->with(['products' => function($query) {
                $query->with('type');
            }])->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Menus/Create', [
            'categories' => Category::orderBy('name')->with('products')->get(),
            'products' => Product::orderBy('name')->with('category', 'type')->get()->map(function($product) {
                $product->quantity = 1;
                return $product;
            }),
            'menu' => new Menu(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Menu::validate($request);
        
        $validate['start_date'] = Carbon::create($request->start_date)->format('Y-m-d');
        $validate['end_date'] = $request->end_date ? Carbon::create($request->end_date)->format('Y-m-d') : Carbon::create($request->start_date)->endOfWeek()->format('Y-m-d');

        $menu = Menu::create($validate);
        if ($request->has('products')) {
            $menu->validate_products($request);
            $products = [];
            foreach ($request->input('products') as $product) {
                $products[$product['id']] = ['quantity' => $product['quantity']];
            }
            $menu->products()->attach($products);
        }

        return redirect()->route('menus.index')->with('success', "Menu creato con successo.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Menu $menu)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Menu $menu)
    {
        return Inertia::render('Menus/Edit', [
            'categories' => Category::orderBy('name')->with('products')->get(),
            'products' => Product::orderBy('name')->with('category', 'type')->get()->map(function($product) {
                $product->quantity = 1;
                return $product;
            }),
            'menu' => $menu->load('products.type')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu)
    {
        $validate = Menu::validate($request);
        $validate['start_date'] = Carbon::create($request->start_date)->format('Y-m-d');
        $validate['end_date'] = $request->end_date ? Carbon::create($request->end_date)->format('Y-m-d') : Carbon::create($request->start_date)->endOfWeek()->format('Y-m-d');

        $menu->update($validate);
        if ($request->has('products')) {
            $menu->validate_products($request);

            foreach ($request->input('products') as $product) {
                $products[$product['id']] = ['quantity' => $product['quantity']];   
            }

            $menu->products()->sync($products);
        }

        return redirect()->route('menus.index')->with('success', "Menu aggiornato con successo.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Menu $menu)
    {
        dd($menu);
    }
}

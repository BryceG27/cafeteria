<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use App\Models\ProductType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Inertia::render('Menus/Index', [
            'menus' => Menu::orderBy('validity_date')->with(['products' => function($query) {
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
            'product_types' => ProductType::where('is_active', true)->get(),
            'menus' => Menu::where('start_date', '>=', Carbon::now()->startOfWeek(Carbon::MONDAY)->setTimezone('Europe/Rome')->format('Y-m-d'))->where('end_date', '<=', Carbon::now()->endOfWeek(Carbon::SUNDAY)->setTimezone('Europe/Rome')->format('Y-m-d'))->with('products.type')->get(),
            'menu' => new Menu(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Menu::validate($request);
        
        $validate['validity_date'] = Carbon::create($request->validity_date)->setTimezone('Europe/Rome')->format('Y-m-d');
        $validate['start_date'] = Carbon::create($request->validity_date)->startOfWeek(Carbon::MONDAY)->setTimezone('Europe/Rome')->format('Y-m-d');
        $validate['end_date'] = Carbon::create($request->validity_date)->endOfWeek(Carbon::SUNDAY)->setTimezone('Europe/Rome')->format('Y-m-d');

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
            'product_types' => ProductType::where('is_active', true)->get(),
            'menu' => $menu->load('products.type')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu)
    {
        $validate = Menu::validate($request);
        $validate['validity_date'] = Carbon::create($request->validity_date)->setTimezone('Europe/Rome')->format('Y-m-d');
        $validate['start_date'] = Carbon::create($request->validity_date)->setTimezone('Europe/Rome')->startOfWeek(CARBON::MONDAY)->format('Y-m-d');
        $validate['end_date'] = Carbon::create($request->validity_date)->setTimezone('Europe/Rome')->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

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
        if($menu->orders()->count() > 0)
            return redirect()->route('menus.index')->withErrors("Non è possibile eliminare il menu in quanto associato ad uno o più ordini.");

        $menu->products()->detach();

        $menu->delete();

        return redirect()->route('menus.index')->with('success', "Menu eliminato con successo.");
    }

    public function toggle_active(Menu $menu) {
        $menu->update([
            'is_active' => !$menu->is_active   
        ]);

        return redirect()->route('menus.index');
    }

    public function get_test() {
        dd(Menu::all());
    }
}

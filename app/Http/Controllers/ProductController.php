<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
            $imagePath = $request->file('image')->store('products', 'public');
            $validate['image'] = $imagePath;
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
            $imagePath = $request->file('image')->store('products', 'public');
            $validate['image'] = $imagePath;
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

    public function filter(Request $request) {
        $carbon = Carbon::create($request->selectedDate)->setTimezone('Europe/Rome');

        $products = [];

        $orders = \App\Models\Order::whereNotIn('status', [0, 2])->where(function($query) use ($request, $carbon) {
            switch ((Int)$request->filterType) {
                case 0:
                    $date = $carbon->format('Y-m-d');

                    return $query->whereDate('order_date', $date);
                    break;

                case 1:
                    $dateFrom = $carbon->startOfWeek()->format('Y-m-d');
                    $dateTo = $carbon->endOfWeek()->format('Y-m-d');

                    return $query->whereBetween('order_date', [$dateFrom, $dateTo]);
                    break;

                case 2:
                    $dateFrom = $carbon->startOfMonth()->format('Y-m-d');
                    $dateTo = $carbon->endOfMonth()->format('Y-m-d');

                    return $query->whereBetween('order_date', [$dateFrom, $dateTo]);
                    break;
                
                default:
                    # code...
                    break;
            }
        })->with(['first_dish', 'second_dish', 'side_dish', 'customer'])->get()->map(function($order) use (&$products) {
            if($order->first_dish) {
                $dish = $order->first_dish->load('type');
                $dish->detail = $order->load('customer')->only(['notes', 'customer', 'order_date']);
                $products[] = $dish;
            }

            if($order->second_dish) {
                $dish = $order->second_dish->load('type');
                $dish->detail = $order->load('customer')->only(['notes', 'customer', 'order_date']);
                $products[] = $dish;
            }

            if($order->side_dish) {
                $dish = $order->side_dish->load('type');
                $dish->detail = $order->load('customer')->only(['notes', 'customer', 'order_date']);
                $products[] = $dish;
            }

        });


        $products = collect($products)->groupBy('id')->map(function($item) {
            return [
                'id' => $item[0]->id,
                'name' => $item[0]->name,
                'type' => $item[0]->type->name,
                'image' => $item[0]->image,
                'times_ordered' => count($item),
                'detail' => $item->pluck('detail')->all()
            ];
        })->sortByDesc('times_ordered')->values();
        
        return response()->json($products);
    }
}

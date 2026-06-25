<?php

namespace App\Http\Controllers;

use App\Models\SpecialMenu;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SpecialMenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('SpecialMenus/Index', [
            'menus' => SpecialMenu::with('product')->get()
        ]);
    }

    public function toggle_active(SpecialMenu $menu) {
        $menu->update([
            $menu->active = !$menu->active
        ]);

        return back()->with('message', 'Menù '  . ($menu->active ? 'attivato' : 'disattivato') . ' correttamente');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use App\Models\ItemType;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        $categories = ItemCategory::orderBy('name')->get();
        $itemTypes  = ItemType::with('category')->orderBy('type_name')->get();

        return view('admin.items', compact('categories', 'itemTypes'));
    }

    public function addCategory(Request $request)
    {
        $request->validate(['category_name' => ['required', 'string', 'max:100', 'unique:item_categories,name']]);

        ItemCategory::create(['name' => $request->category_name]);

        return redirect()->route('admin.items')->with('success', 'Category added.');
    }

    public function addType(Request $request)
    {
        $request->validate([
            'category_id' => ['required', 'exists:item_categories,id'],
            'type_name'   => ['required', 'string', 'max:100'],
            'price'       => ['required', 'numeric', 'min:0'],
        ]);

        ItemType::create($request->only('category_id', 'type_name', 'price'));

        return redirect()->route('admin.items')->with('success', 'Item type added.');
    }

    public function editType(Request $request)
    {
        $request->validate([
            'type_id'   => ['required', 'exists:item_types,id'],
            'type_name' => ['required', 'string', 'max:100'],
            'price'     => ['required', 'numeric', 'min:0'],
        ]);

        ItemType::findOrFail($request->type_id)->update([
            'type_name' => $request->type_name,
            'price'     => $request->price,
        ]);

        return redirect()->route('admin.items')->with('success', 'Item updated.');
    }

    public function deleteType(Request $request)
    {
        $request->validate(['type_id' => ['required', 'exists:item_types,id']]);
        ItemType::findOrFail($request->type_id)->delete();

        return redirect()->route('admin.items')->with('success', 'Item type deleted.');
    }

    public function deleteCategory(Request $request)
    {
        $request->validate(['cat_id' => ['required', 'exists:item_categories,id']]);
        ItemCategory::findOrFail($request->cat_id)->delete();

        return redirect()->route('admin.items')->with('success', 'Category and all its types deleted.');
    }
}

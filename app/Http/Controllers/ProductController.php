<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        //get data products
        $products = DB::table('products')
            ->when($request->input('name'), function ($query, $name) {
                return $query->where('name', 'like', '%' . $name . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        //sort by created_at desc

        return view('pages.products.index', compact('products'));
    }

    public function create()
    {
        return view('pages.products.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|min:3|unique:products',
        'price' => 'required|integer',
        'stock' => 'required|integer',
        'category' => 'required|in:food,drink,snack,school,other',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    $product = new \App\Models\Product;
    $product->name = $request->name;
    $product->price = (int) $request->price;
    $product->stock = (int) $request->stock;
    $product->category = $request->category;
    $product->save();

    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imagePath = $image->storeAs('public/products', $product->id . '.' . $image->getClientOriginalExtension());
        $product->image = 'products/' . $product->id . '.' . $image->getClientOriginalExtension(); // Simpan path relatif
        $product->save();
    }


    return redirect()->route('product.index')->with('success', 'Product successfully created');
}


    public function edit($id)
    {
        $product = \App\Models\Product::findOrFail($id);
        return view('pages.products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $product = \App\Models\Product::findOrFail($id);
        $product->update($data);
        return redirect()->route('product.index')->with('success', 'Product successfully updated');
    }

    public function destroy($id)
    {
        $product = \App\Models\Product::findOrFail($id);
        $product->delete();
        return redirect()->route('product.index')->with('success', 'Product successfully deleted');
    }
}

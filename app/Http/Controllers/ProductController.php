<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get data products berdasarkan kasir_id dari user yang sedang login
        $products = DB::table('products')
            ->where('kasir_id', $user->id) // Filter produk berdasarkan kasir_id dari user yang sedang login
            ->when($request->input('name'), function ($query, $name) {
                return $query->where('name', 'like', '%' . $name . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pages.products.index', compact('products'));
    }

    public function create()
    {
        return view('pages.products.create');
    }
    public function store(Request $request)
    {
        // Validasi data request
        $request->validate([
            'name' => 'required|min:3|unique:products',
            'price' => 'required|integer',
            'stock' => 'required|integer',
            'category' => 'required|in:food,drink,snack,school,other',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            // 'kasir_id' => 'required|integer', // Hapus validasi ini karena kita akan menggunakan ID user
        ]);

        // Ambil user yang sedang login
        $user = Auth::user();

        // Buat produk baru
        $product = new \App\Models\Product;
        $product->name = $request->name;
        $product->price = (int) $request->price;
        $product->stock = (int) $request->stock;
        $product->category = $request->category;
        $product->kasir_id = $user->id; // Set kasir_id dengan ID user yang sedang login
        $product->save();

        // Proses penyimpanan gambar jika ada
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->storeAs('public/products', $product->id . '.' . $image->getClientOriginalExtension());
            $product->image = 'products/' . $product->id . '.' . $image->getClientOriginalExtension(); // Simpan path relatif
            $product->save();
        }

        // Redirect dengan pesan sukses
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

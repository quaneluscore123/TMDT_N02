<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    public function index(Request $request)
    {
        $ids = $request->input('products', []);
        
        if (empty($ids)) {
            $ids = session('compare_products', []);
        } else {
            session(['compare_products' => $ids]);
        }

        $products = Product::whereIn('id', $ids)
            ->with(['category', 'images', 'reviews'])
            ->get();

        return view('pages.compare', compact('products'));
    }

    public function add(Request $request, Product $product)
    {
        $compareProducts = session('compare_products', []);
        
        if (count($compareProducts) >= 4) {
            return back()->with('error', 'Chỉ có thể so sánh tối đa 4 sản phẩm!');
        }

        if (!in_array($product->id, $compareProducts)) {
            $compareProducts[] = $product->id;
            session(['compare_products' => $compareProducts]);
        }

        return back()->with('success', 'Đã thêm vào danh sách so sánh!');
    }

    public function remove(Request $request, Product $product)
    {
        $compareProducts = session('compare_products', []);
        $compareProducts = array_filter($compareProducts, fn($id) => $id != $product->id);
        session(['compare_products' => array_values($compareProducts)]);

        return back()->with('success', 'Đã xóa khỏi danh sách so sánh!');
    }

    public function clear()
    {
        session()->forget('compare_products');
        return back()->with('success', 'Đã xóa danh sách so sánh!');
    }
}

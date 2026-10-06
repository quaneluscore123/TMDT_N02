<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    public function index(Request $request)
    {
        // Chỉ nhận ID số nguyên, tối đa 4 sản phẩm (giống giới hạn khi thêm bằng POST)
        $ids = collect((array) $request->input('products', []))
            ->filter(fn ($id) => is_scalar($id) && ctype_digit((string) $id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->take(4)
            ->values()
            ->all();

        if (empty($ids)) {
            $ids = session('compare_products', []);
        } else {
            session(['compare_products' => $ids]);
        }

        $products = Product::whereIn('id', $ids)
            ->with('category')->withCardData()
            ->get();

        return view('pages.compare', compact('products'));
    }

    public function add(Request $request, Product $product)
    {
        $compareProducts = session('compare_products', []);

        if (count($compareProducts) >= 4) {
            $message = 'Chỉ có thể so sánh tối đa 4 sản phẩm!';

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        if (in_array($product->id, $compareProducts)) {
            $message = 'Sản phẩm đã có trong danh sách so sánh!';

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $compareProducts[] = $product->id;
        session(['compare_products' => $compareProducts]);

        $message = 'Đã thêm vào danh sách so sánh!';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'compare_count' => count($compareProducts),
            ]);
        }

        return back()->with('success', $message);
    }

    public function remove(Request $request, Product $product)
    {
        $compareProducts = session('compare_products', []);
        $compareProducts = array_filter($compareProducts, fn ($id) => $id != $product->id);
        session(['compare_products' => array_values($compareProducts)]);

        return back()->with('success', 'Đã xóa khỏi danh sách so sánh!');
    }

    public function clear()
    {
        session()->forget('compare_products');

        return back()->with('success', 'Đã xóa danh sách so sánh!');
    }
}

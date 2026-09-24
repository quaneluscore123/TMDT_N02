<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category', 'images');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($categoryId = $request->input('category')) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'brand' => 'nullable|string',
            'image' => 'nullable|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['status'] = $request->input('status', 'active');

        $product = Product::create(collect($validated)->only(['name', 'slug', 'description', 'price', 'sale_price', 'category_id', 'stock', 'brand', 'status'])->toArray());

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images/products', 'public');

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Thêm sản phẩm thành công!');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:products,name,' . $product->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'brand' => 'nullable|string',
            'image' => 'nullable|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'sometimes|in:active,inactive,out_of_stock',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $product->update(collect($validated)->only(['name', 'slug', 'description', 'price', 'sale_price', 'category_id', 'stock', 'brand', 'status'])->toArray());

        $this->syncVariants($product, $request);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images/products', 'public');

            $oldImages = $product->images()->where('is_primary', true)->get();

            $product->images()->where('is_primary', true)->delete();

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
                'is_primary' => true,
                'sort_order' => 0,
            ]);

            foreach ($oldImages as $oldImage) {
                $stillUsed = ProductImage::where('image_path', $oldImage->image_path)
                    ->where('id', '!=', $oldImage->id)
                    ->exists();

                if (! $stillUsed && str_starts_with($oldImage->image_path, 'images/products/')) {
                    Storage::disk('public')->delete($oldImage->image_path);
                }
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Cập nhật sản phẩm thành công!');
    }

    private function syncVariants(Product $product, Request $request): void
    {
        if (! $request->has('variants')) {
            return;
        }

        $rows = $request->input('variants', []);
        $keepIds = [];

        foreach ($rows as $row) {
            $size = trim((string) ($row['size'] ?? ''));
            $color = trim((string) ($row['color'] ?? ''));
            $stock = (int) ($row['stock'] ?? 0);
            $price = $row['price'] !== null && $row['price'] !== '' ? (int) $row['price'] : null;
            $id = $row['id'] ?? null;

            if ($size === '' && $color === '') {
                continue;
            }

            $variant = $id
                ? ProductVariant::where('product_id', $product->id)->find($id)
                : ProductVariant::where('product_id', $product->id)
                    ->where('size', $size)
                    ->where('color', $color)
                    ->first();

            if ($variant) {
                $variant->update([
                    'size' => $size ?: null,
                    'color' => $color ?: null,
                    'stock' => $stock,
                    'price' => $price,
                ]);
            } else {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'size' => $size ?: null,
                    'color' => $color ?: null,
                    'stock' => $stock,
                    'price' => $price,
                ]);
            }

            $keepIds[] = $variant->id;
        }

        ProductVariant::where('product_id', $product->id)
            ->when(! empty($keepIds), fn ($q) => $q->whereNotIn('id', $keepIds))
            ->delete();

        if ($product->variants()->exists()) {
            $product->update([
                'stock' => (int) $product->variants()->sum('stock'),
            ]);
        }
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Đã xóa sản phẩm!');
    }

    public function toggleActive(Product $product)
    {
        $newStatus = $product->status === 'active' ? 'inactive' : 'active';
        $product->update(['status' => $newStatus]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $product->fresh()->status,
            ]);
        }

        return back()->with('success', 'Cập nhật trạng thái thành công!');
    }

    public function addImages(Request $request, Product $product)
    {
        $request->validate([
            'images' => 'required|array|min:1',
            'images.*' => 'mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $hasPrimary = $product->images()->where('is_primary', true)->exists();
        $sortBase = $product->images()->max('sort_order') ?? -1;

        foreach ($request->file('images') as $i => $file) {
            $path = $file->store('images/products', 'public');

            $isPrimary = ! $hasPrimary && $i === 0;

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
                'is_primary' => $isPrimary,
                'sort_order' => $sortBase + $i + 1,
            ]);

            $hasPrimary = $hasPrimary || $isPrimary;
        }

        return back()->with('success', 'Đã thêm '.count($request->file('images')).' ảnh!');
    }

    public function deleteImage(Product $product, ProductImage $image)
    {
        if ($image->product_id !== $product->id) {
            abort(403);
        }

        $wasPrimary = $image->is_primary;

        $stillUsed = ProductImage::where('image_path', $image->image_path)
            ->where('id', '!=', $image->id)
            ->exists();

        if (! $stillUsed && str_starts_with($image->image_path, 'images/products/')) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        // Nếu ảnh vừa xoá là ảnh chính → tự động chọn ảnh đầu tiên còn lại làm primary
        if ($wasPrimary) {
            $product->images()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
        }

        return back()->with('success', 'Đã xoá ảnh!');
    }
}

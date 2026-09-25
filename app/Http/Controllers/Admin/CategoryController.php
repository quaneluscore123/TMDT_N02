<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')
            ->with('parent')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:1024',
            'sort_order' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|integer|exists:categories,id',
        ]);

        if (($error = $this->validateParent(null, $validated['parent_id'] ?? null)) !== null) {
            return back()->withErrors(['parent_id' => $error])->withInput();
        }

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Thêm danh mục thành công!');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:1024',
            'sort_order' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|integer|exists:categories,id',
        ]);

        $parentId = $validated['parent_id'] ?? null;

        if ($parentId !== null && (int) $parentId === (int) $category->id) {
            return back()->withErrors(['parent_id' => 'Không thể tự chọn chính danh mục này làm cha.'])->withInput();
        }

        if (($error = $this->validateParent($category->id, $parentId)) !== null) {
            return back()->withErrors(['parent_id' => $error])->withInput();
        }

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $oldImage = $category->image;

        $category->update($validated);

        if ($request->hasFile('image') && $oldImage && $oldImage !== $category->image) {
            $this->deleteImageFile($oldImage);
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Cập nhật danh mục thành công!');
    }

    private function deleteImageFile(?string $path): void
    {
        if ($path && str_starts_with($path, 'categories/')) {
            Storage::disk('public')->delete($path);
        }
    }

    private function validateParent(?int $categoryId, $parentId): ?string
    {
        if ($parentId === null) {
            return null;
        }

        if ($categoryId !== null && (int) $parentId === $categoryId) {
            return 'Không thể tự chọn chính danh mục này làm cha.';
        }

        $parent = Category::find($parentId);

        if ($parent && $parent->parent_id !== null) {
            return 'Chỉ được chọn danh mục cấp 1 làm cha (tối đa 2 cấp).';
        }

        return null;
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Không thể xóa danh mục đang có sản phẩm! Hãy chuyển sản phẩm sang danh mục khác trước.');
        }

        $this->deleteImageFile($category->image);

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Đã xóa danh mục!');
    }
}

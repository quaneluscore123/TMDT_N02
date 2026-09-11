<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->where('status', 'active');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::where('status', 'active')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        if ($product->status !== 'active') {
            abort(404);
        }

        $product->load('category', 'images');
        $relatedProducts = Product::with('category')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->limit(4)
            ->get();

        $reviews = $product->reviews()->with('user')->latest()->paginate(5);

        $canReview   = false;
        $hasReviewed = false;

        if (auth()->check()) {
            $user = auth()->user();
            $hasReviewed = $user->reviews()->where('product_id', $product->id)->exists();

            if (!$hasReviewed) {
                $canReview = $user->orders()
                    ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
                    ->where('status', 'completed')
                    ->exists();
            }
        }

        // SEO Meta Tags
        $metaTitle = $product->name . ' - SocialShop';
        $metaDescription = Str::limit(strip_tags($product->description ?? $product->name), 160);
        $metaImage = $product->image_url ?? asset('images/og-image.jpg');

        // JSON-LD Structured Data
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $metaDescription,
            'image' => $metaImage,
            'sku' => $product->sku,
            'brand' => [
                '@type' => 'Brand',
                'name' => $product->brand ?? 'SocialShop',
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $product->effectivePrice() / 1000,
                'priceCurrency' => 'VND',
                'availability' => $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => 'SocialShop',
                ],
            ],
            'aggregateRating' => $product->reviews_count > 0 ? [
                '@type' => 'AggregateRating',
                'ratingValue' => $product->average_rating,
                'reviewCount' => $product->reviews_count,
            ] : null,
        ];

        return view('products.show', compact(
            'product', 'relatedProducts', 'reviews', 'canReview', 'hasReviewed',
            'metaTitle', 'metaDescription', 'metaImage', 'jsonLd'
        ));
    }
}

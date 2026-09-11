<x-layouts.app title="So sánh sản phẩm - SocialShop">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="font-serif text-3xl text-[#3d3d3d] mb-8">So sánh sản phẩm</h1>

        @if($products->isEmpty())
            <div class="text-center py-16">
                <p class="text-gray-500 mb-4">Chưa có sản phẩm nào để so sánh</p>
                <a href="{{ route('products.index') }}" class="bg-[#b8847e] text-white px-6 py-2 rounded-lg hover:bg-[#a6736d] transition">
                    Tiếp tục mua sắm
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="text-left p-4 bg-[#efe8e3] font-serif text-[#3d3d3d]">Thông tin</th>
                            @foreach($products as $product)
                            <th class="p-4 bg-[#efe8e3] min-w-[200px]">
                                <div class="text-center">
                                    <img src="{{ $product->image_url ?? asset('images/placeholder.jpg') }}" 
                                         alt="{{ $product->name }}" 
                                         class="w-32 h-32 object-cover mx-auto mb-2 rounded-lg">
                                    <a href="{{ route('products.show', $product->slug) }}" 
                                       class="font-serif text-[#3d3d3d] hover:text-[#b8847e] block mb-2">
                                        {{ $product->name }}
                                    </a>
                                    <button onclick="removeFromCompare({{ $product->id }})" 
                                            class="text-red-500 hover:text-red-700 text-sm">
                                        Xóa
                                    </button>
                                </div>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-[#efe8e3]">
                            <td class="p-4 font-medium text-[#3d3d3d]">Giá</td>
                            @foreach($products as $product)
                            <td class="p-4 text-center">
                                @if($product->sale_price)
                                    <span class="text-[#b8847e] font-bold text-lg">{{ number_format($product->sale_price, 0, ',', '.') }}₫</span>
                                    <span class="text-gray-400 line-through text-sm block">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                                @else
                                    <span class="text-[#b8847e] font-bold text-lg">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        <tr class="border-b border-[#efe8e3]">
                            <td class="p-4 font-medium text-[#3d3d3d]">Danh mục</td>
                            @foreach($products as $product)
                            <td class="p-4 text-center text-gray-600">{{ $product->category->name ?? '-' }}</td>
                            @endforeach
                        </tr>
                        <tr class="border-b border-[#efe8e3]">
                            <td class="p-4 font-medium text-[#3d3d3d]">Thương hiệu</td>
                            @foreach($products as $product)
                            <td class="p-4 text-center text-gray-600">{{ $product->brand ?? '-' }}</td>
                            @endforeach
                        </tr>
                        <tr class="border-b border-[#efe8e3]">
                            <td class="p-4 font-medium text-[#3d3d3d]">Tồn kho</td>
                            @foreach($products as $product)
                            <td class="p-4 text-center">
                                @if($product->stock > 0)
                                    <span class="text-green-600">Còn hàng ({{ $product->stock }})</span>
                                @else
                                    <span class="text-red-500">Hết hàng</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        <tr class="border-b border-[#efe8e3]">
                            <td class="p-4 font-medium text-[#3d3d3d]">Đánh giá</td>
                            @foreach($products as $product)
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center">
                                    <span class="text-yellow-500 mr-1">★</span>
                                    <span class="text-gray-600">{{ $product->average_rating }} ({{ $product->reviews_count }})</span>
                                </div>
                            </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="p-4 font-medium text-[#3d3d3d]">Thao tác</td>
                            @foreach($products as $product)
                            <td class="p-4 text-center">
                                <form action="{{ route('cart.add', $product->slug) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" 
                                            class="bg-[#b8847e] text-white px-4 py-2 rounded-lg hover:bg-[#a6736d] transition text-sm">
                                        Thêm vào giỏ
                                    </button>
                                </form>
                            </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <script>
    function removeFromCompare(productId) {
        fetch(`/compare/remove/${productId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        }).then(() => location.reload());
    }
    </script>
</x-layouts.app>

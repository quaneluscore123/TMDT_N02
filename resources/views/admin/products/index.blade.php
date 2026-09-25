<x-layouts.admin :title="'Quản lý sản phẩm'" :header="'Sản phẩm'">

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <form action="{{ route('admin.products.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 flex-1">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Tìm sản phẩm..."
                       class="w-full pl-10 pr-4 py-2.5 border border-[#efe8e3] rounded-lg bg-white focus:ring-2 focus:ring-[#c9a9a6] focus:border-transparent transition-all text-sm">
                <svg class="absolute left-3 top-3 h-4 w-4 text-[#9a9490]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <select name="category"
                    class="border border-[#efe8e3] rounded-lg px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-[#c9a9a6]">
                <option value="">Tất cả danh mục</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit"
                    class="bg-[#b8847e] text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-[#a6736d] transition-colors">
                Lọc
            </button>
        </form>

        <a href="{{ route('admin.products.create') }}"
           class="inline-flex items-center gap-2 bg-[#b8847e] text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-[#a6736d] transition-colors whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Thêm sản phẩm
        </a>
    </div>

    {{-- Products Table --}}
    @if($products->count())
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-[#faf7f4]">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Sản phẩm</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Danh mục</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Giá</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Tồn kho</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Trạng thái</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#efe8e3]">
                        @foreach($products as $product)
                            <tr class="hover:bg-[#faf7f4] transition-colors" x-data="{ toggling: false }">
                                {{-- Product Name + Image --}}
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($product->image_url)
                                            <img src="{{ $product->image_url }}"
                                                 alt="{{ $product->name }}"
                                                 class="w-12 h-12 rounded-lg object-cover flex-shrink-0">
                                        @else
                                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-[#f5f0ec] to-[#efe8e3] flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-[#c9a9a6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="font-semibold text-[#3d3d3d] truncate max-w-[220px]">{{ $product->name }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Category --}}
                                <td class="px-5 py-3">
                                    <span class="text-[#9a9490]">{{ $product->category->name ?? '—' }}</span>
                                </td>

                                {{-- Price --}}
                                <td class="px-5 py-3">
                                    @if($product->sale_price)
                                        <p class="font-bold text-[#b8847e]">{{ number_format($product->sale_price, 0, ',', '.') }}₫</p>
                                        <p class="text-xs text-gray-400 line-through">{{ number_format($product->price, 0, ',', '.') }}₫</p>
                                    @else
                                        <p class="font-bold text-[#b8847e]">{{ number_format($product->price, 0, ',', '.') }}₫</p>
                                    @endif
                                </td>

                                {{-- Stock --}}
                                <td class="px-5 py-3">
                                    <span class="font-medium {{ $product->stock <= 0 ? 'text-red-500' : 'text-[#3d3d3d]' }}">
                                        {{ $product->stock }}
                                    </span>
                                </td>

                                {{-- Status Toggle --}}
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                                        {{ $product->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $product->status === 'active' ? 'Đang bán' : 'Ngừng bán' }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="px-5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form action="{{ route('admin.products.toggle', $product) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 text-amber-600 hover:text-amber-700 font-medium text-sm transition-colors"
                                                    title="{{ $product->status === 'active' ? 'Ngừng bán' : 'Bán lại' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.products.edit', $product) }}"
                                           class="inline-flex items-center gap-1 text-[#b8847e] hover:text-[#a6736d] font-medium text-sm transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Sửa
                                        </a>
                                        <a href="{{ route('admin.products.edit', $product) }}#gallery"
                                           class="inline-flex items-center gap-1 text-[#b8847e] hover:text-[#a6736d] font-medium text-sm transition-colors"
                                           title="Quản lý ảnh sản phẩm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Ảnh
                                        </a>
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                              onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 text-red-500 hover:text-red-700 font-medium text-sm transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="px-5 py-4 border-t border-[#efe8e3]">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-20 animate-fade-in-up">
            <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-[#f5f0ec] to-[#efe8e3] rounded-full flex items-center justify-center">
                <svg class="h-12 w-12 text-[#c9a9a6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h2 class="font-serif text-xl font-semibold text-[#3d3d3d] mb-2">Chưa có sản phẩm nào</h2>
            <p class="text-[#9a9490] mb-6 max-w-sm mx-auto text-sm">Bắt đầu bằng cách thêm sản phẩm đầu tiên vào cửa hàng.</p>
            <a href="{{ route('admin.products.create') }}"
               class="inline-flex items-center gap-2 bg-[#b8847e] text-white px-6 py-2.5 rounded-lg font-medium text-sm hover:bg-[#a6736d] transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Thêm sản phẩm
            </a>
        </div>
    @endif

</x-layouts.admin>

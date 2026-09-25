<x-layouts.admin :title="'Sửa sản phẩm'" :header="'Chỉnh sửa: ' . $product->name">

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.products._form', ['product' => $product])
    </form>

    {{-- Gallery (Tách ra ngoài form chính để tránh lỗi form lồng nhau) --}}
    <div id="gallery" class="mt-8 bg-white rounded-xl shadow-sm border border-[#efe8e3] p-6 scroll-mt-20">
        <h3 class="font-serif text-lg font-semibold text-[#3d3d3d] mb-4">Ảnh sản phẩm</h3>

        {{-- Danh sách ảnh hiện tại --}}
        @php $images = $product->images()->orderByDesc('is_primary')->orderBy('sort_order')->get(); @endphp

        @if($images->isNotEmpty())
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-8 gap-3 mb-5">
                @foreach($images as $img)
                    <div class="relative group">
                        <img src="{{ $img->url }}"
                             alt="Ảnh sản phẩm"
                             class="w-full aspect-square object-cover rounded-lg border border-[#efe8e3]">

                        @if($img->is_primary)
                            <span class="absolute top-1 left-1 bg-[#b8847e] text-white text-[9px] font-bold px-1.5 py-0.5 rounded leading-tight">
                                Chính
                            </span>
                        @endif

                        <form action="{{ route('admin.products.images.delete', [$product, $img]) }}"
                              method="POST"
                              onsubmit="return confirm('Xoá ảnh này?')"
                              class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-xs transition-colors shadow">
                                ×
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-[#9a9490] mb-4">Chưa có ảnh nào. Hãy tải lên ảnh bên dưới.</p>
        @endif

        {{-- Form upload thêm ảnh --}}
        <form action="{{ route('admin.products.images.add', $product) }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf
            <label class="block text-sm font-medium text-[#3d3d3d] mb-2">Thêm ảnh mới (có thể chọn nhiều)</label>
            <div class="flex items-center gap-3">
                <input type="file" name="images[]" multiple accept="image/*" required
                       class="text-sm text-[#9a9490] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#faf7f4] file:text-[#b8847e] hover:file:bg-[#efe8e3] file:cursor-pointer file:transition-colors">
                <button type="submit"
                        class="px-5 py-2 bg-[#b8847e] text-white rounded-lg text-sm font-medium hover:bg-[#a6736d] transition-colors whitespace-nowrap">
                    Tải lên ảnh
                </button>
            </div>
            @error('images.*')
                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
            @enderror
            <p class="text-xs text-[#9a9490] mt-3">JPG, PNG, GIF, WebP. Tối đa 2MB/ảnh. Ảnh đầu tiên sẽ trở thành ảnh chính nếu chưa có.</p>
        </form>
    </div>

</x-layouts.admin>

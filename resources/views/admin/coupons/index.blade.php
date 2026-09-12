<x-layouts.admin :title="'Quản lý mã giảm giá'" :header="'Mã giảm giá'">

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-[#3d3d3d]">{{ $stats['total'] }}</p>
                    <p class="text-xs text-[#9a9490]">Tổng mã</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
                    <p class="text-xs text-[#9a9490]">Đang active</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-amber-600">{{ $stats['expired'] }}</p>
                    <p class="text-xs text-[#9a9490]">Hết hạn</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['used'] }}</p>
                    <p class="text-xs text-[#9a9490]">Lượt dùng</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <form action="{{ route('admin.coupons.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 flex-1">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Tìm theo mã code..."
                       class="w-full pl-10 pr-4 py-2.5 border border-[#efe8e3] rounded-lg bg-white focus:ring-2 focus:ring-[#c9a9a6] focus:border-transparent transition-all text-sm">
                <svg class="absolute left-3 top-3 h-4 w-4 text-[#9a9490]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <select name="type"
                    class="border border-[#efe8e3] rounded-lg px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-[#c9a9a6]">
                <option value="">Tất cả loại</option>
                <option value="percent" {{ request('type') == 'percent' ? 'selected' : '' }}>Phần trăm (%)</option>
                <option value="fixed" {{ request('type') == 'fixed' ? 'selected' : '' }}>Cố định (₫)</option>
            </select>
            <select name="status"
                    class="border border-[#efe8e3] rounded-lg px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-[#c9a9a6]">
                <option value="">Tất cả trạng thái</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Hết hạn</option>
            </select>
            <button type="submit"
                    class="bg-[#b8847e] text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-[#a6736d] transition-colors">
                Lọc
            </button>
        </form>

        <a href="{{ route('admin.coupons.create') }}"
           class="inline-flex items-center gap-2 bg-[#b8847e] text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-[#a6736d] transition-colors whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Thêm mã giảm giá
        </a>
    </div>

    {{-- Bulk Actions --}}
    <div x-data="{ selected: [], showBulk: false }"
         x-effect="showBulk = selected.length > 0">
        <div x-show="showBulk" x-cloak
             class="flex items-center gap-3 mb-4 bg-blue-50 border border-blue-200 rounded-lg px-4 py-2.5">
            <span class="text-sm text-blue-700" x-text="'Đã chọn ' + selected.length + ' mã'"></span>
            <button @click="$dispatch('bulk-toggle', { ids: selected, status: 'active' })"
                    class="text-sm text-green-600 hover:text-green-800 font-medium">Bật active</button>
            <button @click="$dispatch('bulk-toggle', { ids: selected, status: 'inactive' })"
                    class="text-sm text-amber-600 hover:text-amber-800 font-medium">Tắt active</button>
            <button @click="if(confirm('Xóa các mã đã chọn?')) $dispatch('bulk-delete', { ids: selected })"
                    class="text-sm text-red-600 hover:text-red-800 font-medium">Xóa</button>
            <button @click="selected = []" class="text-sm text-gray-500 hover:text-gray-700 ml-auto">Bỏ chọn</button>
        </div>

        {{-- Coupons Table --}}
        @if($coupons->count())
            <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-[#faf7f4]">
                            <tr>
                                <th class="px-5 py-3 text-left">
                                    <input type="checkbox" x-model="selectAll"
                                           @change="selected = selectAll ? @js($coupons->pluck('id')->toArray()) : []"
                                           class="rounded border-gray-300 text-[#b8847e] focus:ring-[#c9a9a6]">
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Mã code</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Loại</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Giá trị</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Đơn tối thiểu</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Đã dùng</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Thời hạn</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Trạng thái</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#efe8e3]">
                            @foreach($coupons as $coupon)
                                @php
                                    $isExpired = $coupon->end_at && $coupon->end_at->isPast();
                                    $usagePercent = $coupon->usage_limit ? round(($coupon->used_count / $coupon->usage_limit) * 100) : 0;
                                @endphp
                                <tr class="hover:bg-[#faf7f4] transition-colors">
                                    <td class="px-5 py-3">
                                        <input type="checkbox" value="{{ $coupon->id }}" x-model="selected"
                                               class="rounded border-gray-300 text-[#b8847e] focus:ring-[#c9a9a6]">
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-[#3d3d3d] tracking-wide">{{ $coupon->code }}</span>
                                            <button onclick="navigator.clipboard.writeText('{{ $coupon->code }}'); window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Đã copy mã {{ $coupon->code }}', type: 'success' } }))"
                                                    class="text-[#9a9490] hover:text-[#b8847e] transition-colors" title="Copy mã">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold
                                            {{ $coupon->type === 'percent' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $coupon->type === 'percent' ? '%' : '₫' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="font-semibold text-[#3d3d3d]">
                                            {{ $coupon->type === 'percent' ? $coupon->value . '%' : number_format($coupon->value, 0, ',', '.') . '₫' }}
                                        </span>
                                        @if($coupon->max_discount)
                                            <p class="text-xs text-[#9a9490]">Tối đa {{ number_format($coupon->max_discount, 0, ',', '.') }}₫</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-[#9a9490]">
                                        {{ $coupon->min_order_amount > 0 ? '≥ ' . number_format($coupon->min_order_amount, 0, ',', '.') . '₫' : 'Không' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-[#3d3d3d]">{{ $coupon->used_count }}</span>
                                            @if($coupon->usage_limit)
                                                <span class="text-[#9a9490]">/ {{ $coupon->usage_limit }}</span>
                                                <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                                    <div class="h-full rounded-full {{ $usagePercent >= 100 ? 'bg-red-500' : ($usagePercent >= 80 ? 'bg-amber-500' : 'bg-green-500') }}"
                                                         style="width: {{ min($usagePercent, 100) }}%"></div>
                                                </div>
                                            @else
                                                <span class="text-[#9a9490]">/ ∞</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-xs text-[#9a9490]">
                                        @if($coupon->start_at || $coupon->end_at)
                                            @if($coupon->start_at)
                                                {{ $coupon->start_at->format('d/m/Y') }}
                                            @else
                                                ∞
                                            @endif
                                            -
                                            @if($coupon->end_at)
                                                {{ $coupon->end_at->format('d/m/Y') }}
                                            @else
                                                ∞
                                            @endif
                                            @if($isExpired)
                                                <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700 mt-1">Hết hạn</span>
                                            @endif
                                        @else
                                            Không giới hạn
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        @if($coupon->status === 'active' && !$isExpired)
                                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Active</span>
                                        @elseif($isExpired)
                                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">Hết hạn</span>
                                        @else
                                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.coupons.usage', $coupon) }}"
                                               class="inline-flex items-center gap-1.5 text-purple-600 hover:text-purple-800 font-medium text-sm transition-colors px-3 py-1.5 rounded-lg hover:bg-purple-50"
                                               title="Lịch sử sử dụng">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                </svg>
                                                <span class="hidden xl:inline">Sử dụng</span>
                                            </a>
                                            <a href="{{ route('admin.coupons.edit', $coupon) }}"
                                               class="inline-flex items-center gap-1.5 text-[#b8847e] hover:text-[#a6736d] font-medium text-sm transition-colors px-3 py-1.5 rounded-lg hover:bg-[#faf7f4]"
                                               title="Sửa">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                <span class="hidden xl:inline">Sửa</span>
                                            </a>
                                            <form action="{{ route('admin.coupons.toggle', $coupon) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1.5 text-amber-600 hover:text-amber-800 font-medium text-sm transition-colors px-3 py-1.5 rounded-lg hover:bg-amber-50"
                                                        title="{{ $coupon->status === 'active' ? 'Tắt' : 'Bật' }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                                    </svg>
                                                    <span class="hidden xl:inline">{{ $coupon->status === 'active' ? 'Tắt' : 'Bật' }}</span>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST"
                                                  onsubmit="return confirm('Xóa mã {{ $coupon->code }}?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1.5 text-red-500 hover:text-red-700 font-medium text-sm transition-colors px-3 py-1.5 rounded-lg hover:bg-red-50"
                                                        title="Xóa">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    <span class="hidden xl:inline">Xóa</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($coupons->hasPages())
                    <div class="px-5 py-4 border-t border-[#efe8e3]">
                        {{ $coupons->links() }}
                    </div>
                @endif
            </div>
        @else
            <div class="text-center py-20">
                <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-[#f5f0ec] to-[#efe8e3] rounded-full flex items-center justify-center">
                    <svg class="h-12 w-12 text-[#c9a9a6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <h2 class="font-serif text-xl font-semibold text-[#3d3d3d] mb-2">Chưa có mã giảm giá nào</h2>
                <p class="text-[#9a9490] mb-6 max-w-sm mx-auto text-sm">Bắt đầu bằng cách tạo mã giảm giá đầu tiên.</p>
                <a href="{{ route('admin.coupons.create') }}"
                   class="inline-flex items-center gap-2 bg-[#b8847e] text-white px-6 py-2.5 rounded-lg font-medium text-sm hover:bg-[#a6736d] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Thêm mã giảm giá
                </a>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('bulk-delete', async (e) => {
            const response = await fetch('{{ route("admin.coupons.bulk-delete") }}', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ ids: e.detail.ids }),
            });
            const data = await response.json();
            if (data.success) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message, type: 'success' } }));
                setTimeout(() => location.reload(), 500);
            }
        });

        document.addEventListener('bulk-toggle', async (e) => {
            const response = await fetch('{{ route("admin.coupons.bulk-toggle") }}', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ ids: e.detail.ids, status: e.detail.status }),
            });
            const data = await response.json();
            if (data.success) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message, type: 'success' } }));
                setTimeout(() => location.reload(), 500);
            }
        });
    </script>
    @endpush

</x-layouts.admin>

<x-layouts.admin :title="'Quản lý đơn hàng'" :header="'Đơn hàng'">

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 flex-1">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Tìm mã đơn hoặc tên khách..."
                       class="w-full pl-10 pr-4 py-2.5 border border-[#efe8e3] rounded-lg bg-white focus:ring-2 focus:ring-[#c9a9a6] focus:border-transparent transition-all text-sm">
                <svg class="absolute left-3 top-3 h-4 w-4 text-[#9a9490]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <button type="submit"
                    class="bg-[#b8847e] text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-[#a6736d] transition-colors">
                Tìm
            </button>
        </form>
    </div>

    {{-- Status Filter Tabs --}}
    @php
        $statuses = [
            '' => 'Tất cả',
            'pending' => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'shipping' => 'Đang giao',
            'delivered' => 'Đã giao',
            'cancelled' => 'Đã hủy',
        ];
    @endphp
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach($statuses as $value => $label)
            <a href="{{ route('admin.orders.index', array_merge(request()->except('status', 'page'), $value ? ['status' => $value] : [])) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request('status') === $value || (!request('status') && $value === '')
                          ? 'bg-[#b8847e] text-white'
                          : 'bg-white border border-[#efe8e3] text-[#3d3d3d] hover:bg-[#faf7f4]' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Orders Table --}}
    @if($orders->count())
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-[#faf7f4]">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Mã đơn</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Khách hàng</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Tổng tiền</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Trạng thái</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Ngày đặt</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#efe8e3]">
                        @foreach($orders as $order)
                            <tr class="hover:bg-[#faf7f4] transition-colors">
                                <td class="px-5 py-4 font-semibold text-[#3d3d3d]">{{ $order->order_code }}</td>
                                <td class="px-5 py-4 text-[#3d3d3d]">
                                    <p class="font-medium">{{ $order->shipping_name }}</p>
                                    <p class="text-xs text-[#9a9490]">{{ $order->shipping_phone }}</p>
                                </td>
                                <td class="px-5 py-4 font-bold text-[#b8847e]">{{ number_format($order->total, 0, ',', '.') }}₫</td>
                                <td class="px-5 py-4">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'confirmed' => 'bg-blue-100 text-blue-800',
                                            'shipping' => 'bg-rose-100 text-rose-800',
                                            'delivered' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Chờ xử lý',
                                            'confirmed' => 'Đã xác nhận',
                                            'shipping' => 'Đang giao',
                                            'delivered' => 'Đã giao',
                                            'cancelled' => 'Đã hủy',
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $statusLabels[$order->status] ?? $order->status }}
                                    </span>
                                    @if($order->payment_method !== 'cod')
                                        <span class="block mt-1 text-xs {{ $order->payment_status === 'paid' ? 'text-green-700' : ($order->payment_status === 'failed' ? 'text-red-600' : 'text-yellow-700') }}">
                                            VNPay · {{ ['paid' => 'Đã thanh toán', 'failed' => 'Thất bại'][$order->payment_status] ?? 'Chưa thanh toán' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-[#9a9490]">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                       class="inline-flex items-center gap-1 text-[#b8847e] hover:text-[#a6736d] font-medium text-sm transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Xem
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="px-5 py-4 border-t border-[#efe8e3]">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-20 animate-fade-in-up">
            <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-[#f5f0ec] to-[#efe8e3] rounded-full flex items-center justify-center">
                <svg class="h-12 w-12 text-[#c9a9a6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h2 class="font-serif text-xl font-semibold text-[#3d3d3d] mb-2">Không tìm thấy đơn hàng</h2>
            <p class="text-[#9a9490] text-sm">
                @if(request('search') || request('status'))
                    Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm.
                @else
                    Chưa có đơn hàng nào trong hệ thống.
                @endif
            </p>
        </div>
    @endif

</x-layouts.admin>

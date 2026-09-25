<x-layouts.admin :title="'Dashboard'" :header="'Tổng quan'">

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

        {{-- Doanh thu tháng --}}
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#faf7f4] flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-[#b8847e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-[#9a9490] font-medium uppercase tracking-wide">Doanh thu tháng</p>
                    <p class="text-xl font-extrabold text-[#3d3d3d] mt-0.5">{{ number_format($totalRevenue, 0, ',', '.') }}₫</p>
                </div>
            </div>
        </div>

        {{-- Đơn hàng hôm nay --}}
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#faf7f4] flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-[#b8847e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-[#9a9490] font-medium uppercase tracking-wide">Đơn hàng hôm nay</p>
                    <p class="text-xl font-extrabold text-[#3d3d3d] mt-0.5">{{ $todayOrders }}</p>
                </div>
            </div>
        </div>

        {{-- Tổng sản phẩm --}}
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#faf7f4] flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-[#b8847e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-[#9a9490] font-medium uppercase tracking-wide">Tổng sản phẩm</p>
                    <p class="text-xl font-extrabold text-[#3d3d3d] mt-0.5">{{ $totalProducts }}</p>
                </div>
            </div>
        </div>

        {{-- Tổng khách hàng --}}
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#faf7f4] flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-[#b8847e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-[#9a9490] font-medium uppercase tracking-wide">Tổng khách hàng</p>
                    <p class="text-xl font-extrabold text-[#3d3d3d] mt-0.5">{{ $totalUsers }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart + Recent Orders --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Revenue Chart --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-[#efe8e3] p-6">
            <h2 class="font-serif text-lg font-semibold text-[#3d3d3d] mb-4">Doanh thu 7 ngày gần nhất</h2>
            <div class="relative" style="height: 280px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        {{-- Order Summary --}}
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] p-6">
            <h2 class="font-serif text-lg font-semibold text-[#3d3d3d] mb-4">Tóm tắt đơn hàng</h2>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 bg-[#faf7f4] rounded-lg">
                    <span class="text-sm text-[#9a9490]">Đơn hàng tháng này</span>
                    <span class="font-bold text-[#3d3d3d]">{{ $monthOrders }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-[#faf7f4] rounded-lg">
                    <span class="text-sm text-[#9a9490]">Đơn hàng hôm nay</span>
                    <span class="font-bold text-[#3d3d3d]">{{ $todayOrders }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-[#faf7f4] rounded-lg">
                    <span class="text-sm text-[#9a9490]">Tổng sản phẩm</span>
                    <span class="font-bold text-[#3d3d3d]">{{ $totalProducts }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-[#faf7f4] rounded-lg">
                    <span class="text-sm text-[#9a9490]">Tổng người dùng</span>
                    <span class="font-bold text-[#3d3d3d]">{{ $totalUsers }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Behavior stats: top viewed + top sold + recent users --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] p-6">
            <h2 class="font-serif text-lg font-semibold text-[#3d3d3d] mb-4">Sản phẩm xem nhiều</h2>
            <div class="space-y-3">
                @forelse($topViewed as $item)
                    <div class="flex justify-between items-center p-3 bg-[#faf7f4] rounded-lg">
                        <a href="{{ route('products.show', $item->slug) }}" class="text-sm text-[#3d3d3d] hover:text-[#b8847e] truncate mr-2">{{ $item->name }}</a>
                        <span class="font-bold text-[#b8847e] text-sm whitespace-nowrap">{{ $item->views_count }} lượt</span>
                    </div>
                @empty
                    <p class="text-sm text-[#9a9490]">Chưa có lượt xem.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] p-6">
            <h2 class="font-serif text-lg font-semibold text-[#3d3d3d] mb-4">Bán chạy</h2>
            <div class="space-y-3">
                @forelse($topSelling as $item)
                    <div class="flex justify-between items-center p-3 bg-[#faf7f4] rounded-lg">
                        <a href="{{ route('products.show', $item->product?->slug) }}" class="text-sm text-[#3d3d3d] hover:text-[#b8847e] truncate mr-2">{{ $item->product?->name ?? 'SP đã xóa' }}</a>
                        <span class="font-bold text-[#b8847e] text-sm whitespace-nowrap">{{ $item->sold }} SP</span>
                    </div>
                @empty
                    <p class="text-sm text-[#9a9490]">Chưa có đơn bán.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] p-6">
            <h2 class="font-serif text-lg font-semibold text-[#3d3d3d] mb-4">Khách mới</h2>
            <div class="space-y-3">
                @forelse($recentUsers as $u)
                    <div class="flex justify-between items-center p-3 bg-[#faf7f4] rounded-lg">
                        <div class="min-w-0 mr-2">
                            <p class="text-sm font-medium text-[#3d3d3d] truncate">{{ $u->name }}</p>
                            <p class="text-xs text-[#9a9490] truncate">{{ $u->email }}</p>
                        </div>
                        <span class="text-xs text-[#9a9490] whitespace-nowrap">{{ optional($u->created_at)->format('d/m') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-[#9a9490]">Chưa có khách.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Orders Table --}}
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-[#efe8e3] overflow-hidden">
        <div class="px-6 py-4 border-b border-[#efe8e3]">
            <h2 class="font-serif text-lg font-semibold text-[#3d3d3d]">Đơn hàng gần đây</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[#faf7f4]">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Mã đơn</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Khách hàng</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Tổng tiền</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Trạng thái</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Ngày đặt</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-[#9a9490] uppercase tracking-wide">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#efe8e3]">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-[#faf7f4] transition-colors">
                            <td class="px-6 py-4 font-semibold text-[#3d3d3d]">{{ $order->order_code }}</td>
                            <td class="px-6 py-4 text-[#3d3d3d]">{{ $order->user->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 font-bold text-[#b8847e]">{{ number_format($order->total, 0, ',', '.') }}₫</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                                    @elseif($order->status === 'shipping') bg-rose-100 text-rose-800
                                    @elseif($order->status === 'delivered') bg-green-100 text-green-800
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    @switch($order->status)
                                        @case('pending') Chờ xử lý @break
                                        @case('confirmed') Đã xác nhận @break
                                        @case('shipping') Đang giao @break
                                        @case('delivered') Đã giao @break
                                        @case('cancelled') Đã hủy @break
                                        @default {{ $order->status }}
                                    @endswitch
                                </span>
                            </td>
                            <td class="px-6 py-4 text-[#9a9490]">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.orders.show', $order) }}"
                                   class="text-[#b8847e] hover:text-[#a6736d] font-medium text-sm transition-colors">
                                    Xem
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-[#9a9490]">Chưa có đơn hàng nào.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Chart.js CDN + Init --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('revenueChart');
            if (!ctx) return;

            const labels = @json(array_column($chartData, 'date'));
            const data = @json(array_column($chartData, 'total'));

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Doanh thu (₫)',
                        data: data,
                        backgroundColor: 'rgba(184, 132, 126, 0.2)',
                        borderColor: '#b8847e',
                        borderWidth: 2,
                        borderRadius: 6,
                        hoverBackgroundColor: 'rgba(184, 132, 126, 0.4)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#3d3d3d',
                            titleColor: '#faf7f4',
                            bodyColor: '#faf7f4',
                            borderColor: '#efe8e3',
                            borderWidth: 1,
                            cornerRadius: 8,
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    return new Intl.NumberFormat('vi-VN').format(context.parsed.y) + '₫';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#9a9490', font: { size: 12 } }
                        },
                        y: {
                            grid: { color: '#efe8e3' },
                            ticks: {
                                color: '#9a9490',
                                font: { size: 11 },
                                callback: function(value) {
                                    if (value >= 1000000) return (value / 1000000).toFixed(0) + 'tr';
                                    if (value >= 1000) return (value / 1000).toFixed(0) + 'k';
                                    return value;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

</x-layouts.admin>

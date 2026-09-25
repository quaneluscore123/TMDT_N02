<x-layouts.app :title="'Đơn hàng của tôi'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="font-serif text-3xl font-bold text-[#3d3d3d] mb-8">Đơn hàng của tôi</h1>

        @if($orders->count())
            <div class="space-y-4">
                @foreach($orders as $order)
                    <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] overflow-hidden hover-lift">
                        <div class="p-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $order->order_code }}</p>
                                    <p class="text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-sm font-medium
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
                            </div>

                            <div class="mt-4 flex justify-between items-center">
                                <div>
                                    <p class="text-sm text-gray-600">{{ $order->items->count() }} sản phẩm</p>
                                    <p class="text-lg font-bold text-[#b8847e]">{{ number_format($order->total, 0, ',', '.') }} ₫</p>
                                </div>
                                <a href="{{ route('orders.show', $order) }}"
                                   class="text-[#b8847e] hover:text-[#a6736d] text-sm font-medium">
                                    Xem chi tiết →
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <svg class="h-24 w-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Chưa có đơn hàng</h2>
                <p class="text-gray-600 mb-6">Bạn chưa đặt đơn hàng nào. Hãy mua sắm ngay!</p>
                <a href="{{ route('products.index') }}"
                   class="bg-[#b8847e] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#a6736d] transition">
                    Mua sắm ngay
                </a>
            </div>
        @endif
    </div>
</x-layouts.app>

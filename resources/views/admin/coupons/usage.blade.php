<x-layouts.admin :title="'Lịch sử sử dụng mã giảm giá'" :header="'Lịch sử sử dụng - ' . strtoupper($coupon->code)">
    <div class="space-y-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-[#9a9490] mt-1">Mã: <span class="font-bold text-[#b8847e]">{{ strtoupper($coupon->code) }}</span></p>
            </div>
            <a href="{{ route('admin.coupons.index') }}" class="inline-flex items-center gap-1 text-[#b8847e] hover:text-[#a6736d] text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                Quay lại
            </a>
        </div>

        {{-- Stats --}}
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-xl">
                    <p class="text-3xl font-bold text-[#b8847e]">{{ $coupon->used_count }}</p>
                    <p class="text-sm text-[#9a9490]">Đã sử dụng</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-xl">
                    <p class="text-3xl font-bold text-[#3d3d3d]">{{ $coupon->usage_limit ?: '∞' }}</p>
                    <p class="text-sm text-[#9a9490]">Giới hạn</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-xl">
                    <p class="text-3xl font-bold text-[#3d3d3d]">{{ $coupon->usage_limit ? $coupon->usage_limit - $coupon->used_count : '∞' }}</p>
                    <p class="text-sm text-[#9a9490]">Còn lại</p>
                </div>
            </div>
        </div>

        {{-- Usage History Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] overflow-hidden">
            <div class="p-5 border-b border-[#efe8e3]">
                <h3 class="font-serif text-lg font-semibold text-[#3d3d3d]">Danh sách đơn hàng đã áp dụng</h3>
            </div>

            @if($usages->isEmpty())
                <div class="p-10 text-center">
                    <svg class="w-16 h-16 text-[#efe8e3] mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                    <p class="text-[#9a9490]">Chưa có đơn hàng nào sử dụng mã này</p>
                </div>
            @else
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-[#efe8e3] bg-gray-50">
                            <th class="px-5 py-3 text-left text-xs font-medium text-[#9a9490] uppercase">Mã đơn hàng</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-[#9a9490] uppercase">Khách hàng</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-[#9a9490] uppercase">Giá trị đơn</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-[#9a9490] uppercase">Số tiền giảm</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-[#9a9490] uppercase">Thời gian sử dụng</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#efe8e3]">
                        @foreach($usages as $usage)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <a href="{{ route('admin.orders.show', $usage->order_id) }}" class="text-[#b8847e] hover:text-[#a6736d] font-bold">#{{ str_pad($usage->order_id, 8, '0', STR_PAD_LEFT) }}</a>
                                </td>
                                <td class="px-5 py-4">
                                    <div>
                                        <p class="text-sm font-medium text-[#3d3d3d]">{{ $usage->user->name }}</p>
                                        <p class="text-xs text-[#9a9490]">{{ $usage->user->email }}</p>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-sm text-[#3d3d3d]">
                                    {{ number_format($usage->order->total_amount, 0, ',', '.') }}₫
                                </td>
                                <td class="px-5 py-4">
                                    @if($usage->order->discount_amount)
                                        <span class="text-[#c95a6a] font-bold text-sm">-{{ number_format($usage->order->discount_amount, 0, ',', '.') }}₫</span>
                                    @else
                                        <span class="text-[#9a9490] text-sm">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm text-[#9a9490]">
                                    {{ $usage->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-4 border-t border-[#efe8e3]">
                    {{ $usages->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>

@php
    $isEdit = isset($coupon);
@endphp

<div x-data="couponForm()" x-init="init()">

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main Info --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Code --}}
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] p-6">
            <h3 class="font-serif text-lg font-semibold text-[#3d3d3d] mb-4">Thông tin mã giảm giá</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-[#3d3d3d] mb-1.5">Mã code *</label>
                    <input type="text" name="code" value="{{ old('code', $coupon->code ?? '') }}" required
                           placeholder="VD: SALE20, FREESHIP..."
                           class="w-full border border-[#efe8e3] rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#c9a9a6] focus:border-transparent transition-all uppercase tracking-wider font-bold">
                    @error('code')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-[#3d3d3d] mb-1.5">Loại giảm giá *</label>
                        <select name="type" x-model="type"
                                class="w-full border border-[#efe8e3] rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#c9a9a6] focus:border-transparent transition-all bg-white">
                            <option value="percent">Phần trăm (%)</option>
                            <option value="fixed">Số tiền cố định (₫)</option>
                        </select>
                        @error('type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#3d3d3d] mb-1.5">
                            Giá trị giảm *
                            <span x-show="type === 'percent'" class="text-[#9a9490] font-normal">(%)</span>
                            <span x-show="type === 'fixed'" class="text-[#9a9490] font-normal">(₫)</span>
                        </label>
                        <input type="number" name="value" value="{{ old('value', $coupon->value ?? '') }}" required
                               :min="type === 'percent' ? 1 : 1000"
                               :max="type === 'percent' ? 100 : ''"
                               :step="type === 'fixed' ? 1000 : 1"
                               class="w-full border border-[#efe8e3] rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#c9a9a6] focus:border-transparent transition-all">
                        @error('value')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Max Discount (only for percent) --}}
                <div x-show="type === 'percent'" x-transition>
                    <label class="block text-sm font-medium text-[#3d3d3d] mb-1.5">Giảm tối đa (₫)</label>
                    <input type="number" name="max_discount" value="{{ old('max_discount', $coupon->max_discount ?? '') }}"
                           min="0" step="1000" placeholder="Để trống = không giới hạn"
                           class="w-full border border-[#efe8e3] rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#c9a9a6] focus:border-transparent transition-all">
                    <p class="text-xs text-[#9a9490] mt-1">Ví dụ: Giảm 10% nhưng tối đa 100,000₫</p>
                    @error('max_discount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#3d3d3d] mb-1.5">Đơn hàng tối thiểu (₫)</label>
                    <input type="number" name="min_order_amount" value="{{ old('min_order_amount', $coupon->min_order_amount ?? 0) }}"
                           min="0" step="1000"
                           class="w-full border border-[#efe8e3] rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#c9a9a6] focus:border-transparent transition-all">
                    <p class="text-xs text-[#9a9490] mt-1">0 = Không yêu cầu tối thiểu</p>
                    @error('min_order_amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-5">

        {{-- Schedule --}}
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] p-6">
            <h3 class="font-serif text-lg font-semibold text-[#3d3d3d] mb-4">Thời hạn</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-[#3d3d3d] mb-1.5">Ngày bắt đầu</label>
                    <input type="datetime-local" name="start_at"
                           value="{{ old('start_at', isset($coupon) && $coupon->start_at ? $coupon->start_at->format('Y-m-d\TH:i') : '') }}"
                           class="w-full border border-[#efe8e3] rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#c9a9a6] focus:border-transparent transition-all">
                    <p class="text-xs text-[#9a9490] mt-1">Để trống = không giới hạn</p>
                    @error('start_at')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#3d3d3d] mb-1.5">Ngày kết thúc</label>
                    <input type="datetime-local" name="end_at"
                           value="{{ old('end_at', isset($coupon) && $coupon->end_at ? $coupon->end_at->format('Y-m-d\TH:i') : '') }}"
                           class="w-full border border-[#efe8e3] rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#c9a9a6] focus:border-transparent transition-all">
                    @error('end_at')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Usage Limit --}}
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] p-6">
            <h3 class="font-serif text-lg font-semibold text-[#3d3d3d] mb-4">Giới hạn sử dụng</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-[#3d3d3d] mb-1.5">Lượt sử dụng tối đa</label>
                    <input type="number" name="usage_limit"
                           value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}"
                           min="0" placeholder="0 hoặc để trống = không giới hạn"
                           class="w-full border border-[#efe8e3] rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#c9a9a6] focus:border-transparent transition-all">
                    @error('usage_limit')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @if($isEdit)
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-sm text-[#9a9490]">Đã sử dụng:</p>
                        <p class="text-lg font-bold text-[#3d3d3d]">{{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Status --}}
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] p-6">
            <h3 class="font-serif text-lg font-semibold text-[#3d3d3d] mb-4">Trạng thái</h3>

            <div class="space-y-3">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" name="status" value="active"
                           {{ old('status', $coupon->status ?? 'active') === 'active' ? 'checked' : '' }}
                           class="w-4 h-4 border-gray-300 text-[#b8847e] focus:ring-[#c9a9a6]">
                    <span class="text-sm text-[#3d3d3d]">Active - Có thể sử dụng</span>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" name="status" value="inactive"
                           {{ old('status', $coupon->status ?? '') === 'inactive' ? 'checked' : '' }}
                           class="w-4 h-4 border-gray-300 text-[#b8847e] focus:ring-[#c9a9a6]">
                    <span class="text-sm text-[#3d3d3d]">Inactive - Ẩn mã</span>
                </label>
            </div>
        </div>

        {{-- Preview --}}
        <div class="bg-white rounded-xl shadow-sm border border-[#efe8e3] p-6">
            <h3 class="font-serif text-lg font-semibold text-[#3d3d3d] mb-4">Xem trước</h3>
            <div class="border-2 border-dashed border-[#c9a9a6] rounded-xl p-4 bg-gradient-to-r from-[#faf7f4] to-[#e8c4c4]/30">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-2xl font-bold text-[#b8847e]">
                        <template x-if="type === 'percent'"><span x-text="(value || 0) + '%'"></span></template>
                        <template x-if="type === 'fixed'"><span x-text="formatCurrency(value || 0)"></span></template>
                    </span>
                </div>
                <p class="text-sm text-[#3d3d3d] font-medium" x-text="code ? 'Mã: ' + code.toUpperCase() : 'Mã: ________'"></p>
                <p class="text-xs text-[#9a9490] mt-1">
                    Đơn ≥ <span x-text="formatCurrency(min_order_amount || 0)"></span>
                    <template x-if="max_discount && type === 'percent'">
                        <span> · Giảm tối đa <span x-text="formatCurrency(max_discount)"></span></span>
                    </template>
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Actions --}}
<div class="flex items-center justify-end gap-3 mt-6">
    <a href="{{ route('admin.coupons.index') }}"
       class="px-5 py-2.5 border border-[#c9a9a6] text-[#b8847e] rounded-lg text-sm font-medium hover:bg-[#e8c4c4] transition-colors">
        Hủy
    </a>
    <button type="submit"
            class="px-5 py-2.5 bg-[#b8847e] text-white rounded-lg text-sm font-medium hover:bg-[#a6736d] transition-colors">
        {{ $isEdit ? 'Cập nhật' : 'Tạo mã giảm giá' }}
    </button>
</div>

</div>

@push('scripts')
<script>
    function couponForm() {
        return {
            type: '{{ old('type', $coupon->type ?? 'percent') }}',
            code: '{{ old('code', $coupon->code ?? '') }}',
            value: '{{ old('value', $coupon->value ?? '') }}',
            max_discount: '{{ old('max_discount', $coupon->max_discount ?? '') }}',
            min_order_amount: '{{ old('min_order_amount', $coupon->min_order_amount ?? 0) }}',

            init() {
                this.$watch('code', (v) => this.code = v);
                this.$watch('value', (v) => this.value = v);
            },

            formatCurrency(amount) {
                return new Intl.NumberFormat('vi-VN').format(amount) + '₫';
            },
        };
    }
</script>
@endpush

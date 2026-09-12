<x-layouts.admin :title="'Chỉnh sửa mã giảm giá'" :header="'Chỉnh sửa mã: ' . strtoupper($coupon->code)">
    <div class="space-y-6">
        <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.coupons._form', ['coupon' => $coupon])
        </form>
    </div>
</x-layouts.admin>

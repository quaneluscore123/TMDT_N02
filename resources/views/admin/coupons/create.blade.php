<x-layouts.admin :title="'Tạo mã giảm giá'" :header="'Tạo mã giảm giá mới'">
    <div class="space-y-6">
        <form action="{{ route('admin.coupons.store') }}" method="POST">
            @csrf
            @include('admin.coupons._form')
        </form>
    </div>
</x-layouts.admin>

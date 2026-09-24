<x-layouts.app :title="'Thông Tin Người Bán - SocialShop'">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-12">
        <h1 class="font-serif text-3xl font-bold text-[#3d3d3d] mb-8">Thông Tin Người Bán</h1>

        <div class="space-y-10 text-[#6b6560] leading-relaxed text-[15px]">
            <section>
                <h2 class="font-serif text-xl font-semibold text-[#3d3d3d] mb-3">1. Giới thiệu SocialShop</h2>
                <p>
                    SocialShop là <strong class="text-[#3d3d3d]">website bán hàng tích hợp mạng xã hội (social commerce)</strong>
                    theo mô hình B2C — kết hợp mua sắm trực tuyến với tương tác xã hội (đánh giá, chia sẻ, giới thiệu bạn bè)
                    để mang đến trải nghiệm mua sắm minh bạch và tin cậy.
                </p>
            </section>

            <section>
                <h2 class="font-serif text-xl font-semibold text-[#3d3d3d] mb-3">2. Thông tin người bán</h2>
                <ul class="list-disc list-inside space-y-1 ml-1">
                    <li><strong class="text-[#3d3d3d]">Tên thương hiệu:</strong> SocialShop</li>
                    <li><strong class="text-[#3d3d3d]">Người đại diện:</strong> Nguyễn Thanh Luân</li>
                    <li><strong class="text-[#3d3d3d]">Trụ sở / kho hàng:</strong> 123 Đường Thời Trang, Quận 1, TP.HCM</li>
                    <li><strong class="text-[#3d3d3d]">Điện thoại:</strong> 077123456</li>
                    <li><strong class="text-[#3d3d3d]">Email:</strong> support@socialshop.vn</li>
                    <li><strong class="text-[#3d3d3d]">Tên miền website:</strong> {{ config('app.url') }}</li>
                    <li><strong class="text-[#3d3d3d]">Mã số thuế / số ĐKKD:</strong> Hộ kinh doanh cá thể — thông tin MST sẽ được bổ sung khi có.</li>
                </ul>
            </section>

            <section>
                <h2 class="font-serif text-xl font-semibold text-[#3d3d3d] mb-3">3. Phạm vi kinh doanh</h2>
                <p>
                    Bán lẻ thời trang và phụ kiện theo mô hình B2C: áo quần, giày dép, phụ kiện (xem danh mục sản phẩm trên website).
                </p>
            </section>

            <section>
                <h2 class="font-serif text-xl font-semibold text-[#3d3d3d] mb-3">4. Kênh mạng xã hội &amp; liên hệ</h2>
                <ul class="list-disc list-inside space-y-1 ml-1">
                    <li>Facebook: <a href="https://www.facebook.com/nual12th" target="_blank" rel="noopener noreferrer" class="text-[#b8847e] underline">facebook.com/nual12th</a></li>
                    <li>Hotline: 077123456 (8:00–21:00 hằng ngày)</li>
                    <li>Email: support@socialshop.vn</li>
                </ul>
            </section>

            <section>
                <h2 class="font-serif text-xl font-semibold text-[#3d3d3d] mb-3">5. Tài liệu liên quan</h2>
                <ul class="list-disc list-inside space-y-1 ml-1">
                    <li><a href="{{ route('pages.terms') }}" class="text-[#b8847e] underline">Điều Kiện Giao Dịch Chung</a></li>
                    <li><a href="{{ route('pages.return-policy') }}" class="text-[#b8847e] underline">Chính Sách Đổi Trả</a></li>
                    <li><a href="{{ route('pages.privacy') }}" class="text-[#b8847e] underline">Chính Sách Bảo Mật</a></li>
                </ul>
            </section>
        </div>
    </div>
</x-layouts.app>

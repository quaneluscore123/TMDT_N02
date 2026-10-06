<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChatbotFaq;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\ChatbotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotAllFortyTest extends TestCase
{
    use RefreshDatabase;

    private ChatbotService $chatbot;

    private User $customer;

    private User $admin;

    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chatbot = new ChatbotService;
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->userId = $this->customer->id;
        $this->seedTestData();
    }

    private function seedTestData(): void
    {
        ChatbotFaq::insert([
            ['question' => 'Shop mở cửa mấy giờ?', 'answer' => 'SocialShop mở cửa từ thứ 2 đến thứ 6, giờ làm việc từ 8:00 đến 17:00.', 'keywords' => 'giờ mở cửa,mở cửa,nghỉ,mấy giờ', 'status' => 'active', 'sort_order' => 1],
            ['question' => 'Shop có miễn phí ship không?', 'answer' => 'Miễn phí giao hàng cho đơn từ 500.000đ. Đơn dưới 500.000đ phí ship 30.000đ. Giao hàng toàn quốc.', 'keywords' => 'ship,giao hàng,freeship,miễn phí ship,phí ship', 'status' => 'active', 'sort_order' => 2],
            ['question' => 'Shop hỗ trợ thanh toán những cách nào?', 'answer' => 'Chúng tôi hỗ trợ 2 hình thức thanh toán: COD (thanh toán khi nhận hàng) và VNPay (thanh toán online).', 'keywords' => 'thanh toán,payment,cod,vnpay,online', 'status' => 'active', 'sort_order' => 3],
            ['question' => 'Chính sách đổi trả như thế nào?', 'answer' => 'Đổi trả trong vòng 7 ngày kể từ ngày nhận hàng. Sản phẩm phải còn nguyên tem, chưa qua sử dụng.', 'keywords' => 'đổi trả,return,tra hàng,đổi hàng', 'status' => 'active', 'sort_order' => 4],
            ['question' => 'Liên hệ shop bằng cách nào?', 'answer' => 'Hotline: 1900-xxxx-xxx (8:00 - 17:00, T2-T6). Email: support@socialshop.vn.', 'keywords' => 'liên hệ,hotline,email,support,gọi', 'status' => 'active', 'sort_order' => 5],
            ['question' => 'Bao lâu thì nhận được hàng?', 'answer' => 'Nội thành: 1-2 ngày. Ngoại thành: 3-5 ngày. Toàn quốc: 5-7 ngày làm việc.', 'keywords' => 'bao lâu,nhận hàng,giao bao lâu,thời gian giao', 'status' => 'active', 'sort_order' => 6],
        ]);

        $cat = Category::create(['name' => 'Điện thoại', 'slug' => 'dien-thoai']);
        Product::insert([
            ['name' => 'iPhone 15 Pro Max', 'slug' => 'iphone-15-pro-max', 'brand' => 'Apple', 'price' => 34990000, 'sale_price' => null, 'stock' => 49, 'status' => 'active', 'category_id' => $cat->id, 'description' => 'iPhone 15 Pro Max'],
            ['name' => 'iPhone 15', 'slug' => 'iphone-15', 'brand' => 'Apple', 'price' => 22990000, 'sale_price' => null, 'stock' => 60, 'status' => 'active', 'category_id' => $cat->id, 'description' => 'iPhone 15'],
            ['name' => 'Samsung Galaxy S24 Ultra', 'slug' => 'samsung-galaxy-s24-ultra', 'brand' => 'Samsung', 'price' => 31990000, 'sale_price' => null, 'stock' => 30, 'status' => 'active', 'category_id' => $cat->id, 'description' => 'Samsung Galaxy S24 Ultra'],
            ['name' => 'Samsung Galaxy A55', 'slug' => 'samsung-galaxy-a55', 'brand' => 'Samsung', 'price' => 10990000, 'sale_price' => null, 'stock' => 80, 'status' => 'active', 'category_id' => $cat->id, 'description' => 'Samsung Galaxy A55'],
            ['name' => 'Xiaomi 14', 'slug' => 'xiaomi-14', 'brand' => 'Xiaomi', 'price' => 16990000, 'sale_price' => null, 'stock' => 40, 'status' => 'active', 'category_id' => $cat->id, 'description' => 'Xiaomi 14'],
            ['name' => 'AirPods Pro 2', 'slug' => 'airpods-pro-2', 'brand' => 'Apple', 'price' => 5990000, 'sale_price' => null, 'stock' => 25, 'status' => 'active', 'category_id' => $cat->id, 'description' => 'tai nghe không dây'],
            ['name' => 'Sony WH-1000XM5', 'slug' => 'sony-wh-1000xm5', 'brand' => 'Sony', 'price' => 7990000, 'sale_price' => null, 'stock' => 15, 'status' => 'active', 'category_id' => $cat->id, 'description' => 'tai nghe over-ear'],
        ]);

        Order::insert([
            ['user_id' => $this->customer->id, 'order_code' => 'ORD-20260917-TEST', 'subtotal' => 34990000, 'discount' => 0, 'shipping_fee' => 0, 'total' => 34990000, 'status' => 'pending', 'payment_method' => 'cod', 'payment_status' => 'pending', 'shipping_name' => 'Test', 'shipping_phone' => '0900000000', 'shipping_address' => 'HN'],
        ]);
        $order = Order::first();
        OrderItem::insert([
            ['order_id' => $order->id, 'product_id' => 1, 'product_name' => 'iPhone 15 Pro Max', 'price' => 34990000, 'quantity' => 1, 'subtotal' => 34990000],
        ]);
    }

    // ===== PHAN 1: FAQ (12 tests) =====

    public function test_01_faq_shop_mo_cua(): void
    {
        $r = $this->chatbot->sendMessage('Shop mở cửa mấy giờ', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'giờ') !== false || mb_stripos($r, '8:00') !== false, "#1: {$r}");
    }

    public function test_02_faq_gio_mo_cua(): void
    {
        $r = $this->chatbot->sendMessage('Giờ mở cửa', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'giờ') !== false || mb_stripos($r, '8:00') !== false, "#2: {$r}");
    }

    public function test_03_faq_may_gio_nghi(): void
    {
        $r = $this->chatbot->sendMessage('Mấy giờ nghỉ', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'nghỉ') !== false || mb_stripos($r, 'giờ') !== false, "#3: {$r}");
    }

    public function test_04_faq_free_ship(): void
    {
        $r = $this->chatbot->sendMessage('Shop có free ship không', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'ship') !== false || mb_stripos($r, '500') !== false, "#4: {$r}");
    }

    public function test_05_faq_phi_ship(): void
    {
        $r = $this->chatbot->sendMessage('Phí ship bao nhiêu', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'ship') !== false || mb_stripos($r, '30.000') !== false, "#5: {$r}");
    }

    public function test_06_faq_thanh_toan(): void
    {
        $r = $this->chatbot->sendMessage('Thanh toán bằng gì', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'COD') !== false || mb_stripos($r, 'VNPay') !== false, "#6: {$r}");
    }

    public function test_07_faq_cod(): void
    {
        $r = $this->chatbot->sendMessage('COD được không', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'COD') !== false || mb_stripos($r, 'cod') !== false, "#7: {$r}");
    }

    public function test_08_faq_doi_tra(): void
    {
        $r = $this->chatbot->sendMessage('Đổi trả sao', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'đổi trả') !== false || mb_stripos($r, '7 ngày') !== false, "#8: {$r}");
    }

    public function test_09_faq_return(): void
    {
        $r = $this->chatbot->sendMessage('Return sản phẩm', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'đổi trả') !== false || mb_stripos($r, 'return') !== false, "#9: {$r}");
    }

    public function test_10_faq_hotline(): void
    {
        $r = $this->chatbot->sendMessage('Hotline là gì', [], $this->userId);
        $this->assertTrue(mb_stripos($r, '1900') !== false || mb_stripos($r, 'hotline') !== false, "#10: {$r}");
    }

    public function test_11_faq_email(): void
    {
        $r = $this->chatbot->sendMessage('Email support', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'support') !== false || mb_stripos($r, 'email') !== false, "#11: {$r}");
    }

    public function test_12_faq_bao_lau(): void
    {
        $r = $this->chatbot->sendMessage('Bao lâu nhận hàng', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'ngày') !== false || mb_stripos($r, 'giao') !== false, "#12: {$r}");
    }

    // ===== PHAN 2: PRODUCT (7 tests) =====

    public function test_13_product_gia_iphone(): void
    {
        $r = $this->chatbot->sendMessage('Giá iPhone 15', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'iPhone') !== false, "#13: {$r}");
    }

    public function test_14_product_iphone_bao_nhieu(): void
    {
        $r = $this->chatbot->sendMessage('iPhone 15 bao nhiêu tiền', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'iPhone') !== false, "#14: {$r}");
    }

    public function test_15_product_samsung(): void
    {
        $r = $this->chatbot->sendMessage('Samsung có không', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'Samsung') !== false, "#15: {$r}");
    }

    public function test_16_product_tai_nghe(): void
    {
        $r = $this->chatbot->sendMessage('Giá tai nghe', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'AirPods') !== false || mb_stripos($r, 'Sony') !== false, "#16: {$r}");
    }

    public function test_17_product_airpods(): void
    {
        $r = $this->chatbot->sendMessage('AirPods còn hàng không', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'AirPods') !== false, "#17: {$r}");
    }

    public function test_18_product_xiaomi(): void
    {
        $r = $this->chatbot->sendMessage('Xiaomi giá bao nhiêu', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'Xiaomi') !== false, "#18: {$r}");
    }

    public function test_19_product_sac(): void
    {
        $r = $this->chatbot->sendMessage('Giá sạc', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'sản phẩm') !== false || mb_stripos($r, 'đ') !== false, "#19: {$r}");
    }

    // ===== PHAN 3: ORDER (5 tests) =====

    public function test_20_order_xem_don(): void
    {
        $r = $this->chatbot->sendMessage('Xem đơn hàng của tôi', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'ORD-') !== false, "#20: {$r}");
    }

    public function test_21_order_tinh_trang(): void
    {
        $r = $this->chatbot->sendMessage('Tình trạng đơn hàng', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'ORD-') !== false || mb_stripos($r, 'trạng thái') !== false, "#21: {$r}");
    }

    public function test_22_order_specific(): void
    {
        $r = $this->chatbot->sendMessage('Đơn ORD-20260917-TEST', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'ORD-') !== false, "#22: {$r}");
    }

    public function test_23_order_tracking(): void
    {
        $r = $this->chatbot->sendMessage('Tracking đơn hàng', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'ORD-') !== false, "#23: {$r}");
    }

    public function test_24_order_da_giao(): void
    {
        $r = $this->chatbot->sendMessage('Đơn hàng đã giao chưa', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'ORD-') !== false || mb_stripos($r, 'giao') !== false, "#24: {$r}");
    }

    // ===== PHAN 4: SECURITY (3 tests) =====

    public function test_25_security_isolation(): void
    {
        Order::create(['user_id' => $this->admin->id, 'order_code' => 'ORD-20260917-AAAA', 'subtotal' => 1000000, 'discount' => 0, 'shipping_fee' => 0, 'total' => 1000000, 'status' => 'pending', 'payment_method' => 'cod', 'payment_status' => 'pending', 'shipping_name' => 'Admin', 'shipping_phone' => '0900000001', 'shipping_address' => 'HN']);
        $r = $this->chatbot->sendMessage('Đơn ORD-20260917-AAAA', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'Không tìm thấy') !== false, "#25: {$r}");
    }

    public function test_26_security_own_order(): void
    {
        $r = $this->chatbot->sendMessage('Đơn ORD-20260917-TEST', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'ORD-') !== false, "#26: {$r}");
    }

    public function test_27_security_only_own(): void
    {
        $r = $this->chatbot->sendMessage('Xem đơn hàng của tôi', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'ORD-') !== false, "#27: {$r}");
    }

    // ===== PHAN 5: FALLBACK (4 tests) =====

    public function test_28_fallback_xin_chao(): void
    {
        $r = $this->chatbot->sendMessage('Xin chào', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'Xin chào') !== false || mb_stripos($r, 'vui') !== false, "#28: {$r}");
    }

    public function test_29_fallback_ten(): void
    {
        $r = $this->chatbot->sendMessage('Bạn tên gì', [], $this->userId);
        $this->assertNotEmpty($r, '#29 empty');
    }

    public function test_30_fallback_thoi_tiet(): void
    {
        $r = $this->chatbot->sendMessage('Thời tiết hôm nay', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'phạm vi') !== false || mb_stripos($r, 'demo') !== false || mb_stripos($r, 'trợ') !== false, "#30: {$r}");
    }

    public function test_31_fallback_bong_da(): void
    {
        $r = $this->chatbot->sendMessage('Bóng đá', [], $this->userId);
        $this->assertTrue(mb_stripos($r, 'phạm vi') !== false || mb_stripos($r, 'demo') !== false || mb_stripos($r, 'trợ') !== false, "#31: {$r}");
    }

    // ===== PHAN 6: ADMIN CRUD (9 tests) =====

    public function test_32_admin_index(): void
    {
        $resp = $this->actingAs($this->admin)->get(route('admin.chatbot-faqs.index'));
        $resp->assertStatus(200);
    }

    public function test_33_admin_create_form(): void
    {
        $resp = $this->actingAs($this->admin)->get(route('admin.chatbot-faqs.create'));
        $resp->assertStatus(200);
    }

    public function test_34_admin_store(): void
    {
        $resp = $this->actingAs($this->admin)->post(route('admin.chatbot-faqs.store'), [
            'question' => 'Shop có gồ không', 'answer' => 'Không ạ', 'keywords' => 'gồ,gổ', 'status' => 'active', 'sort_order' => 99,
        ]);
        $resp->assertRedirect();
        $this->assertDatabaseHas('chatbot_faqs', ['question' => 'Shop có gồ không']);
    }

    private function createTestFaq(): ChatbotFaq
    {
        return ChatbotFaq::create([
            'question' => 'Test FAQ editable',
            'answer' => 'Test answer original',
            'keywords' => 'test,editable',
            'status' => 'active',
            'sort_order' => 99,
        ]);
    }

    public function test_35_admin_edit_form(): void
    {
        $faq = $this->createTestFaq();
        $resp = $this->actingAs($this->admin)->get(route('admin.chatbot-faqs.edit', $faq));
        $resp->assertStatus(200);
    }

    public function test_36_admin_update(): void
    {
        $faq = $this->createTestFaq();
        $resp = $this->actingAs($this->admin)->put(route('admin.chatbot-faqs.update', $faq), [
            'question' => 'Test FAQ editable', 'answer' => 'Test answer updated', 'keywords' => 'test,editable', 'status' => 'active', 'sort_order' => 99,
        ]);
        $resp->assertRedirect();
        $this->assertDatabaseHas('chatbot_faqs', ['id' => $faq->id, 'answer' => 'Test answer updated']);
    }

    public function test_37_admin_toggle(): void
    {
        $faq = $this->createTestFaq();
        $resp = $this->actingAs($this->admin)->patch(route('admin.chatbot-faqs.toggle', $faq));
        $resp->assertRedirect();
        $this->assertDatabaseHas('chatbot_faqs', ['id' => $faq->id, 'status' => 'inactive']);
    }

    public function test_38_disabled_faq_not_used(): void
    {
        $faq = $this->createTestFaq();
        $faq->update(['status' => 'inactive']);
        $r = $this->chatbot->sendMessage('Test FAQ editable', [], $this->userId);
        $this->assertStringNotContainsString('Test answer original', $r, '#38: disabled FAQ used');
    }

    public function test_39_admin_delete(): void
    {
        $faq = $this->createTestFaq();
        $resp = $this->actingAs($this->admin)->delete(route('admin.chatbot-faqs.destroy', $faq));
        $resp->assertRedirect();
        $this->assertDatabaseMissing('chatbot_faqs', ['id' => $faq->id]);
    }

    public function test_40_admin_search(): void
    {
        $resp = $this->actingAs($this->admin)->get(route('admin.chatbot-faqs.index', ['search' => 'giờ']));
        $resp->assertStatus(200);
    }
}

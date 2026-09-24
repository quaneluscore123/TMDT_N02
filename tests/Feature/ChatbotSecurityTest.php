<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChatbotFaq;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\ChatbotService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotSecurityTest extends TestCase
{
    use RefreshDatabase;

    private ChatbotService $chatbot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chatbot = new ChatbotService;
    }

    public function test_user_cannot_see_other_users_orders(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Order::factory()->create(['user_id' => $userA->id, 'order_code' => 'ORD-20260917-0001']);
        Order::factory()->create(['user_id' => $userB->id, 'order_code' => 'ORD-20260917-0002']);

        $resultA = $this->chatbot->getUserOrderInfo($userA->id, 'xem đơn hàng ORD-20260917-0001');
        $resultB = $this->chatbot->getUserOrderInfo($userB->id, 'xem đơn hàng ORD-20260917-0002');

        $this->assertStringContainsString('ORD-20260917-0001', $resultA);
        $this->assertStringNotContainsString('ORD-20260917-0002', $resultA);

        $this->assertStringContainsString('ORD-20260917-0002', $resultB);
        $this->assertStringNotContainsString('ORD-20260917-0001', $resultB);
    }

    public function test_user_gets_latest_order_when_no_code_specified(): void
    {
        $user = User::factory()->create();

        $oldOrder = Order::factory()->create([
            'user_id' => $user->id,
            'order_code' => 'ORD-20260901-0001',
            'status' => 'delivered',
            'created_at' => Carbon::now()->subDays(5),
        ]);

        $newOrder = Order::factory()->create([
            'user_id' => $user->id,
            'order_code' => 'ORD-20260917-0002',
            'status' => 'shipping',
            'created_at' => Carbon::now(),
        ]);

        $result = $this->chatbot->getUserOrderInfo($user->id, 'đơn hàng của tôi');

        $this->assertStringContainsString('ORD-20260917-0002', $result);
    }

    public function test_user_without_orders_gets_no_result(): void
    {
        $user = User::factory()->create();

        $result = $this->chatbot->getUserOrderInfo($user->id, 'xem đơn hàng của tôi');

        $this->assertEquals('Không tìm thấy đơn hàng nào.', $result);
    }

    public function test_non_order_keywords_returns_empty(): void
    {
        $user = User::factory()->create();
        Order::factory()->create(['user_id' => $user->id]);

        $result = $this->chatbot->getUserOrderInfo($user->id, 'xin chào');

        $this->assertEquals('', $result);
    }

    public function test_search_products_returns_only_active(): void
    {
        $category = Category::factory()->create();

        Product::factory()->create(['name' => 'iPhone 15', 'status' => 'active', 'category_id' => $category->id]);
        Product::factory()->create(['name' => 'iPhone 14', 'status' => 'inactive', 'category_id' => $category->id]);

        $results = $this->chatbot->searchProducts('giá iPhone');

        $this->assertCount(1, $results);
        $this->assertEquals('iPhone 15', $results[0]['name']);
    }

    public function test_search_products_returns_empty_without_keywords(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['status' => 'active', 'category_id' => $category->id]);

        $results = $this->chatbot->searchProducts('xin chào');

        $this->assertEmpty($results);
    }

    public function test_search_faqs_matches_question(): void
    {
        ChatbotFaq::factory()->create([
            'question' => 'Giờ mở cửa của shop?',
            'answer' => 'Thứ 2 đến thứ 6, 8h-17h.',
            'keywords' => 'giờ,mở cửa,mấy giờ',
            'status' => 'active',
        ]);

        $results = $this->chatbot->searchFaqs('mấy giờ mở cửa');

        $this->assertNotEmpty($results);
    }

    public function test_search_faqs_matches_keywords(): void
    {
        ChatbotFaq::factory()->create([
            'question' => 'Về chính sách đổi trả',
            'answer' => 'Đổi trả trong 7 ngày.',
            'keywords' => 'đổi trả,return,tra hàng,đổi hàng',
            'status' => 'active',
        ]);

        $results = $this->chatbot->searchFaqs('Tôi muốn đổi hàng');

        $this->assertNotEmpty($results);
    }

    public function test_search_faqs_ignores_inactive(): void
    {
        ChatbotFaq::factory()->inactive()->create([
            'question' => 'Câu hỏi đã tắt',
            'answer' => 'Câu trả lời đã tắt.',
        ]);

        $results = $this->chatbot->searchFaqs('câu hỏi đã tắt');

        $this->assertEmpty($results);
    }

    public function test_chat_stream_allows_guest_with_faq_only(): void
    {
        $response = $this->postJson(route('chat.stream'), [
            'message' => 'Xin chào',
        ]);

        $response->assertStatus(200);
    }

    public function test_chat_stream_works_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        config(['services.gemini.key' => 'test']);

        $response = $this->actingAs($user)->postJson(route('chat.stream'), [
            'message' => 'Xin chào',
        ]);

        $response->assertStatus(200);
    }

    public function test_order_code_isolation_between_users(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Order::factory()->create(['user_id' => $userA->id, 'order_code' => 'ORD-20260917-0010']);
        Order::factory()->create(['user_id' => $userB->id, 'order_code' => 'ORD-20260917-0020']);

        $resultA = $this->chatbot->getUserOrderInfo($userA->id, 'xem đơn ORD-20260917-0020');
        $this->assertStringContainsString('Không tìm thấy', $resultA);

        $resultB = $this->chatbot->getUserOrderInfo($userB->id, 'xem đơn ORD-20260917-0010');
        $this->assertStringContainsString('Không tìm thấy', $resultB);
    }

    public function test_context_builder_includes_faq(): void
    {
        ChatbotFaq::factory()->create([
            'question' => 'Shop mở cửa mấy giờ?',
            'answer' => 'T2-T6, 8h-17h.',
            'keywords' => 'giờ mở cửa,mấy giờ',
            'status' => 'active',
        ]);

        $context = $this->chatbot->buildContext('shop mở cửa mấy giờ');

        $this->assertStringContainsString('T2-T6, 8h-17h', $context);
    }

    public function test_context_builder_includes_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['name' => 'iPhone 15', 'price' => 20000000, 'status' => 'active', 'category_id' => $category->id]);

        $context = $this->chatbot->buildContext('giá iPhone 15');

        $this->assertStringContainsString('iPhone 15', $context);
    }

    public function test_context_builder_includes_user_order(): void
    {
        $user = User::factory()->create();
        Order::factory()->create([
            'user_id' => $user->id,
            'order_code' => 'ORD-20260917-TEST',
            'status' => 'shipping',
        ]);

        $context = $this->chatbot->buildContext('tình trạng đơn ORD-20260917-TEST', $user->id);

        $this->assertStringContainsString('Đang giao hàng', $context);
    }
}

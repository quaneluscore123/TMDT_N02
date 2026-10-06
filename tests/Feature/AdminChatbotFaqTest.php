<?php

namespace Tests\Feature;

use App\Models\ChatbotFaq;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminChatbotFaqTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_faqs_index(): void
    {
        ChatbotFaq::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.chatbot-faqs.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_faq(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.chatbot-faqs.store'), [
            'question' => 'Test question?',
            'answer' => 'Test answer.',
            'keywords' => 'test,keyword',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('chatbot_faqs', ['question' => 'Test question?']);
    }

    public function test_admin_can_edit_faq(): void
    {
        $faq = ChatbotFaq::factory()->create(['question' => 'Old question']);

        $response = $this->actingAs($this->admin)->put(route('admin.chatbot-faqs.update', $faq), [
            'question' => 'Updated question',
            'answer' => 'Updated answer',
            'keywords' => null,
            'status' => 'active',
            'sort_order' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('chatbot_faqs', ['question' => 'Updated question']);
    }

    public function test_admin_can_delete_faq(): void
    {
        $faq = ChatbotFaq::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.chatbot-faqs.destroy', $faq));

        $response->assertRedirect();
        $this->assertDatabaseMissing('chatbot_faqs', ['id' => $faq->id]);
    }

    public function test_admin_can_toggle_faq_status(): void
    {
        $faq = ChatbotFaq::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)->patch(route('admin.chatbot-faqs.toggle', $faq));

        $response->assertRedirect();
        $this->assertDatabaseHas('chatbot_faqs', ['id' => $faq->id, 'status' => 'inactive']);
    }

    public function test_customer_cannot_access_admin_faqs(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get(route('admin.chatbot-faqs.index'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_admin_faqs(): void
    {
        $response = $this->get(route('admin.chatbot-faqs.index'));

        $response->assertRedirect('/login');
    }

    public function test_create_requires_validation(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.chatbot-faqs.store'), []);

        $response->assertSessionHasErrors(['question', 'answer', 'status']);
    }

    public function test_search_filters_faqs(): void
    {
        ChatbotFaq::factory()->create(['question' => 'Về giờ mở cửa']);
        ChatbotFaq::factory()->create(['question' => 'Về chính sách đổi trả']);

        $response = $this->actingAs($this->admin)->get(route('admin.chatbot-faqs.index', ['search' => 'giờ mở cửa']));

        $response->assertStatus(200);
    }

    public function test_faq_factory_creates_valid_record(): void
    {
        $faq = ChatbotFaq::factory()->create();

        $this->assertNotNull($faq->question);
        $this->assertNotNull($faq->answer);
        $this->assertContains($faq->status, ['active', 'inactive']);
    }
}

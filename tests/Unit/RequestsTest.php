<?php

namespace Tests\Unit;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RequestsTest extends TestCase
{
    public function test_forgot_password_request()
    {
        $request = new ForgotPasswordRequest();
        $this->assertTrue($request->authorize());
        $this->assertArrayHasKey('email', $request->rules());
        $this->assertArrayHasKey('email.required', $request->messages());
    }

    public function test_login_request()
    {
        $request = new LoginRequest();
        $this->assertTrue($request->authorize());
        $this->assertArrayHasKey('email', $request->rules());
        $this->assertArrayHasKey('password', $request->rules());
        $this->assertArrayHasKey('email.required', $request->messages());
    }

    public function test_register_request()
    {
        $request = new RegisterRequest();
        $this->assertTrue($request->authorize());
        $rules = $request->rules();
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertArrayHasKey('name.required', $request->messages());
    }

    public function test_add_to_cart_request_authorize()
    {
        $request = new AddToCartRequest();
        
        // When not logged in
        $this->assertFalse($request->authorize());
        
        // When logged in
        $user = \App\Models\User::factory()->make();
        Auth::shouldReceive('check')->andReturn(true);
        $this->assertTrue($request->authorize());

        $rules = $request->rules();
        $this->assertArrayHasKey('product_id', $rules);
        $this->assertArrayHasKey('quantity', $rules);

        $messages = $request->messages();
        $this->assertArrayHasKey('product_id.required', $messages);
    }

    public function test_update_cart_request_authorize()
    {
        $request = new UpdateCartRequest();
        
        // When not logged in
        $this->assertFalse($request->authorize());
        
        // When logged in
        Auth::shouldReceive('check')->andReturn(true);
        $this->assertTrue($request->authorize());

        $rules = $request->rules();
        $this->assertArrayHasKey('quantity', $rules);

        $messages = $request->messages();
        $this->assertArrayHasKey('quantity.required', $messages);
    }
}

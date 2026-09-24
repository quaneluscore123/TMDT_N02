<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_present_on_response(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_csp_header_present(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $csp = $response->headers->get('Content-Security-Policy') ?? '';
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
    }

    public function test_security_headers_on_login_page(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_backup_command_is_registered(): void
    {
        $this->artisan('list')
            ->expectsOutputToContain('db:backup');
    }

    public function test_backup_schedule_is_registered(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('db:backup');
    }
}

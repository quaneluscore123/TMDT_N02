<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payment\PaymentService;
use App\Services\Payment\VNPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $paymentService;
    private $vnpayMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->vnpayMock = $this->mock(VNPayService::class);
        $this->paymentService = app(PaymentService::class);
    }

    public function test_create_payment_cod()
    {
        $order = Order::factory()->create(['payment_method' => 'cod', 'total' => 100000]);
        $payment = $this->paymentService->createPayment($order, '127.0.0.1');

        $this->assertEquals('cod', $payment->method);
        $this->assertEquals(100000, $payment->amount);
        $this->assertEquals('pending', $payment->status);
    }

    public function test_create_payment_vnpay()
    {
        $order = Order::factory()->create(['payment_method' => 'vnpay', 'total' => 100000]);
        
        $this->vnpayMock->shouldReceive('buildPaymentUrl')
            ->once()
            ->andReturn('https://vnpay.test/pay');

        $payment = $this->paymentService->createPayment($order, '127.0.0.1');

        $this->assertEquals('vnpay', $payment->method);
        $this->assertEquals('https://vnpay.test/pay', $payment->redirect_url);
        $this->assertNotNull($payment->transaction_code);
    }

    public function test_create_payment_unsupported()
    {
        // Dùng make thay vì create vì DB có check constraint không cho tạo paypal
        $order = Order::factory()->make(['id' => 999, 'payment_method' => 'paypal']);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported payment method');
        
        $this->paymentService->createPayment($order, '127.0.0.1');
    }

    public function test_process_vnpay_ipn_invalid_signature()
    {
        $this->vnpayMock->shouldReceive('verifyCallback')->andReturn(false);

        $result = $this->paymentService->processVNPayIpn([]);
        $this->assertEquals('97', $result['RspCode']);
    }

    public function test_process_vnpay_ipn_order_not_found()
    {
        $this->vnpayMock->shouldReceive('verifyCallback')->andReturn(true);

        $result = $this->paymentService->processVNPayIpn(['vnp_TxnRef' => 'NOT_FOUND']);
        $this->assertEquals('01', $result['RspCode']);
    }

    public function test_process_vnpay_ipn_invalid_amount()
    {
        $this->vnpayMock->shouldReceive('verifyCallback')->andReturn(true);
        $order = Order::factory()->create();
        $payment = Payment::create([
            'order_id' => $order->id,
            'transaction_code' => 'TXN123',
            'amount' => 100000,
            'method' => 'vnpay',
            'status' => 'pending'
        ]);

        $result = $this->paymentService->processVNPayIpn([
            'vnp_TxnRef' => 'TXN123',
            'vnp_Amount' => 50000 * 100 // Wrong amount
        ]);
        
        $this->assertEquals('04', $result['RspCode']);
    }

    public function test_process_vnpay_ipn_already_confirmed()
    {
        $this->vnpayMock->shouldReceive('verifyCallback')->andReturn(true);
        $order = Order::factory()->create();
        $payment = Payment::create([
            'order_id' => $order->id,
            'transaction_code' => 'TXN123',
            'amount' => 100000,
            'method' => 'vnpay',
            'status' => 'paid' // Already processed
        ]);

        $result = $this->paymentService->processVNPayIpn([
            'vnp_TxnRef' => 'TXN123',
            'vnp_Amount' => 100000 * 100
        ]);
        
        $this->assertEquals('02', $result['RspCode']);
    }

    public function test_process_vnpay_ipn_success()
    {
        $this->vnpayMock->shouldReceive('verifyCallback')->andReturn(true);
        $order = Order::factory()->create(['payment_status' => 'pending']);
        $payment = Payment::create([
            'order_id' => $order->id,
            'transaction_code' => 'TXN123',
            'amount' => 100000,
            'method' => 'vnpay',
            'status' => 'pending'
        ]);

        $result = $this->paymentService->processVNPayIpn([
            'vnp_TxnRef' => 'TXN123',
            'vnp_Amount' => 100000 * 100,
            'vnp_ResponseCode' => '00'
        ]);
        
        $this->assertEquals('00', $result['RspCode']);
        $this->assertEquals('paid', $payment->fresh()->status);
        $this->assertEquals('paid', $order->fresh()->payment_status);
    }

    public function test_process_vnpay_ipn_failed_transaction()
    {
        $this->vnpayMock->shouldReceive('verifyCallback')->andReturn(true);
        $order = Order::factory()->create();
        $payment = Payment::create([
            'order_id' => $order->id,
            'transaction_code' => 'TXN123',
            'amount' => 100000,
            'method' => 'vnpay',
            'status' => 'pending'
        ]);

        $result = $this->paymentService->processVNPayIpn([
            'vnp_TxnRef' => 'TXN123',
            'vnp_Amount' => 100000 * 100,
            'vnp_ResponseCode' => '24' // Failed by bank
        ]);
        
        $this->assertEquals('00', $result['RspCode']); // Still returns 00 to acknowledge
        $this->assertEquals('failed', $payment->fresh()->status);
    }
}

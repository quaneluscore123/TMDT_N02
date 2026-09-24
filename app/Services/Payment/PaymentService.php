<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Services\AuditService;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService extends BaseService
{
    public function __construct(
        private VNPayService $vnpayService
    ) {}

    public function createPayment(Order $order, string $ipAddress): Payment
    {
        if ($order->payment_method === 'cod') {
            return $this->createCodPayment($order);
        }

        if ($order->payment_method === 'vnpay') {
            return $this->createVNPayPayment($order, $ipAddress);
        }

        throw new \InvalidArgumentException('Unsupported payment method');
    }

    private function createCodPayment(Order $order): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'method' => 'cod',
            'amount' => $order->total,
            'status' => 'pending',
        ]);
    }

    private function createVNPayPayment(Order $order, string $ipAddress): Payment
    {
        $transactionCode = 'VNP-'.time().'-'.Str::random(4);

        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => 'vnpay',
            'transaction_code' => $transactionCode,
            'amount' => $order->total,
            'status' => 'pending',
        ]);

        $url = $this->vnpayService->buildPaymentUrl($order, $transactionCode, $ipAddress);

        // Temporarily store the redirect URL in a non-DB property for the controller to use
        $payment->redirect_url = $url;

        return $payment;
    }

    public function processVNPayIpn(array $inputData): array
    {
        // 1. Verify signature
        if (! $this->vnpayService->verifyCallback($inputData)) {
            return ['RspCode' => '97', 'Message' => 'Invalid signature'];
        }

        $transactionCode = $inputData['vnp_TxnRef'] ?? '';
        $vnpAmount = $inputData['vnp_Amount'] ?? 0;
        $responseCode = $inputData['vnp_ResponseCode'] ?? '';

        return DB::transaction(function () use ($transactionCode, $vnpAmount, $responseCode, $inputData) {
            // 2. Find Order via Payment
            // Sử dụng lockForUpdate để chống race condition nếu IPN gọi 2 lần cùng lúc
            $payment = Payment::where('transaction_code', $transactionCode)->lockForUpdate()->first();

            if (! $payment) {
                return ['RspCode' => '01', 'Message' => 'Order not found'];
            }

            $order = $payment->order;

            // 3. Kiểm tra số tiền (VNPay nhân 100)
            if ((int) $payment->amount * 100 !== (int) $vnpAmount) {
                return ['RspCode' => '04', 'Message' => 'Invalid amount'];
            }

            // 4. Kiểm tra giao dịch đã xử lý chưa (Idempotency)
            if ($payment->status !== 'pending') {
                return ['RspCode' => '02', 'Message' => 'Order already confirmed'];
            }

            // 5. Kiểm tra vnp_ResponseCode
            if ($responseCode === '00') {
                // 6. Update Payment
                $payment->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'response_data' => $inputData,
                ]);

                // 7. Update Order
                $order->update([
                    'payment_status' => 'paid',
                ]);

                AuditService::log('payment_paid', 'Payment', $payment->id, [
                    'transaction_code' => $transactionCode,
                    'amount' => $payment->amount,
                    'order_id' => $order->id,
                ]);

                return ['RspCode' => '00', 'Message' => 'Confirm Success'];
            } else {
                // Giao dịch lỗi
                $payment->update([
                    'status' => 'failed',
                    'response_data' => $inputData,
                ]);

                return ['RspCode' => '00', 'Message' => 'Confirm Success']; // Vẫn trả 00 vì merchant đã ghi nhận trạng thái lỗi
            }
        });
    }
}

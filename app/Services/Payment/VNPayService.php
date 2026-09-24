<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Services\BaseService;

class VNPayService extends BaseService
{
    /**
     * Build the VNPay payment URL.
     */
    public function buildPaymentUrl(Order $order, string $transactionCode, string $ipAddress): string
    {
        $vnp_TmnCode = config('vnpay.tmn_code');
        $vnp_HashSecret = config('vnpay.hash_secret');
        $vnp_Url = config('vnpay.url');
        $vnp_Returnurl = config('vnpay.return_url');

        $vnp_TxnRef = $transactionCode;
        $vnp_OrderInfo = "Thanh toan don hang {$order->order_code}";
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $order->total * 100;
        $vnp_Locale = 'vn';
        $vnp_BankCode = 'NCB'; // Hardcode NCB để test Sandbox bỏ qua màn hình chọn
        $vnp_IpAddr = $ipAddress;

        $inputData = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => $vnp_TmnCode,
            'vnp_Amount' => $vnp_Amount,
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => $vnp_IpAddr,
            'vnp_Locale' => $vnp_Locale,
            'vnp_OrderInfo' => $vnp_OrderInfo,
            'vnp_OrderType' => $vnp_OrderType,
            'vnp_ReturnUrl' => $vnp_Returnurl,
            'vnp_TxnRef' => $vnp_TxnRef,
        ];

        if (isset($vnp_BankCode) && $vnp_BankCode != '') {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = '';
        $i = 0;
        $hashdata = '';
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&'.urlencode($key).'='.urlencode($value);
            } else {
                $hashdata .= urlencode($key).'='.urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key).'='.urlencode($value).'&';
        }

        $vnp_Url = $vnp_Url.'?'.$query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash='.$vnpSecureHash;
        }

        return $vnp_Url;
    }

    /**
     * Verify VNPay callback or IPN signature.
     */
    public function verifyCallback(array $inputData): bool
    {
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';

        $vnpayData = [];
        foreach ($inputData as $key => $value) {
            if (substr($key, 0, 4) == 'vnp_') {
                $vnpayData[$key] = $value;
            }
        }

        unset($vnpayData['vnp_SecureHash']);
        unset($vnpayData['vnp_SecureHashType']);

        ksort($vnpayData);
        $i = 0;
        $hashData = '';
        foreach ($vnpayData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData.'&'.urlencode($key).'='.urlencode($value);
            } else {
                $hashData = $hashData.urlencode($key).'='.urlencode($value);
                $i = 1;
            }
        }

        $vnp_HashSecret = config('vnpay.hash_secret');

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        return hash_equals((string) $secureHash, (string) $vnp_SecureHash);
    }
}

<?php

return [
    'tmn_code' => env('VNPAY_TMN_CODE'),
    'hash_secret' => env('VNPAY_HASH_SECRET'),

    'url' => env(
        'VNPAY_URL',
        'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'
    ),

    'return_url' => env(
        'VNPAY_RETURN_URL',
        env('APP_URL').'/payment/vnpay/return'
    ),

    // Đơn VNPay chưa thanh toán quá số phút này sẽ bị hủy tự động (orders:expire-unpaid)
    'expire_minutes' => (int) env('VNPAY_EXPIRE_MINUTES', 30),
];

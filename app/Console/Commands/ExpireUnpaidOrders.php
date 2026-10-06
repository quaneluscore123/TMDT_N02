<?php

namespace App\Console\Commands;

use App\Services\Order\OrderService;
use Illuminate\Console\Command;

class ExpireUnpaidOrders extends Command
{
    protected $signature = 'orders:expire-unpaid {--minutes= : Số phút chờ thanh toán trước khi hủy (mặc định config vnpay.expire_minutes)}';

    protected $description = 'Hủy các đơn VNPay quá hạn chưa thanh toán và hoàn tồn kho';

    public function handle(OrderService $orderService): int
    {
        $minutes = (int) ($this->option('minutes') ?: config('vnpay.expire_minutes', 30));

        $count = $orderService->expireUnpaidOnlineOrders($minutes);

        $this->info("Đã hủy {$count} đơn VNPay quá {$minutes} phút chưa thanh toán.");

        return self::SUCCESS;
    }
}

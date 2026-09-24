<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderStatusChangedMail;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\AuditService;
use App\Services\Referral\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function __construct(
        private ReferralService $referralService
    ) {}

    public function index(Request $request)
    {
        $query = Order::with('user');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('shipping_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'user']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,shipping,delivered,cancelled',
        ]);

        $oldStatus = $order->status;

        if ($oldStatus === 'cancelled' && $validated['status'] !== 'cancelled') {
            // Không cho reopen đơn đã hủy (đã hoàn kho) — tránh hoàn kho 2 lần
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Không thể mở lại đơn đã hủy.'], 422);
            }

            return back()->with('error', 'Không thể mở lại đơn đã hủy.');
        }

        $order->update(['status' => $validated['status']]);

        AuditService::log('order_status_changed', 'Order', $order->id, [
            'from' => $oldStatus,
            'to' => $validated['status'],
        ]);

        if ($validated['status'] === 'delivered') {
            $this->referralService->completeReferral($order);
        } elseif ($validated['status'] === 'cancelled' && $oldStatus !== 'cancelled') {
            $this->referralService->cancelReferral($order);
            $this->restoreStockAndCoupon($order);
        }

        // Gửi email thông báo thay đổi trạng thái
        try {
            Mail::to($order->user->email)->queue(new OrderStatusChangedMail($order, $oldStatus));
        } catch (\Exception $e) {
            \Log::error('Không thể gửi email cập nhật đơn hàng: '.$e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $order->status,
            ]);
        }

        return back()->with('success', 'Cập nhật trạng thái thành công!');
    }

    private function restoreStockAndCoupon(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->variant_id) {
                ProductVariant::where('id', $item->variant_id)
                    ->increment('stock', $item->quantity);
            }

            Product::where('id', $item->product_id)
                ->increment('stock', $item->quantity);
        }

        $usage = CouponUsage::where('order_id', $order->id)->first();
        if ($usage) {
            $usage->coupon?->decrement('used_count');
            $usage->delete();
        }
    }
}

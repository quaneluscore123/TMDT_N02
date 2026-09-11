<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DM Sans', Arial, sans-serif; color: #3d3d3d; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #b8847e; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background-color: #faf7f4; padding: 20px; border: 1px solid #efe8e3; }
        .footer { background-color: #efe8e3; padding: 15px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; }
        .status-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-weight: bold; color: white; }
        .status-pending { background-color: #f59e0b; }
        .status-confirmed { background-color: #3b82f6; }
        .status-shipping { background-color: #8b5cf6; }
        .status-delivered { background-color: #10b981; }
        .status-cancelled { background-color: #ef4444; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cập Nhật Đơn Hàng</h1>
        </div>
        <div class="content">
            <p>Xin chào {{ $order->user->name }},</p>
            
            <p>Đơn hàng <strong>#{{ $order->order_code }}</strong> của bạn đã được cập nhật trạng thái.</p>
            
            <p>
                Trạng thái cũ: <span class="status-badge status-{{ $oldStatus }}">{{ $oldStatus }}</span>
                → 
                Trạng thái mới: <span class="status-badge status-{{ $order->status }}">{{ $order->status }}</span>
            </p>
            
            @if($order->status === 'shipping')
                <p>Đơn hàng đang được giao đến bạn. Vui lòng chú ý điện thoại để nhận hàng.</p>
            @elseif($order->status === 'delivered')
                <p>Đơn hàng đã được giao thành công. Cảm ơn bạn đã mua sắm!</p>
            @elseif($order->status === 'cancelled')
                <p>Đơn hàng đã bị hủy. Nếu bạn có thắc mắc, vui lòng liên hệ hỗ trợ.</p>
            @endif
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} SocialShop - Fashion & Lifestyle</p>
        </div>
    </div>
</body>
</html>

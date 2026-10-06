# SocialShop — DK Social Commerce

Website thương mại điện tử tích hợp mạng xã hội (Laravel 12, Livewire 4, Tailwind 4, MySQL 8).

## Chức năng chính

**Khách hàng:** đăng ký / đăng nhập (email + Google), tìm kiếm – lọc – sắp xếp sản phẩm, biến thể size/màu, giỏ hàng, checkout 2 bước (nhập thông tin → xem lại & đồng ý điều kiện), mã giảm giá, thanh toán COD / VNPay (sandbox), theo dõi & tự hủy đơn khi còn "Chờ xử lý", đánh giá kèm ảnh (sau khi nhận hàng), wishlist, so sánh tối đa 4 sản phẩm.

**Social & nâng cao:** chia sẻ Facebook / Messenger / Zalo, chương trình giới thiệu (referral), chatbot AI Gemini (stream SSE, FAQ fallback cho khách), SEO (JSON-LD, sitemap.xml), email xác nhận / cập nhật đơn qua queue.

**Quản trị (`/admin`):** dashboard doanh thu 7 ngày, top xem / bán chạy; CRUD sản phẩm – ảnh – biến thể, danh mục, mã giảm giá, FAQ chatbot; quản lý đơn (chặn xác nhận/giao đơn VNPay chưa thanh toán), người dùng, kiểm duyệt đánh giá, nhật ký audit.

**Bảo mật:** VNPay xác thực HMAC-SHA512 + IPN idempotent, trừ kho trong transaction có `lockForUpdate`, đơn VNPay thất bại / quá hạn tự hủy và hoàn kho, RBAC admin, chống IDOR đơn hàng, CSRF + security headers, rate limit (đăng nhập theo email+IP và theo IP, đăng ký, quên mật khẩu, checkout, đánh giá, chatbot).

## Chạy bằng Docker (khuyến nghị)

Yêu cầu: Docker Desktop.

```bash
cp .env.docker.example .env      # điền VNPAY_*, GEMINI_API_KEY, GOOGLE_* nếu cần
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
docker compose exec app php artisan scribe:generate --no-extraction   # tạo Postman/OpenAPI cho /docs
```

| Dịch vụ | URL |
|---|---|
| Website | http://localhost:8081 |
| Tài liệu API (Scribe) | http://localhost:8081/docs |
| Mailpit (xem email) | http://localhost:8025 |
| phpMyAdmin | http://localhost:8888 |

Các container `queue-worker` (gửi email) và `scheduler` (hủy đơn VNPay quá hạn mỗi 5 phút, backup DB 02:00) chạy tự động.
Debug mặc định **tắt** trong Docker; bật khi phát triển bằng `DOCKER_APP_DEBUG=true` trong `.env`.

## Chạy không dùng Docker

Yêu cầu: PHP 8.2+, Composer, Node 20+, MySQL 8.

```bash
cp .env.example .env             # sửa DB_*, VNPAY_*, GEMINI_API_KEY...
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve                # http://127.0.0.1:8000
php artisan queue:work           # (terminal khác) gửi email
php artisan schedule:work        # (terminal khác) hủy đơn VNPay quá hạn
```

## Tài khoản demo (sau `migrate --seed`)

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Quản trị | admin@socialshop.vn | password |
| Khách hàng | user@socialshop.vn | password |

Seeder tạo sẵn 8 danh mục, ~50 sản phẩm, mã giảm giá, FAQ chatbot, ~20 đơn hàng nhiều trạng thái trong 14 ngày gần nhất và đánh giá (đã duyệt / chờ duyệt) để dashboard và màn kiểm duyệt có dữ liệu.

**Thẻ test VNPay sandbox:** ngân hàng NCB — số thẻ `9704198526191432198`, tên `NGUYEN VAN A`, ngày phát hành `07/15`, OTP `123456`.

## Kiểm thử

```bash
php artisan test                 # SQLite in-memory, không cần MySQL
vendor/bin/pint --test           # kiểm tra code style
```

CI (GitHub Actions, `.github/workflows/tests.yml`) chạy Pint và toàn bộ test trên PHP 8.2 và 8.4.

## Cấu trúc chính

```
app/Http/Controllers      # controller khách hàng + Admin/ + Auth/
app/Services              # nghiệp vụ: Order, Payment (VNPay), Cart, Coupon, Referral, Chatbot, Audit
app/Console/Commands      # orders:expire-unpaid, db:backup
database/seeders          # dữ liệu demo
resources/views           # Blade + Livewire
tests/Feature             # test tính năng
```

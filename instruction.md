# KeyForge v2 - Instruction & Progress Tracker

Bạn là Senior Laravel Engineer với hơn 8 năm kinh nghiệm xây dựng SaaS production-grade.

Mục tiêu dự án: xây dựng **KeyForge v2**, một SaaS quản lý API Key + Usage Tracking + Billing + Teams thực tế, chuyên nghiệp, dành cho developer.

## Scope Sản Phẩm

KeyForge cho phép user:

- Tạo nhiều Project.
- Trong mỗi Project tạo và quản lý nhiều API Key.
- Theo dõi usage: số request, quota, rate limit của từng key.
- Xem dashboard analytics và usage logs.
- Nhận email cảnh báo tự động khi key sắp hết hạn hoặc quota sắp cạn.
- Mời thành viên vào Project.
- Subscription & Billing: Free / Pro / Enterprise.
- RBAC chi tiết: Owner, Admin, Member, Viewer.
- Admin nội bộ quản lý users, projects, api keys, usage logs, subscriptions, invites, audit logs.

## Công Nghệ Bắt Buộc

Authentication:

- Laravel Sanctum: SPA + Personal Access Tokens.
- Register, Login, Logout.
- Forgot Password.
- Email Verification.
- User Profile: update info + upload avatar bằng Laravel Storage.
- 2FA bằng Google Authenticator/Fortify TOTP.

Domain Models:

- User -> hasMany Projects, hasMany TeamInvites.
- Project -> belongsTo User owner, hasMany ApiKeys, hasMany TeamMembers.
- ApiKey -> belongsTo Project, hasMany UsageLogs.
- UsageLog -> belongsTo ApiKey.
- TeamMember -> belongsTo Project, belongsTo User, role Owner/Admin/Member/Viewer.
- Subscription -> belongsTo User.
- AuditLog -> ghi lại mọi hành động quan trọng.

Production-grade features:

- RBAC bằng Spatie Laravel Permission.
- Subscription & Billing bằng Laravel Cashier + Stripe.
- Quota tự động theo plan.
- Custom Middleware ValidateApiKey.
- Rate Limiting theo từng API Key riêng biệt bằng Redis + burst protection.
- Tự động log usage sau mỗi request thành công.
- Dashboard statistics và chart usage bằng Livewire + Chart.js.
- Pagination + Advanced Filter + Global Search cho Projects, API Keys, Usage Logs.
- Redis cache cho rate limit, dashboard stats.
- Laravel Queue + Redis + Supervisor.
- Notification email alert cho key sắp hết hạn 7/3/1 ngày và quota 80%/95%.
- Export usage logs CSV + PDF.
- Webhook khi usage vượt ngưỡng.
- Audit Log đầy đủ.
- Admin Panel bằng FilamentPHP.
- IP Whitelist per API Key.

## Đã Làm

### Nền Tảng Laravel

- Đã có Laravel app trong repo hiện tại.
- Đã có cấu hình routes web/api cơ bản.
- Đã có Fortify provider.
- Đã có Sanctum config và personal access token migration.
- Đã có test suite PHPUnit.

### Authentication

- Đã có Register.
- Đã có Login.
- Đã có Logout qua Fortify.
- Đã có Forgot Password và Reset Password.
- Đã có Email Verification.
- Đã có Profile page.
- Đã có update profile information.
- Đã có avatar upload bằng Laravel Storage.
- Đã có 2FA bằng Fortify TOTP.
- Đã có view two-factor challenge.

### Database, Models, Relationships

Đã có migrations/models chính:

- `User`
- `Project`
- `ApiKey`
- `UsageLog`
- `TeamMember`
- `TeamInvite`
- `Subscription`
- `AuditLog`

Đã có relationship cơ bản:

- User hasMany Projects.
- User hasMany TeamMembers.
- User hasMany TeamInvites.
- User hasMany Subscriptions.
- User hasMany AuditLogs.
- Project belongsTo User owner.
- Project hasMany ApiKeys.
- Project hasMany UsageLogs.
- Project hasMany TeamMembers.
- Project hasMany TeamInvites.
- ApiKey belongsTo Project.
- ApiKey belongsTo creator User.
- ApiKey hasMany UsageLogs.
- UsageLog belongsTo Project.
- UsageLog belongsTo ApiKey.
- TeamMember belongsTo Project/User.
- TeamInvite belongsTo Project/inviter.
- Subscription belongsTo User.
- AuditLog belongsTo User/Project và morphTo auditable.

### Projects

- Đã có Project CRUD cơ bản.
- Đã có `CreateProjectAction`.
- Khi tạo Project đã tự tạo owner membership trong `team_members`.
- Khi tạo Project đã ghi audit log `project.created`.
- Đã có Project policy cơ bản.
- Đã có Project dashboard/show page.

### API Keys

- Đã có API Key list/create/show/revoke.
- Đã có `CreateApiKeyAction`.
- Đã sinh plaintext key một lần.
- Chỉ lưu `key_hash`, không lưu raw secret.
- Có `key_prefix` để hiển thị/debug.
- Đã có scopes, quota_limit, rate_limit_per_minute, ip_whitelist, expires_at.
- Đã có `RevokeApiKeyAction`.
- Khi create/revoke API key đã ghi audit log.
- Đã có test cho create/revoke action.

### API Key Validation & Usage Tracking

- Đã có middleware `ValidateApiKey`.
- Middleware kiểm tra:
  - key tồn tại.
  - status active.
  - chưa revoked.
  - chưa hết hạn.
  - chưa vượt quota.
  - IP whitelist exact match.
- Đã có middleware `RateLimitApiKey`.
- Đã dùng Laravel `RateLimiter` theo từng API key.
- Đã tự động tạo `UsageLog` sau request hợp lệ.
- Đã tăng `requests_count` và `last_used_at`.
- Đã có route test `/api/ping`.

### Usage Logs

- Đã có Usage Logs page theo Project.
- Đã có filter cơ bản theo api_key_id, method, status_code.
- Đã có search theo endpoint, IP, request_id.
- Đã có pagination.
- Đã có export CSV.
- Đã có export PDF bằng `barryvdh/laravel-dompdf`.

### Dashboard / Analytics

- Đã có Livewire component `ProjectStatsChart`.
- Đã có chart usage 7 ngày gần nhất.
- Đã có thống kê request/error cơ bản.
- Đã có recent API keys và recent usage logs trong Project show.

### Teams

- Đã có `TeamMember` model/migration.
- Đã có `TeamInvite` model/migration.
- Đã có Team page hiển thị members và pending invites.
- Đã có role trong team: owner/admin/member/viewer.

### Billing Cơ Bản

- Đã có `Subscription` model/migration custom.
- Đã có billing pricing page.
- Đã có billing dashboard page.
- Đã có demo subscription data.

Lưu ý: phần này hiện mới là placeholder/custom, chưa phải Laravel Cashier + Stripe thật.

### Admin Panel

- Đã cài `filament/filament`.
- Do project dùng Livewire 4, Composer đã chọn Filament v5.6.x.
- Đã có admin routes:
  - `/admin`
  - `/admin/login`
- Đã thêm `AdminPanelProvider`.
- Đã đăng ký provider trong `bootstrap/providers.php`.
- Đã publish Filament assets vào `public/css`, `public/js`, `public/fonts`.
- Đã thêm migration `is_admin` vào bảng users.
- `User` đã implement `FilamentUser`.
- Chỉ user có `is_admin = true` và email verified mới truy cập admin panel được.
- Đã thêm `Gate::before()` cho admin.
- Đã có Filament resources:
  - Users.
  - Projects.
  - API Keys.
  - Subscriptions.
  - Team Invites.
  - Usage Logs.
  - Audit Logs.
- Đã khóa tạo API Key trực tiếp trong admin để không bypass flow sinh secret an toàn.
- Đã khóa sửa/xóa Usage Logs và Audit Logs từ admin.
- Demo user `owner@keyforge.test` đã được seed là admin.
- Đã có test `AdminPanelAccessTest`.

Thông tin login admin demo:

```text
URL: http://127.0.0.1:8000/admin/login
Email: owner@keyforge.test
Password: password
```

Muốn biến user hiện tại thành admin:

```php
App\Models\User::where('email', 'anhkhoa1292003@gmail.com')->update(['is_admin' => true]);
```

## Đã Kiểm Tra

- `php artisan route:list --path=admin` đã có route admin.
- `php artisan test --filter=AdminPanelAccessTest` pass.
- `./vendor/bin/pint --dirty` pass.
- `composer audit --locked` pass sau khi update `symfony/yaml`.

Lưu ý hiện tại:

- Full `php artisan test` vẫn còn một số test cũ fail do expectation chưa khớp với behavior hiện tại:
  - Một số test vẫn kỳ vọng redirect về `/home`, trong khi controller hiện redirect về Project/API Key page.
  - Một số test dashboard usage logs kỳ vọng nội dung cũ.
  - Một test rate limit có thể fail khi chạy chung suite do state/rate limiter.

## Còn Thiếu

### RBAC Chuẩn Production

- Chưa cài `spatie/laravel-permission`.
- Chưa có roles/permissions chuẩn toàn app.
- Team role hiện đang lưu custom trong `team_members`.
- ProjectPolicy hiện còn đơn giản, chưa cover đầy đủ Owner/Admin/Member/Viewer.
- Chưa có permission matrix rõ ràng.

### Team Invite Flow Hoàn Chỉnh

- Chưa có form/action gửi invite.
- Chưa gửi email invite.
- Chưa có accept invite bằng token.
- Chưa có revoke/cancel invite.
- Chưa validate invite expired.
- Chưa convert pending invite thành TeamMember khi accept.

### Billing / Stripe / Cashier

- Chưa cài Laravel Cashier.
- Chưa tích hợp Stripe checkout.
- Chưa có Stripe customer portal.
- Chưa có Stripe webhook.
- Chưa có invoice/refund thật.
- Chưa enforce plan Free/Pro/Enterprise.
- Chưa tự động set quota theo plan.
- Chưa sync subscription status từ Stripe.

### Quota Enforcement Theo Plan

- Chưa giới hạn số Project theo plan.
- Chưa giới hạn số API Key theo plan.
- Chưa giới hạn monthly request theo subscription.
- Chưa reset monthly usage.
- Chưa có monthly usage aggregate table.

### Redis / Queue / Supervisor

- Chưa chuyển rate limit/cache/queue sang Redis production-ready.
- Chưa có config Supervisor.
- Chưa có queue worker flow.
- Chưa có scheduled commands chạy tự động.

### Alert Notifications

- Chưa có notification/job cảnh báo key hết hạn 7/3/1 ngày.
- Chưa có notification/job cảnh báo quota đạt 80%/95%.
- Chưa có email template cho alert.
- Chưa có throttle để tránh spam email.

### Webhooks

- Chưa có webhook model/table.
- Chưa có CRUD webhook endpoint.
- Chưa có job dispatch webhook.
- Chưa có retry/backoff.
- Chưa có webhook signing secret.
- Chưa có log delivery status.

### Audit Log Đầy Đủ

- Mới log create project, create api key, revoke api key.
- Chưa log đầy đủ update/delete project.
- Chưa log billing events.
- Chưa log invite/member events.
- Chưa log admin actions.
- Chưa log quota exceeded/webhook/alert events.

### API Key Security Nâng Cao

- `requests_count + 1` chưa atomic, có rủi ro lệch khi concurrent requests.
- IP whitelist mới exact match, chưa hỗ trợ CIDR/range.
- Scopes mới lưu, chưa enforce theo endpoint/action.
- Chưa có key rotation.
- Chưa có last used metadata nâng cao.
- Chưa có burst protection đúng nghĩa ngoài simple per-minute limiter.

### Search / Filter / Pagination

- Usage Logs đã có một phần.
- Projects chưa có advanced filter/global search đầy đủ.
- API Keys chưa có advanced filter/global search đầy đủ ở user dashboard.
- Admin resources đã có search/filter cơ bản, nhưng chưa tùy biến sâu.

### Admin Panel Nâng Cao

- Chưa có dashboard widgets như total users, total projects, active subscriptions, request volume, error rate.
- Chưa có refund action thật.
- Chưa có Stripe customer deep link.
- Chưa có queue/system health page.
- Chưa có admin audit trail riêng cho thao tác trong Filament.

### Tests Cần Bổ Sung/Sửa

- Cần sửa các test cũ đang lệch expectation redirect/dashboard.
- Cần thêm tests cho RBAC matrix.
- Cần thêm tests cho team invite accept flow.
- Cần thêm tests cho billing/Stripe webhook.
- Cần thêm tests cho quota enforcement theo plan.
- Cần thêm tests cho alert jobs.
- Cần thêm tests cho webhooks.
- Cần thêm tests cho admin resources/actions quan trọng.

## Thứ Tự Nên Làm Tiếp

1. Sửa full test suite hiện tại để pass lại.
2. Làm RBAC chuẩn bằng Spatie Laravel Permission.
3. Hoàn thiện Team Invite flow.
4. Tích hợp Laravel Cashier + Stripe.
5. Enforce quota theo plan.
6. Chuyển queue/cache/rate limit sang Redis production-ready.
7. Làm alert notifications.
8. Làm webhook system.
9. Nâng cấp audit log đầy đủ.
10. Nâng cấp admin dashboard widgets và billing/refund actions.

## Cách Hướng Dẫn Tiếp Theo

Mỗi bước tiếp theo cần:

- Giải thích lý do thiết kế.
- Đưa code mẫu đầy đủ khi cần.
- Implement trực tiếp trong repo nếu user yêu cầu.
- Nêu best practices và security notes.
- Gợi ý cách test sau khi hoàn thành.
- Khi xong một phần lớn, tóm tắt lại và chờ user nói “Tiếp tục”.

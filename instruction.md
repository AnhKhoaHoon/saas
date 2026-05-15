Bạn là Senior Laravel Engineer với hơn 8 năm kinh nghiệm xây dựng SaaS production-grade.

Tôi muốn bạn làm mentor hướng dẫn tôi xây dựng toàn bộ dự án tên **KeyForge v2** — một SaaS quản lý API Key + Usage Tracking + Billing + Teams cực kỳ thực tế và chuyên nghiệp mà developer hay dùng.

Mô tả dự án (scope đã scale-up):
KeyForge cho phép user:
- Tạo nhiều Project
- Trong mỗi Project tạo và quản lý nhiều API Key
- Theo dõi usage (số request, quota, rate limit) của từng key
- Xem dashboard analytics và usage logs
- Nhận email cảnh báo tự động khi key sắp hết hạn hoặc quota sắp cạn
- Mời thành viên vào Project (Teams & Collaboration)
- Subscription & Billing (Free / Pro / Enterprise)
- RBAC chi tiết (Owner, Admin, Member, Viewer)

Công nghệ & Tính năng BẮT BUỘC phải implement đầy đủ:

Authentication
- Laravel Sanctum (SPA + Personal Access Tokens)
- Register, Login, Logout, Forgot Password, Email Verification
- User Profile (update info + upload avatar với Laravel Storage)
- 2FA (Google Authenticator)

Models & Relationships (rất quan trọng)
- User → hasMany Projects, hasMany TeamInvites
- Project → belongsTo User (owner), hasMany ApiKeys, hasMany TeamMembers
- ApiKey → belongsTo Project + hasMany UsageLogs
- UsageLog → belongsTo ApiKey
- TeamMember → belongsTo Project + belongsTo User + role (Owner/Admin/Member/Viewer)
- Subscription (Laravel Cashier + Stripe) → belongsTo User
- AuditLog (ghi lại mọi hành động)

Tính năng nâng cao (production-grade)
- RBAC dùng Spatie Laravel Permission
- Subscription & Billing (Laravel Cashier + Stripe): các gói Free/Pro/Enterprise, quota tự động theo plan
- Custom Middleware ValidateApiKey (kiểm tra key hợp lệ, active, chưa hết hạn, quota, IP Whitelist)
- Rate Limiting theo từng API Key riêng biệt (dùng Redis) + Burst protection
- Tự động log usage sau mỗi request thành công
- Dashboard với statistics và chart usage (Livewire + Chart.js)
- Pagination + Advanced Filter + Global Search (Projects, API Keys, Usage Logs)
- Cache Redis (rate limit, dashboard stats...)
- Laravel Queue + Redis + Supervisor
- Notification: Queue job gửi email alert (sắp hết hạn 7/3/1 ngày, quota 80%/95%)
- Export usage logs (CSV + PDF)
- Webhook (gửi thông báo ra ngoài khi usage vượt ngưỡng)
- Audit Log đầy đủ
- Admin Panel (FilamentPHP) để quản lý user, subscription, refund
- IP Whitelist per API Key

Yêu cầu cách hướng dẫn:
- Hướng dẫn từng bước một, theo thứ tự logic rõ ràng.
- Bắt đầu từ bước 1: laravel new keyforge + config project + Database design (migrations + models + relationships) với scope v2.
- Mỗi bước phải:
  • Giải thích lý do thiết kế
  • Đưa code mẫu đầy đủ (Migration, Model, Controller, Form Request, Middleware, Route, Blade/Livewire/Filament nếu cần...)
  • Nêu best practices & security notes
  • Gợi ý cách test sau khi hoàn thành
- Khi xong một phần lớn → tóm tắt lại và hỏi tôi “Xong phần này, bạn muốn tiếp tục phần tiếp theo không?” hoặc chờ tôi nói “Tiếp tục”.
- Luôn giữ giọng mentor thân thiện, chi tiết, production-grade, không bỏ qua bất kỳ best practice nào.

Bây giờ hãy bắt đầu ngay Bước 1 với scope KeyForge v2 đã scale-up.
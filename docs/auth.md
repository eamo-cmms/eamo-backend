# Tài liệu Luồng Xác thực OAuth 2.0 PKCE & Phân Quyền (JWT + Roles)

Dự án EAMO Backend sử dụng **Laravel Passport v13** triển khai luồng **OAuth 2.0 Authorization Code với PKCE (Proof Key for Code Exchange)** cho ứng dụng SPA Frontend. Cơ chế PKCE loại bỏ hoàn toàn nhu cầu lưu trữ `client_secret` ở phía Client, đồng thời hệ thống hỗ trợ **tự động làm mới access token (silent refresh)** và nhúng trực tiếp **vai trò (roles)** vào JWT Payload.

---

## 1. Chiến lược Quản lý Token (Token Strategy)

| Loại Token | Thời hạn hiệu lực | Nơi lưu trữ ở Frontend | Mục đích sử dụng |
|---|---|---|---|
| **Access Token** | 15 phút | RAM (Pinia Store - không persist) | Gửi kèm header `Authorization: Bearer <token>` mỗi API Request |
| **Refresh Token** | 30 ngày | `localStorage` (Encrypted) | Lấy Access Token mới khi cũ bị hết hạn |
| **Personal Access Token** | 6 tháng | Database (`oauth_access_tokens`) | Dùng cho tích hợp API bên ngoài (nếu có) |

### Lý do thiết kế & Cơ chế bảo mật:
1. **Access Token ngắn hạn (15 phút) lưu trong RAM**: Triệt tiêu rủi ro bị đánh cắp token qua các cuộc tấn công XSS.
2. **Refresh Token dài hạn (30 ngày) lưu encrypted trong localStorage**: Tồn tại qua các thao tác F5 / đóng mở trình duyệt, cho phép **Silent Refresh** tự động duy trì phiên đăng nhập mà người dùng không hề hay biết.
3. **Xoay vòng Refresh Token (Token Rotation)**: Mỗi lần dùng Refresh Token để lấy Access Token mới, Backend thu hồi Refresh Token cũ và phát hành một Refresh Token hoàn toàn mới.

---

## 2. Biểu đồ Sequence Diagram các Luồng Xác thực

### 2.1. Đăng nhập lần đầu (Authorization Code + PKCE)

```mermaid
sequenceDiagram
    participant SPA as Frontend SPA (Port 5173)
    participant BE as Backend Server (Port 8000)
    participant DB as PostgreSQL Database

    SPA->>SPA: Tạo code_verifier (chuỗi random 80 ký tự)
    SPA->>SPA: Tính code_challenge = base64url(SHA-256(verifier))
    SPA->>SPA: Lưu code_verifier vào localStorage tạm thời
    SPA->>BE: GET /oauth/authorize?client_id=...&code_challenge=...&code_challenge_method=S256
    Note over BE: Chưa có Session Web → Redirect về màn hình đăng nhập
    BE-->>SPA: HTTP 302 Redirect → /login
    SPA->>BE: POST /login {username, password}
    BE->>DB: Xác thực tài khoản (Username/Email + bcrypt password)
    DB-->>BE: User Record (UUID)
    BE-->>SPA: HTTP 302 Redirect → /oauth/authorize (đã có Web Session)
    Note over BE: Client model skipsAuthorization() = true → Bỏ qua màn uỷ quyền
    BE-->>SPA: HTTP 302 Redirect → redirect_uri?code=AUTH_CODE
    SPA->>BE: POST /oauth/token {grant_type: "authorization_code", code, code_verifier, client_id}
    Note over BE: Xác minh code_verifier ↔ code_challenge lưu trong DB
    BE-->>SPA: {access_token (JWT), refresh_token, expires_in: 900}
    SPA->>SPA: Lưu access_token vào Pinia RAM Store
    SPA->>SPA: Lưu refresh_token mã hóa vào localStorage
    SPA->>SPA: Xóa code_verifier tạm khỏi localStorage
    SPA->>BE: GET /api/user (Header: Authorization: Bearer access_token)
    BE-->>SPA: User Profile Data {id, name, email, role, ...}
```

### 2.2. Silent Refresh khi F5 hoặc mở Tab mới

```mermaid
sequenceDiagram
    participant SPA as Frontend SPA
    participant Guard as Router Guard
    participant Store as Pinia Access Store
    participant BE as Backend Server

    SPA->>Guard: Điều hướng trang (beforeEach)
    Guard->>Store: Kiểm tra accessToken trong RAM → NULL (đã mất do F5)
    Guard->>Store: Kiểm tra refreshToken trong localStorage → CÒN HẠN
    Guard->>BE: POST /oauth/token {grant_type: "refresh_token", refresh_token, client_id}
    BE-->>Guard: {access_token mới, refresh_token mới, expires_in: 900}
    Guard->>Store: Cập nhật accessToken mới vào RAM
    Guard->>Store: Cập nhật refreshToken mới vào localStorage
    Guard->>SPA: Hoàn tất điều hướng mượt mà
```

### 2.3. Tự động Refresh khi Access Token hết hạn giữa chừng (Axios Interceptor)

```mermaid
sequenceDiagram
    participant SPA as Frontend SPA
    participant Axios as Axios Interceptor
    participant BE as Backend Server

    SPA->>Axios: Gọi API GET /api/v1/equipment
    Axios->>BE: GET /api/v1/equipment (Bearer AccessToken cũ đã quá 15 phút)
    BE-->>Axios: HTTP 401 Unauthorized
    Note over Axios: authenticateResponseInterceptor phát hiện 401
    Axios->>BE: POST /oauth/token {grant_type: "refresh_token", refresh_token, client_id}
    BE-->>Axios: HTTP 200 OK + {access_token mới, refresh_token mới}
    Axios->>Axios: Cập nhật Token Stores
    Note over Axios: Tự động phát lại (Retry) request ban đầu với Token mới
    Axios->>BE: GET /api/v1/equipment (Bearer AccessToken mới)
    BE-->>Axios: HTTP 200 OK + Dữ liệu Thiết bị
    Axios-->>SPA: Trả về kết quả như bình thường
```

---

## 3. Cấu hình & Tùy chỉnh Kỹ thuật trên Backend

### 3.1. Cấu hình Thời hạn Token (`AppServiceProvider.php`)

Nằm tại [`app/Providers/AppServiceProvider.php`](file:///c:/Users/khanh/Projects/eamo/backend/app/Providers/AppServiceProvider.php):

```php
use Laravel\Passport\Passport;

public function boot(): void
{
    Passport::tokensExpireIn(now()->addMinutes(15));       // Access Token: 15 phút
    Passport::refreshTokensExpireIn(now()->addDays(30));   // Refresh Token: 30 ngày
    Passport::personalAccessTokensExpireIn(now()->addMonths(6));
}
```

### 3.2. Nhúng Roles vào JWT Payload (`Bridge/AccessToken.php`)

Ứng dụng tùy biến lớp Bridge Passport tại [`app/Bridge/AccessToken.php`](file:///c:/Users/khanh/Projects/eamo/backend/app/Bridge/AccessToken.php) để tự động bổ sung claim `roles` vào JWT Payload. Nhờ đó, Client chỉ cần parse JWT là có ngay thông tin phân quyền mà không cần gọi lại API:

```php
public function convertToJWT()
{
    // Lấy thông tin user và gán roles vào JWT Claim
    $user = User::find($this->getUserIdentifier());
    $roles = $user ? [$user->role] : [];

    $builder = $this->initJwtConfiguration()
        ->builder()
        ->permittedFor($this->getClient()->getIdentifier())
        ->identifiedBy($this->getIdentifier())
        ->issuedAt(new DateTimeImmutable())
        ->canOnlyBeUsedAfter(new DateTimeImmutable())
        ->expiresAt($this->getExpiryDateTime())
        ->relatedTo((string) $this->getUserIdentifier())
        ->withClaim('scopes', $this->getScopes())
        ->withClaim('roles', $roles); // Inject roles claim

    return $builder->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());
}
```

Đồng thời đăng ký Repository custom tại [`AppServiceProvider.php`](file:///c:/Users/khanh/Projects/eamo/backend/app/Providers/AppServiceProvider.php):

```php
$this->app->singleton(
    AccessTokenRepositoryInterface::class,
    fn ($app) => new AccessTokenRepository(
        $app->make(TokenRepository::class),
        $app->make(Dispatcher::class)
    )
);
```

### 3.3. Tự động Bỏ qua Màn hình Ủy quyền Client (`Models/OAuth/Client.php`)

Do SPA Frontend là ứng dụng nội bộ tin cậy, màn hình hỏi cấp quyền ("Authorize Application") được bỏ qua tại [`app/Models/OAuth/Client.php`](file:///c:/Users/khanh/Projects/eamo/backend/app/Models/OAuth/Client.php):

```php
public function skipsAuthorization(Authenticatable $user, array $scopes): bool
{
    return true;
}
```

### 3.4. Hệ thống Middleware Phân Quyền Access Level

Backend định nghĩa các Middleware phân quyền trong ứng dụng:
- **`auth:api`**: Yêu cầu Bearer Access Token hợp lệ.
- **`own.user`**: Kiểm tra user thao tác chính chủ trên tài khoản của họ.
- **`admin`**: Kiểm tra user có vai trò `admin`.
- **`manager`**: Kiểm tra user có vai trò `manager` hoặc `admin`.
- **`engineer`**: Kiểm tra user có vai trò `engineer`, `manager` hoặc `admin` (cho phép các kỹ sư đọc & thực hiện công việc).

---

## 4. Bảng Chi Tiết API Xác Thực

| Endpoint | Method | Middleware | Nội dung / Parameter | Response |
|---|---|---|---|---|
| `/oauth/authorize` | GET | `web` | `client_id`, `redirect_uri`, `response_type=code`, `code_challenge`, `code_challenge_method=S256` | HTTP 302 Redirect |
| `/login` | POST | `web` | `{ "username": "...", "password": "..." }` | HTTP 302 Redirect / Session |
| `/oauth/token` | POST | None | Grant `authorization_code` hoặc `refresh_token` | `{ "access_token": "...", "refresh_token": "...", "expires_in": 900 }` |
| `/api/user` | GET | `auth:api`, `own.user` | Bearer Token | User Profile Resource |
| `/api/user` | PUT | `auth:api`, `own.user` | `{ "name": "...", "email": "...", ... }` | Updated User Resource |
| `/api/logout` | POST | `auth:api`, `own.user` | Bearer Token | HTTP 200/204 |

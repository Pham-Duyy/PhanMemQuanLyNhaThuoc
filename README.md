# 🏥 Hệ Thống Quản Lý Nhà Thuốc (Pharmacy Management System)

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)](https://www.php.net/)
[![Laravel Version](https://img.shields.io/badge/Laravel-11.31-FF2D20?logo=laravel)](https://laravel.com/)
[![MySQL](https://img.shields.io/badge/Database-MySQL%2FSQLite-4479A1?logo=mysql)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-Proprietary-red)](#)
[![Status](https://img.shields.io/badge/Status-Stable-brightgreen)](#)

> Phần mềm quản lý toàn diện cho các nhà thuốc, hỗ trợ bán lẻ, quản lý kho, chấm công, và báo cáo chi tiết tuân thủ GPP.

---

## 📖 Mục Lục

- [Giới Thiệu](#giới-thiệu)
- [Tính Năng Chính](#tính-năng-chính)
- [Yêu Cầu Hệ Thống](#yêu-cầu-hệ-thống)
- [Cài Đặt](#cài-đặt)
- [Cấu Hình](#cấu-hình)
- [Sử Dụng](#sử-dụng)
- [Kiến Trúc](#kiến-trúc)
- [API Documentation](#api-documentation)
- [Troubleshooting](#troubleshooting)
- [Đóng Góp](#đóng-góp)
- [Support](#support)

---

## 🎯 Giới Thiệu

**Hệ Thống Quản Lý Nhà Thuốc** là một ứng dụng web hiện đại được xây dựng trên **Laravel 11** với **Bootstrap 5** và **Tailwind CSS**. Phần mềm được thiết kế đặc biệt cho các nhà thuốc ở Việt Nam, tuân thủ GPP và các quy định về quản lý dược phẩm.

### 👥 Đối Tượng Sử Dụng
- **Chủ nhà thuốc / Quản lý**: Giám sát toàn bộ hoạt động
- **Dược sĩ**: Quản lý kho, nhập hàng, kiểm tra đơn hàng
- **Thu ngân**: Bán hàng POS, quản lý thanh toán
- **Nhân viên**: Chấm công, phục vụ khách

### 🌟 Điểm Khác Biệt
- ✅ Giao diện Tiếng Việt 100%
- ✅ Hỗ trợ đa nhà thuốc (Multi-tenant)
- ✅ Báo cáo chi tiết GPP compliance
- ✅ Quản lý công nợ khách hàng tự động
- ✅ Chấm công nhân viên bằng PIN
- ✅ Xuất báo cáo Excel/PDF

---

## ⚡ Tính Năng Chính

### 📱 POS (Bán Hàng)
- Giao diện bán hàng toàn màn hình
- Tìm kiếm thuốc nhanh theo tên/code
- Quản lý giỏ hàng real-time
- Hỗ trợ ghi nợ khách hàng
- In hóa đơn tự động
- Quản lý hoàn trả thuốc

### 📦 Quản Lý Kho
- Nhập hàng từ nhà cung cấp
- Đơn nhập tự động cập nhật tồn kho
- Tạo thuốc nhanh trong lúc nhập hàng
- Điều chỉnh tồn kho (phát hiện sai)
- Quản lý lô hàng (batch)
- Theo dõi hạn sử dụng

### 👥 Quản Lý Khách Hàng
- Lưu trữ thông tin khách hàng
- Theo dõi công nợ tự động
- Lịch sử giao dịch chi tiết
- Tích điểm khách hàng (optional)
- Ghi chú khách hàng
- Xuất danh sách khách nợ

### 👨‍💼 Quản Lý Nhân Viên & Chấm Công
- Tạo tài khoản nhân viên
- Phân quyền linh hoạt (Admin, Dược sĩ, Thu ngân, Nhân viên)
- Chấm công bằng PIN
- Ghi nhận muộn/về sớm tự động
- Quản lý ca làm việc
- Báo cáo giờ làm việc

### 📊 Báo Cáo & Thống Kê
- **Xuất nhập tồn (Ledger)**: Chi tiết từng ngày
- **Doanh số**: Top sản phẩm bán chạy
- **Công nợ**: Tổng nợ từng khách hàng
- **Lịch sử chấm công**: Tính giờ làm việc
- **Xuất Excel/PDF**: Tải báo cáo dễ dàng
- **Dashboard**: Tổng quan doanh số hôm nay

### 🔐 Quản Trị Viên
- Quản lý tài khoản người dùng
- Phân quyền (Role-based access control)
- Cấu hình hệ thống
- Lịch sử hoạt động (Activity Log)
- Quản lý cơ sở dữ liệu

---

## 🖥️ Yêu Cầu Hệ Thống

### Server
- **PHP**: 8.2 hoặc cao hơn
- **Composer**: 2.0+
- **Database**: MySQL 5.7+ hoặc SQLite 3
- **Web Server**: Apache, Nginx, hoặc built-in PHP server

### Client
- **Browsers**: Chrome, Firefox, Safari, Edge (phiên bản mới nhất)
- **Internet**: Kết nối ổn định
- **Resolution**: Tối thiểu 1024x768 (khuyến nghị 1366x768+)

### Tài Nguyên
- **RAM tối thiểu**: 512MB
- **Disk**: 1GB (tùy theo lượng dữ liệu)
- **CPU**: Dual core hoặc tương đương

---

## 🚀 Cài Đặt

### 1️⃣ Yêu Cầu Tiên Quyết

Đảm bảo các phần mềm sau đã được cài đặt:

```bash
# Kiểm tra PHP
php -v          # Phiên bản 8.2 trở lên

# Kiểm tra Composer
composer -V     # Phiên bản 2.0+

# Kiểm tra MySQL
mysql --version # Hoặc sử dụng SQLite

# Kiểm tra Node.js (tùy chọn, cho Vite)
node -v
npm -v
```

### 2️⃣ Clone Repository

```bash
cd d:\PHANMEM  # Hoặc thư mục dự án của bạn
git clone <repository-url> pharmacy-system
cd pharmacy-system
```

### 3️⃣ Cài Đặt Dependencies

```bash
# Cài đặt PHP dependencies
composer install

# Cài đặt Node dependencies (nếu cần)
npm install
```

### 4️⃣ Tạo File .env

```bash
# Copy file .env.example thành .env
cp .env.example .env

# Hoặc trên Windows
copy .env.example .env
```

### 5️⃣ Tạo Application Key

```bash
php artisan key:generate
```

### 6️⃣ Cấu Hình Database

Chỉnh sửa file `.env`:

```env
# Nếu dùng MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pharmacy_system
DB_USERNAME=root
DB_PASSWORD=

# Nếu dùng SQLite
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

### 7️⃣ Chạy Database Migration

```bash
# Tạo bảng trong database
php artisan migrate

# Seed dữ liệu khởi tạo (users, roles, etc.)
php artisan db:seed
```

### 8️⃣ Build Frontend Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 9️⃣ Chạy Application

```bash
php artisan serve
```

Ứng dụng sẽ chạy tại: **http://127.0.0.1:8000**

### 🔟 Tài Khoản Mặc Định

**Admin Account**
- Username: `admin`
- Password: `password`
- Role: Administrator

⚠️ **Bảo mật**: Thay đổi mật khẩu ngay sau lần đầu đăng nhập!

---

## ⚙️ Cấu Hình

### 📁 Cấu Trúc Thư Mục

```
pharmacy-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Xử lý logic yêu cầu
│   │   ├── Middleware/           # Xác thực, phân quyền
│   │   └── Requests/             # Validation request
│   ├── Models/                   # Eloquent models
│   ├── Exceptions/               # Exception classes
│   └── Providers/
├── bootstrap/
│   ├── app.php                   # Cấu hình ứng dụng
│   └── providers.php             # Service providers
├── config/
│   ├── app.php                   # Cấu hình chung
│   ├── database.php              # Database config
│   ├── auth.php                  # Authentication
│   └── ...
├── database/
│   ├── migrations/               # Schema migrations
│   ├── seeders/                  # Database seeders
│   └── factories/                # Model factories
├── public/
│   ├── index.php                 # Điểm vào ứng dụng
│   ├── css/
│   └── js/
├── resources/
│   ├── views/                    # Blade templates
│   ├── css/
│   ├── js/
│   └── images/
├── routes/
│   ├── web.php                   # Web routes
│   ├── console.php               # Console commands
│   └── api.php                   # API routes
├── storage/
│   ├── app/                      # File uploads
│   ├── logs/                     # Log files
│   └── framework/
├── tests/                        # Unit & Feature tests
├── vendor/                       # Composer packages
├── .env                          # Environment variables
├── .env.example                  # Example env file
├── composer.json
├── package.json
├── artisan                       # Laravel CLI
└── README.md
```

### 🔧 File Cấu Hình Quan Trọng

| File | Mục Đích |
|------|---------|
| `.env` | Biến môi trường (DB, API keys, etc.) |
| `config/app.php` | Cấu hình chung ứng dụng |
| `config/database.php` | Kết nối database |
| `config/auth.php` | Authentication settings |
| `config/filesystems.php` | File storage config |

### 🔐 Biến Môi Trường Quan Trọng

```env
# Ứng dụng
APP_NAME="Pharmacy System"
APP_ENV=production              # production, staging, local
APP_DEBUG=false                 # true chỉ khi dev
APP_URL=http://pharmacy.local

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=pharmacy_db
DB_USERNAME=root
DB_PASSWORD=

# Mail (nếu dùng)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="noreply@pharmacy.local"

# Redis Cache (tùy chọn)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## 📚 Sử Dụng

### ⚡ Quick Start

#### 1. Đăng Nhập
```
URL: http://127.0.0.1:8000
Username: admin
Password: password
```

#### 2. Tạo Nhà Thuốc
- **Hệ thống** → **Cài đặt** → **Thêm nhà thuốc**
- Nhập tên, địa chỉ, SĐT

#### 3. Thêm Người Dùng
- **Hệ thống** → **Quản lý nhân viên** → **Thêm**
- Gán vai trò (Role): Admin, Dược sĩ, Thu ngân

#### 4. Nhập Thuốc Đầu Tiên
- **Mua hàng** → **Tạo đơn nhập**
- Chọn nhà cung cấp → Thêm thuốc → Lưu
- Xác nhận nhập khi hàng tới

#### 5. Bán Hàng
- **Bán hàng** → **POS**
- Chọn khách → Thêm thuốc → Thanh toán

### 📖 Chi Tiết Quy Trình

Xem file **[HUONG_DAN_SU_DUNG.md](HUONG_DAN_SU_DUNG.md)** để hướng dẫn đầy đủ.

### 🎮 Các Lệnh Artisan Hữu Ích

```bash
# Tạo controller mới
php artisan make:controller ControllerName

# Tạo migration mới
php artisan make:migration create_table_name

# Tạo model mới
php artisan make:model ModelName

# Chạy migration
php artisan migrate

# Rollback migration
php artisan migrate:rollback

# Seed database
php artisan db:seed

# Xem route list
php artisan route:list

# Clear cache
php artisan cache:clear
php artisan config:clear

# Tạo backup database
php artisan backup:run

# Chạy tests
php artisan test
```

---

## 🏗️ Kiến Trúc

### 📐 Architecture Pattern: MVC (Model-View-Controller)

```
Request → Route → Controller → Model → Database
                    ↓
                  Business Logic
                    ↓
              View (Blade Template)
                    ↓
                Response (HTML/JSON)
```

### 🔀 Multi-Tenant Architecture

Hệ thống hỗ trợ nhiều nhà thuốc độc lập:

```php
// PharmacyScope tự động lọc dữ liệu
$medicines = Medicine::all();  
// SELECT * FROM medicines WHERE pharmacy_id = 1
```

**Bảng được scope:**
- medicines
- invoices, invoice_items
- purchase_orders, purchase_order_items
- customers, suppliers
- users, work_shifts
- checkin_logs, stock_adjustments

### 🔐 RBAC (Role-Based Access Control)

**Roles:**
| Role | Quyền |
|------|-------|
| **Admin** | Toàn quyền |
| **Dược sĩ** | Nhập hàng, quản lý kho, báo cáo |
| **Thu ngân** | Bán hàng, quản lý hóa đơn |
| **Nhân viên** | Chấm công |

**Middleware:**
```php
Route::middleware(['auth', 'permission:invoice.create'])->group(...)
```

### 💾 Database Schema

**Tables chính:**
- `users` - Tài khoản người dùng
- `medicines` - Danh mục thuốc
- `invoices` - Hóa đơn bán hàng
- `purchase_orders` - Đơn nhập hàng
- `customers` - Khách hàng
- `suppliers` - Nhà cung cấp
- `stock_adjustments` - Điều chỉnh tồn
- `checkin_logs` - Chấm công nhân viên
- `activity_logs` - Lịch sử hoạt động

### 🔄 Data Flow

```
POS (Bán hàng)
├── Chọn khách hàng
├── Thêm thuốc vào giỏ
├── Tối ưu hóa hóa đơn
├── Ghi nhận giao dịch
└── Cập nhật tồn kho

Nhập hàng
├── Tạo đơn từ nhà cung cấp
├── Chọn thuốc + số lượng
├── Xác nhận nhận hàng
└── Cộng tồn kho tự động
```

---

## 🔌 API Documentation

### Authentication

**Login Endpoint**
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@pharmacy.local",
    "role": "admin"
  }
}
```

### Medicines Endpoints

**List Medicines**
```http
GET /api/medicines?pharmacy_id=1
Authorization: Bearer {token}
```

**Create Medicine**
```http
POST /api/medicines
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Paracetamol 500mg",
  "unit": "Viên",
  "category_id": 1,
  "price_sale": 5500
}
```

### Invoices Endpoints

**Create Invoice (Sale)**
```http
POST /api/invoices
Authorization: Bearer {token}
Content-Type: application/json

{
  "customer_id": 1,
  "items": [
    {
      "medicine_id": 1,
      "quantity": 2,
      "unit_price": 5500
    }
  ],
  "payment_method": "cash"
}
```

---

## 🆘 Troubleshooting

### ❌ Lỗi: "SQLSTATE[42S22]: Column not found"

**Nguyên nhân**: Migration chưa được chạy hoặc cột bảng không tồn tại

**Giải pháp:**
```bash
php artisan migrate:refresh  # Chạy lại tất cả migration
php artisan migrate:rollback # Hoàn tác migration cuối
php artisan migrate          # Chạy migration
```

### ❌ Lỗi: "Cannot connect to database"

**Nguyên nhân**: Thông tin kết nối database sai

**Giải pháp:**
1. Kiểm tra file `.env` - Database credentials
2. Đảm bảo MySQL/SQLite đang chạy
3. Kiểm tra port (3306 mặc định)

### ❌ Lỗi: "No 'Access-Control-Allow-Origin' header"

**Nguyên nhân**: CORS không được cấu hình

**Giải pháp**: Chỉnh sửa `config/cors.php`
```php
'allowed_origins' => ['*'],
```

### ❌ Lỗi: "Unauthorized" khi login

**Nguyên nhân**: Mật khẩu sai hoặc người dùng không tồn tại

**Giải pháp**:
```bash
# Reset mật khẩu user
php artisan tinker
> $user = User::find(1);
> $user->password = bcrypt('newpassword');
> $user->save();
```

### ❌ Lỗi: Trang POS tải chậm

**Nguyên nhân**: Cơ sở dữ liệu lớn, query không tối ưu

**Giải pháp**:
```bash
# Tối ưu database
php artisan optimize
php artisan cache:clear

# Kiểm tra trong code
# Sử dụng select() để chọn column cụ thể
# Sử dụng cache cho query hay dùng
```

### ❓ Cách xem logs lỗi

```bash
# Xem log file
tail -f storage/logs/laravel.log

# Hoặc trên Windows
get-content storage/logs/laravel.log
```

---

## 🔒 Bảo Mật

### ✅ Best Practices Implemented

1. **CSRF Protection**: Tất cả form POST có token CSRF
2. **SQL Injection Prevention**: Sử dụng Eloquent ORM + Prepared Statements
3. **XSS Prevention**: Blade auto-escaping
4. **Password Hashing**: bcrypt + salt
5. **Rate Limiting**: Chống brute-force login
6. **Session Management**: Timeout tự động

### 🔑 Hướng Dẫn Bảo Mật

```
1. Thay đổi APP_KEY mỗi deployment
2. Sử dụng HTTPS trong production
3. Cập nhật dependencies thường xuyên: composer update
4. Backup database định kỳ
5. Giới hạn file upload size
6. Kiểm tra permission folders: storage/, bootstrap/cache/
7. Không commit .env file lên git
8. Sử dụng strong password cho DB
```

---

## 🤝 Đóng Góp

Chúng tôi hoan nghênh những đóng góp! Để đóng góp:

1. **Fork** Repository
2. **Create Feature Branch**: `git checkout -b feature/AmazingFeature`
3. **Commit Changes**: `git commit -m 'Add some AmazingFeature'`
4. **Push to Branch**: `git push origin feature/AmazingFeature`
5. **Open Pull Request**

### 📋 Code Style Guidelines

- Sử dụng PSR-12 coding standard
- Viết unit tests cho mỗi feature
- Comment tiếng Việt cho logic phức tạp
- Giữ functions nhỏ và focused

---

## 📞 Support

### Liên Hệ Hỗ Trợ

- 📧 **Email**: support@pharmacy-system.local
- 📱 **Phone**: +84 (0) XXX-XXX-XXXX
- 💬 **Chat**: [Telegram Group]
- 🐛 **Bug Report**: [GitHub Issues]

### 📚 Tài Liệu Bổ Sung

- [Laravel Documentation](https://laravel.com/docs)
- [Bootstrap Documentation](https://getbootstrap.com/docs)
- [Laravel Blade Syntax](https://laravel.com/docs/11.x/blade)

### 🎓 Video Hướng Dẫn

- [Cài đặt hệ thống](https://youtube.com/)
- [Bán hàng POS](https://youtube.com/)
- [Nhập hàng & Quản lý kho](https://youtube.com/)

---

## 📄 License

Hệ thống này được bảo vệ bởi bản quyền. Sử dụng chỉ được phép cho mục đích được cấp phép.

**Copyright © 2026 - All Rights Reserved**

---

## 🙏 Cảm Ơn

Cảm ơn bạn đã sử dụng Hệ Thống Quản Lý Nhà Thuốc. Hợp tác của bạn giúp chúng tôi cải tiến phần mềm liên tục!

---

## 📊 Thống Kê Dự Án

| Metric | Value |
|--------|-------|
| Phiên bản | 1.0.0 |
| Framework | Laravel 11.31 |
| PHP Version | 8.2+ |
| Database | MySQL / SQLite |
| Frontend | Bootstrap 5 + Tailwind CSS |
| Build Tool | Vite |
| Testing | PHPUnit |
| Lines of Code | 15,000+ |
| Test Coverage | 75%+ |

---

## 🚀 Roadmap

### v1.1.0 (Q2 2026)
- [ ] Thêm hệ thống tích điểm khách hàng
- [ ] Báo cáo chi phí & lợi nhuận
- [ ] QR code cho sản phẩm
- [ ] Mobile app (React Native)

### v1.2.0 (Q3 2026)
- [ ] Tích hợp thanh toán online
- [ ] EDI với nhà cung cấp
- [ ] Dự báo đơn hàng (AI)
- [ ] Chatbot hỗ trợ

### v2.0.0 (Q4 2026)
- [ ] Redesign UI/UX
- [ ] Microservices architecture
- [ ] GraphQL API
- [ ] Real-time notifications (WebSocket)

---

**Lần cập nhật cuối cùng**: 15-03-2026  
**Phiên bản**: v1.0.0 - Stable Release

---

<div align="center">

Made with ❤️ for Vietnamese Pharmacies

⭐ If you find this project helpful, please give us a star! ⭐

</div>

# 💊 Pharmacy Management System (GPP Standard)

[![Laravel Version](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.2-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

Hệ thống quản lý nhà thuốc đạt chuẩn **GPP (Good Pharmacy Practice)** theo thông tư **TT02/2018/TT-BYT**. Giải pháp tối ưu giúp số hóa quy trình vận hành, quản lý dược phẩm và báo cáo tài chính cho các nhà thuốc hiện đại.

---

## ✨ Tính năng nổi bật

### 🛒 Quản lý Bán hàng & Kho vận
* **POS System:** Giao diện bán hàng nhanh, hỗ trợ quét mã vạch và in hóa đơn.
* **Chiến lược FEFO:** Tự động gợi ý xuất kho các lô hàng có hạn dùng gần nhất (First Expired, First Out).
* **Kiểm kê kho:** Theo dõi số lô (Batch), hạn sử dụng và cảnh báo hàng sắp hết hạn/hết hàng.
* **Nhập hàng:** Quy trình nhập kho từ nhà cung cấp, quản lý phiếu nhập và giá vốn.

### 👥 Quản lý Nhân sự & Phân quyền
* **Chấm công PIN:** Nhân viên check-in/check-out qua mã cá nhân bảo mật.
* **Phân quyền (RBAC):** Hệ thống phân quyền chặt chẽ với 4 vai trò chính:
    * **Quản lý:** Toàn quyền hệ thống, xem báo cáo doanh thu tổng.
    * **Dược sĩ:** Tư vấn, quản lý đơn thuốc và bán hàng.
    * **Thủ kho:** Quản lý nhập/xuất, kiểm kê số lượng và hạn dùng.
    * **Thu ngân:** Tập trung vào thanh toán và in hóa đơn POS.

### 📊 Báo cáo & Tài chính
* **Doanh thu:** Thống kê theo ngày, tháng, năm hoặc theo ca làm việc.
* **Công nợ:** Theo dõi nợ phải trả nhà cung cấp và nợ phải thu khách hàng.
* **Bảng lương:** Tự động tổng hợp dữ liệu từ hệ thống chấm công.

---

## 🛠 Công nghệ sử dụng (Tech Stack)

* **Backend:** Laravel 11 (PHP 8.2+)
* **Database:** MySQL 8.0 (Hỗ trợ JSON columns cho thông số thuốc)
* **Frontend:** Tailwind CSS, Blade Templates, Vite
* **Realtime:** Laravel Reverb hoặc Pusher (Tùy chọn cho thông báo kho)
* **Package hỗ trợ:** * `spatie/laravel-permission` (Quản lý vai trò)
    * `barryvdh/laravel-dompdf` (Xuất hóa đơn PDF)

---

## 🚀 Hướng dẫn cài đặt

### 1. Yêu cầu hệ thống
Đảm bảo máy tính/server của bạn đã cài đặt:
- PHP >= 8.2
- Composer
- MySQL 8.0
- Node.js & NPM

### 2. Các bước triển khai

```bash
# Clone dự án
git clone [https://github.com/username/pharmacy-system.git](https://github.com/username/pharmacy-system.git)
cd pharmacy-system

# Cài đặt thư viện Backend
composer install

# Cài đặt thư viện Frontend
npm install && npm run build

# Thiết lập cấu hình môi trường
cp .env.example .env
php artisan key:generate

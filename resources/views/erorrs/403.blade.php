<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Không có quyền truy cập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="text-center">
        <div style="font-size:80px;">🔒</div>
        <h1 class="fw-bold text-danger">403</h1>
        <h4 class="text-muted">Không có quyền truy cập</h4>
        <p class="text-muted">{{ $message ?? 'Bạn không có quyền xem trang này.' }}</p>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-2">← Quay lại</a>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">🏠 Dashboard</a>
    </div>
</body>

</html>
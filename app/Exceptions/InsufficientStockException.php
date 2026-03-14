<?php

namespace App\Exceptions;

use Exception;

/**
 * Ném ra khi tồn kho không đủ để xuất hàng.
 * Được bắt trong InvoiceController để trả về lỗi 422 cho POS.
 */
class InsufficientStockException extends Exception
{
    //
}
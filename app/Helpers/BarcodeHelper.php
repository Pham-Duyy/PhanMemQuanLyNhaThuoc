<?php

namespace App\Helpers;

/**
 * Helper class to generate barcodes using SVG
 * Creates EAN-13 barcode as SVG without external dependencies
 */
class BarcodeHelper
{
    /**
     * Generate EAN-13 barcode SVG (Code128-style visual)
     * 
     * @param string $value - 13-digit barcode value
     * @return string SVG HTML
     */
    public static function generateEAN13($value)
    {
        if (strlen($value) !== 13 || !ctype_digit($value)) {
            return '';
        }

        return self::createSvgBarcode($value, 'EAN-13');
    }

    /**
     * Generate Code128 barcode SVG
     * 
     * @param string $value - Barcode value
     * @return string SVG HTML
     */
    public static function generateCode128($value)
    {
        if (empty($value)) {
            return '';
        }

        return self::createSvgBarcode($value, 'Code128');
    }

    /**
     * Generate barcode with auto-detection
     * 
     * @param string $value - Barcode value
     * @return string SVG HTML
     */
    public static function generate($value)
    {
        if (strlen($value) === 13 && ctype_digit($value)) {
            return static::generateEAN13($value);
        }
        
        return static::generateCode128($value);
    }

    /**
     * Create SVG barcode (vertical lines pattern)
     * 
     * @param string $value
     * @param string $type
     * @return string SVG HTML
     */
    private static function createSvgBarcode($value, $type = 'Code128')
    {
        try {
            // Generate barcode pattern (simple: each char = group of bars)
            $pattern = self::encodeBarcode($value);
            
            if (empty($pattern)) {
                return '';
            }

            $barWidth = 2;  // Width of each bar in pixels
            $barHeight = 60; // Height of barcode
            $totalWidth = strlen($pattern) * $barWidth;
            $totalHeight = $barHeight + 15; // Extra for padding
            
            $svg = '<svg width="' . $totalWidth . '" height="' . $totalHeight . '" xmlns="http://www.w3.org/2000/svg" version="1.1" style="border: 1px solid #ccc;">';
            
            // Draw bars
            $x = 0;
            for ($i = 0; $i < strlen($pattern); $i++) {
                if ($pattern[$i] === '1') {
                    $svg .= '<rect x="' . $x . '" y="0" width="' . $barWidth . '" height="' . $barHeight . '" fill="black"/>';
                }
                $x += $barWidth;
            }
            
            $svg .= '</svg>';
            
            return $svg;
        } catch (\Exception $e) {
            \Log::warning('Barcode generation failed: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Encode barcode into binary pattern (1 = black bar, 0 = white space)
     * Simplified binary pattern for visual representation
     * 
     * @param string $value
     * @return string Binary pattern (1s and 0s)
     */
    private static function encodeBarcode($value)
    {
        // EAN-13 START pattern: 101
        $pattern = '101';
        
        // Convert each digit to 6-bit pattern (simplified)
        $table = [
            '0' => '001101', '1' => '011001', '2' => '011100', '3' => '010011',
            '4' => '100011', '5' => '110001', '6' => '110100', '7' => '110010',
            '8' => '110110', '9' => '100110',
            'A' => '101011', 'B' => '101110', 'C' => '111010', 'D' => '100101',
            'E' => '111001', 'F' => '101100', 'G' => '111101', 'H' => '111100',
            'I' => '110101', 'J' => '101101', 'K' => '101001', 'L' => '101010',
        ];
        
        // Encode each character
        foreach (str_split($value) as $char) {
            $key = is_numeric($char) ? $char : strtoupper($char);
            if (isset($table[$key])) {
                $pattern .= $table[$key] . '0'; // Add separator
            }
        }
        
        // END pattern: 101
        $pattern .= '101';
        
        return $pattern;
    }
}

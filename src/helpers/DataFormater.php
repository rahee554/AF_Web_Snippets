
<?php

if (!function_exists('formatContactPK')) {

    function formatContactPK($number)
    {
        // Remove spaces, dashes, parentheses, etc.
        $number = preg_replace('/[^\d+]/', '', $number);

        // Remove leading 00 or + and convert to international
        if (strpos($number, '00') === 0) {
            $number = '+' . substr($number, 2);
        }

        // If starts with 0, replace with +92
        if (strpos($number, '0') === 0) {
            $number = '+92' . substr($number, 1);
        }

        // If starts with 92 (no +), add +
        if (strpos($number, '92') === 0 && strpos($number, '+92') !== 0) {
            $number = '+' . $number;
        }

        // If starts with 3, assume missing +92
        if (strpos($number, '3') === 0 && strlen($number) >= 10) {
            $number = '+92' . $number;
        }

        // Final cleaning: ensure it is +923XXXXXXXXX format (13 digits)
        if (preg_match('/^\+923\d{9}$/', $number)) {
            return $number;
        }

        return null; // invalid or unrecognized format
    }
}

if (!function_exists('formatCnicPK')) {
    function formatCnicPK($cnic)
    {
        if (preg_match('/[a-zA-Z]/', $cnic)) {
            return strtoupper($cnic); // Keep as-is, just uppercase
        }

        $cnic = preg_replace('/\D/', '', $cnic); // Remove non-digit characters

        return strlen($cnic) === 13
            ? substr($cnic, 0, 5) . '-' . substr($cnic, 5, 7) . '-' . substr($cnic, 12, 1)
            : null; // Invalid CNIC
    }
}

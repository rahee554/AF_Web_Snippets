
<?php

if (!function_exists('formatContactPK')) {

    function formatContactPK($number)
    {
        // Remove all non-digit and non-plus characters
        $number = preg_replace('/[^\d+]/', '', $number);
    
        // Convert 00 prefix to +
        if (strpos($number, '00') === 0) {
            $number = '+' . substr($number, 2);
        }
    
        // Pakistani number handling
        if (strpos($number, '0') === 0) {
            $number = '+92' . substr($number, 1);
        } elseif (strpos($number, '92') === 0 && strpos($number, '+92') !== 0) {
            $number = '+' . $number;
        } elseif (strpos($number, '3') === 0 && strlen($number) >= 10) {
            $number = '+92' . $number;
        }
    
        // If it's a valid Pakistani mobile number, return formatted
        if (preg_match('/^\+923\d{9}$/', $number)) {
            return $number;
        }
    
        // For non-PK numbers like +1, +44, etc., return as-is
        return $number;
    }
    
}

if (!function_exists('formatCnicPK')) {
    function formatCnicPK($cnic)
{
    if (preg_match('/[a-zA-Z]/', $cnic)) {
        return strtoupper($cnic);
    }

    $cnic = preg_replace('/\D/', '', $cnic);

    return strlen($cnic) === 13
        ? substr($cnic, 0, 5) . '-' . substr($cnic, 5, 7) . '-' . substr($cnic, 12, 1)
        : $cnic; // Return as-is if not 13 digits
}

}

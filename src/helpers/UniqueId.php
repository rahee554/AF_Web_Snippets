
<?php 

if (!function_exists('unique6digitID')) {
    function unique6digitID()
    {
        $num = rand(100, 999999);
        $num_str = (string)$num;
        $len = strlen($num_str);

        if ($len < 6) {
            $zeros_needed = 6 - $len;
            if ($zeros_needed > 0) {
                $middle = (int)($len / 2);
                $num_str = substr($num_str, 0, $middle) . str_repeat('0', $zeros_needed) . substr($num_str, $middle);
            }
        }
        
        return $num_str;
    }
}

if (!function_exists('generateUniqueBase36ID')) {
    function generateUniqueBase36ID()
    { 
        $year = date('y');
        $day = date('z');
        $hour = date('G');
        $minute = date('i');
        $second = date('s');
        $type = date('a') === 'am' ? 1 : 2;
        $unique = $year . $day . $hour . $minute . $second . $type;
        $base36 = base_convert($unique, 10, 36);
        //Get Last 4 Digits
        $base36value = substr($base36, -4);
        //Add Random 2 digit in the start
        $unique_id = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT) . $base36value;
        return strtoupper($unique_id); // Return uppercase base36 value
    }
}
if (!function_exists('generateUniqueID')) {
    function generateUniqueID($model, $column)
    {
        $unique_id = unique6digitID();
        $attempts = 0;
        while ($attempts < 5) {
            $result = $model::where($column, $unique_id)->first();
            if (empty($result)) {
                return $unique_id;
            } else {
                $unique_id = unique6digitID();
                $attempts++;
            }
        }
        // If all attempts fail, generate unique ID using generateUniqueBase36ID
        $unique_id = generateUniqueBase36ID();
        while ($model::where($column, $unique_id)->exists()) {
            $unique_id = generateUniqueBase36ID();
        }
        return $unique_id;
    }
}

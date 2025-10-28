<?php

function trans($key, $replace = []) {
    global $lang;
    $text = $lang[$key] ?? $key;
    foreach ($replace as $k => $v) {
        $text = str_replace($k, $v, $text);
    }
    return $text;
}

// format ngày theo session ngôn ngữ
function formatDateByLocale($datetime) {
    $created_at = new DateTime($datetime);
    $lang = $_SESSION['lang'];

    if ($lang === 'vi') {
        $months = [
            'Jan'=>'Tháng 1','Feb'=>'Tháng 2','Mar'=>'Tháng 3','Apr'=>'Tháng 4',
            'May'=>'Tháng 5','Jun'=>'Tháng 6','Jul'=>'Tháng 7','Aug'=>'Tháng 8',
            'Sep'=>'Tháng 9','Oct'=>'Tháng 10','Nov'=>'Tháng 11','Dec'=>'Tháng 12',
        ];
        $month = $created_at->format('M');
        return $created_at->format('d ') . $months[$month] . $created_at->format(' Y, H:i');
    } else {
        return $created_at->format('M d, Y H:i');
    }
}

<?php

$api_key = "246d88731d741558c8437eaffc7584f6598c8d2c";
$country = "FR";
$year = "2023";
$url = "https://calendarific.com/api/v2/holidays?&api_key=$api_key&country=$country&year=$year";

try {
    $holidays = json_decode(file_get_contents($url), false, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    echo $e->getMessage();
}

// transform holidays into an array of DateTime
$holidaysDates = [];
$wanted = [
    'New Year\'s Day',
    'Easter Monday',
    'Labor Day / May Day',
    'WWII Victory Day',
    'Ascension Day',
    'Whit Monday',
    'Bastille Day',
    'Assumption of Mary',
    'All Saints\' Day',
    'Armistice Day',
    'Christmas Day',
];
foreach ($holidays->response->holidays as $holiday) {
    try {
        if (in_array($holiday->name, $wanted, true)) {
            $tempDate = new DateTime($holiday->date->iso);
            $holidaysDates[$holiday->name] = $tempDate->format('Y-m-d');
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

return $holidaysDates;

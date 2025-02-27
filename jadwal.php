<?php
    header("Content-Type: application/json");
    date_default_timezone_set('Asia/Jakarta');
    
    // API Aladhan untuk kota Jakarta
    $url = "http://api.aladhan.com/v1/timingsByCity?city=Jakarta&country=Indonesia&method=2";
    
    // Ambil data dari API
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    // Ambil waktu shalat yang diperlukan
    $waktu = [
        "imsak" => $data['data']['timings']['Imsak'],
        "subuh" => $data['data']['timings']['Fajr'],
        "dzuhur" => $data['data']['timings']['Dhuhr'],
        "ashar" => $data['data']['timings']['Asr'],
        "maghrib" => $data['data']['timings']['Maghrib'],
        "isya" => $data['data']['timings']['Isha']
    ];
    
    // Ubah menjadi JSON
    header('Content-Type: application/json');
    echo json_encode($waktu);
    
?>

<?php
function getHijriDate() {
    $today = date("d-m-Y"); // format dd-mm-yyyy
    $url = "https://api.aladhan.com/v1/gToH?date=" . $today;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Nonaktifkan verifikasi SSL jika perlu

    $json = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }
    curl_close($ch);

    $data = json_decode($json, true);
    if (!$data || !isset($data['data']['hijri'])) {
        return null;
    }
    return $data['data']['hijri'];
}

$hijriData = getHijriDate();
$statusRamadan = "";
$isIdulFitri = false;

if ($hijriData) {
    $hijriDay = intval($hijriData['day']);
    $hijriMonth = intval($hijriData['month']['number']);
    $hijriYear = $hijriData['year'];

    if ($hijriMonth == 9) {
        // Bulan 9 adalah Ramadan
        $statusRamadan = "Ramadan ke-" . $hijriDay . " tahun " . $hijriYear;
    } elseif ($hijriMonth == 10 && $hijriDay == 1) {
        // Bulan Syawal hari pertama = Idul Fitri
        $statusRamadan = "Selamat Idul Fitri! Mohon maaf lahir dan batin.";
        $isIdulFitri = true;
    } else {
        $statusRamadan = "Hari ini: " . $hijriData['day'] . " " . $hijriData['month']['en'] . " " . $hijriYear;
    }
} else {
    $statusRamadan = "Gagal mengambil data Hijriyah.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Berbuka, Imsak & Status Ramadan</title>
    <!-- Menggunakan font 'Scheherazade New' untuk nuansa Islami -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Scheherazade+New&display=swap">
    <style>
        body {
        font-family: 'Scheherazade New', serif;
        text-align: center;
        margin: 20px;
        background-image: url('src/latar.png');
        background-size: cover;
        background-attachment: fixed;
        background-repeat: no-repeat;
        color: #fff;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
        }
        h2 {
        font-size: 36px;
        margin-bottom: 20px;
        text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.8);
        }
        #clock {
        font-size: 32px;
        font-weight: bold;
        margin-bottom: 20px;
        }
        .jadwal {
        font-size: 20px;
        margin: 10px auto;
        background-color: rgba(0, 0, 0, 0.5);
        display: inline-block;
        padding: 12px 24px;
        border-radius: 12px;
        transition: background-color 0.3s, transform 0.3s;
        cursor: default;
        }
        .jadwal:hover {
        background-color: rgba(0, 0, 0, 0.7);
        transform: scale(1.05);
        }
        .header-img {
        width: 120px;
        transition: transform 0.3s;
        margin-bottom: 10px;
        }
        .header-img:hover {
        transform: rotate(5deg) scale(1.1);
        }
    </style>
</head>
<body>
    <img class="header-img" src="https://img.icons8.com/fluency/96/ffffff/mosque.png" alt="Ikon Masjid">
    <h2>Panduan Jadwal Shalat Sesuai Kalender Hijriah</h2>
    <p id="clock">Memuat jam...</p>
    <div class="jadwal">🌅 Imsak: <span id="imsak">-</span></div>
    <div class="jadwal">🌅 Subuh: <span id="subuh">-</span></div>
    <div class="jadwal">🌞 Dzuhur: <span id="dzuhur">-</span></div>
    <div class="jadwal">🌤️ Ashar: <span id="ashar">-</span></div>
    <div class="jadwal">🌇 Maghrib: <span id="maghrib">-</span></div>
    <div class="jadwal">🌃 Isya: <span id="isya">-</span></div>
    <div class="jadwal" id="ramadan-status"><?php echo $statusRamadan; ?></div>

    <!-- Footer -->
    <footer>
        <h3>&copy; 2025 Satria Farel Cipta Permata. All rights reserved.</h3>
        <h3><a href="https://linktree-sf.vercel.app">Kunjungi Linktree saya</a></h2>
    </footer>

  <script>
    // Registrasi Service Worker dan Push Subscription
    if ('serviceWorker' in navigator && 'PushManager' in window) {
      navigator.serviceWorker.register('service.js')
      .then(function(registration) {
        // console.log('Service Worker terdaftar:', registration);
        initialisePush(registration);
      })
      .catch(function(error) {
        // console.error('Registrasi Service Worker gagal:', error);
      });
    }

    function initialisePush(registration) {
      registration.pushManager.getSubscription()
      .then(function(subscription) {
        if (subscription) {
          return subscription;
        }
        // Ganti dengan VAPID public key Anda (dalam format URL-safe Base64)
        const vapidPublicKey = 'BFu1SBHdqgMrmmk-HbAb6BTALVh2HMG8LYWs3FYDcHolS20KlHV8vfxpMYvGw5M9T5Ac_DLm4YWGsNYNY8kfFS4';
        const convertedVapidKey = urlBase64ToUint8Array(vapidPublicKey);
        return registration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: convertedVapidKey
        });
      })
      .then(function(subscription) {
        // console.log('Berhasil subscribe untuk push notifications:', JSON.stringify(subscription));
        // Kirim data subscription ke backend Anda agar dapat digunakan untuk mengirim push notification
      })
      .catch(function(error) {
        // console.error('Push subscription gagal:', error);
      });
    }

    // Fungsi konversi VAPID key
    function urlBase64ToUint8Array(base64String) {
      const padding = '='.repeat((4 - base64String.length % 4) % 4);
      const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');
      const rawData = window.atob(base64);
      const outputArray = new Uint8Array(rawData.length);
      for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
      }
      return outputArray;
    }

    // Notifikasi lokal (jika halaman aktif) menggunakan Service Worker
    function showNotification(title, message) {
      if (Notification.permission === "granted") {
        navigator.serviceWorker.getRegistration().then(function(registration) {
          if (registration) {
            registration.showNotification(title, {
              body: message,
              icon: "https://img.icons8.com/fluency/96/ffffff/mosque.png",
              data: { url: window.location.href }
            });
          }
        });
      }
      // Fallback: menampilkan alert
      alert(title + "\n" + message);
    }

    // Ambil jadwal shalat dari server (pastikan file jadwal.php menyediakan data JSON)
    async function getPrayerTimes() {
      try {
        const response = await fetch("jadwal.php");
        const data = await response.json();
        document.getElementById("imsak").textContent = data.imsak;
        document.getElementById("subuh").textContent = data.subuh;
        document.getElementById("dzuhur").textContent = data.dzuhur;
        document.getElementById("ashar").textContent = data.ashar;
        document.getElementById("maghrib").textContent = data.maghrib;
        document.getElementById("isya").textContent = data.isya;
        // Cek setiap detik untuk notifikasi berbuka dan imsak
        setInterval(() => checkPrayerTime(data.subuh, data.maghrib), 1000);
      } catch (error) {
        console.error("Gagal mengambil jadwal shalat:", error);
      }
    }

    function updateClock() {
      const now = new Date();
      const hours = now.getHours().toString().padStart(2, '0');
      const minutes = now.getMinutes().toString().padStart(2, '0');
      const seconds = now.getSeconds().toString().padStart(2, '0');
      document.getElementById("clock").textContent = `🕒 ${hours}:${minutes}:${seconds}`;
    }

    // Fungsi helper untuk mengubah waktu "HH:mm" ke total menit sejak tengah malam
    function timeToMinutes(timeStr) {
    const parts = timeStr.split(":");
    return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
    }

    function checkPrayerTime(imsak, subuh, maghrib) {
    const now = new Date();
    const currentTime = now.getHours().toString().padStart(2, '0') + ":" +
                        now.getMinutes().toString().padStart(2, '0');
    const currentMinutes = now.getHours() * 60 + now.getMinutes();

    // Notifikasi imsak dengan radius 10 menit
    const imsakMinutes = timeToMinutes(imsak);
    if (Math.abs(currentMinutes - imsakMinutes) <= 10) {
        showNotification("Waktu Imsak", "Sahur telah berakhir. Waktu imsak telah tiba.");
    }

    // Notifikasi Subuh: dipicu tepat pada waktunya (tanpa radius)
    if (currentTime === subuh) {
        showNotification("Waktunya Sholat Subuh", "Saatnya melaksanakan sholat subuh.");
    }

    // Notifikasi Maghrib: dipicu tepat pada waktunya (tanpa radius)
    if ( && currentTime === maghrib) {
        showNotification("Selamat Berbuka!", "Saatnya berbuka puasa 🍽️");
    }
    }


    // Jalankan fungsi-fungsi
    getPrayerTimes();
    updateClock();
    setInterval(updateClock, 1000);

    // Cek apakah hari ini adalah Idul Fitri (dari PHP)
    var isIdulFitri = <?php echo $isIdulFitri ? 'true' : 'false'; ?>;
    if (isIdulFitri) {
      showNotification("Selamat Idul Fitri!", "Mohon maaf lahir dan batin.");
    }
  </script>
</body>
</html>

// Tangani event push dan tampilkan notifikasi
self.addEventListener('push', function(event) {
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'Notifikasi';
    const options = {
      body: data.body || 'Anda mendapatkan notifikasi dari server.',
      icon: data.icon || 'https://img.icons8.com/fluency/96/ffffff/mosque.png',
      data: data.url || '/'
    };
    event.waitUntil(self.registration.showNotification(title, options));
  });
  
  // Tangani klik pada notifikasi untuk membuka halaman terkait
  self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    event.waitUntil(
      clients.matchAll({ type: "window" }).then(function(clientList) {
        for (let client of clientList) {
          if (client.url === event.notification.data && 'focus' in client) {
            return client.focus();
          }
        }
        if (clients.openWindow) {
          return clients.openWindow(event.notification.data);
        }
      })
    );
  });

  //   public key
// BFu1SBHdqgMrmmk-HbAb6BTALVh2HMG8LYWs3FYDcHolS20KlHV8vfxpMYvGw5M9T5Ac_DLm4YWGsNYNY8kfFS4
//   private key
// w4q9_tHyUDm9unLSX9QLU-zkn1wwtKlabxElJt8zvrY
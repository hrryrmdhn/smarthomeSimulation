<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Home Control</title>
    <!-- Load framework CSS dan JavaScript -->
    <script src="https://cdn.tailwindcss.com"></script> <!-- Framework untuk styling -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> <!-- Icons -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery untuk AJAX -->
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">🏠 Simulasi Smarthome</h1>
        
        <!-- Notification: Popup notifikasi di pojok kanan atas -->
        <!-- Awalnya hidden, akan muncul saat ada update status -->
        <div id="notification" class="hidden fixed top-4 right-4 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <span id="notification-message"></span>
        </div>

        <!-- Grid layout untuk menampilkan 6 devices -->
        <!-- Responsif: 1 kolom di mobile, 2 kolom di tablet, 3 kolom di desktop -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Loop melalui setiap device dari database -->
            <!-- $devices dikirim dari Laravel Controller -->
            @foreach($devices as $device)
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <!-- Device Icon & Status: Gambar lampu di tengah -->
                <div class="mb-4">
                    <!-- Icon lampu yang berubah warna berdasarkan status -->
                    <!-- Jika status true (ON): background hijau, jika false (OFF): background abu-abu -->
                    <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center 
                        {{ $device->status ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' }}">
                        <i class="fas fa-lightbulb text-2xl"></i>
                    </div>
                </div>

                <!-- Device Name: Nama device dari database -->
                <!-- $device->label mengambil data dari kolom 'label' di tabel devices -->
                <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $device->label }}</h3>
                
                <!-- Status Badge: Indicator ON/OFF di atas tombol -->
                <div class="mb-4">
                    <!-- Badge yang berubah warna berdasarkan status -->
                    <!-- ON: background hijau, OFF: background merah -->
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $device->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        <!-- Dot indicator kecil di kiri teks -->
                        <span class="w-2 h-2 rounded-full mr-2 
                            {{ $device->status ? 'bg-green-500' : 'bg-red-500' }}"></span>
                        <!-- Teks ON atau OFF -->
                        {{ $device->status ? 'ON' : 'OFF' }}
                    </span>
                </div>

                <!-- Toggle Button: Tombol untuk mengubah status device -->
                <!-- 
                    onclick: Panggil function toggleDevice() dengan parameter:
                    - deviceId: ID device (1-6)
                    - newStatus: Status baru (kebalikan dari status sekarang)
                    
                    Warna tombol:
                    - Jika ON: Tombol merah "MATIKAN"
                    - Jika OFF: Tombol hijau "NYALAKAN"
                -->
                <button onclick="toggleDevice({{ $device->id }}, {{ $device->status ? 'false' : 'true' }})" 
                        class="w-full py-2 px-4 rounded-lg font-medium transition-colors
                            {{ $device->status ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }} 
                            text-white"
                        id="btn-{{ $device->id }}">
                    {{ $device->status ? 'MATIKAN' : 'NYALAKAN' }}
                </button>

                <!-- Last Update: Waktu terakhir device diupdate -->
                <!-- $device->updated_at mengambil dari kolom updated_at di database -->
                <!-- format('H:i:s') menampilkan jam:menit:detik -->
                <div class="mt-3 text-xs text-gray-500">
                    Update: {{ $device->updated_at->format('H:i:s') }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <script>
        // CSRF Token untuk keamanan, di-generate oleh Laravel
        // Mencegah serangan CSRF (Cross-Site Request Forgery)
        const csrfToken = '{{ csrf_token() }}';

        /**
         * FUNCTION TOGGLE DEVICE - Fungsi utama untuk mengubah status device
         * Cara kerja:
         * 1. Tampilkan loading state di tombol
         * 2. Kirim request AJAX ke server
         * 3. Handle response sukses/error
         * 4. Update tampilan
         */
        function toggleDevice(deviceId, newStatus) {
            console.log('🚀 TOGGLE DEVICE:', {
                deviceId: deviceId,
                newStatus: newStatus,
                type: typeof newStatus
            });

            // Dapatkan element tombol yang diklik
            const button = document.getElementById(`btn-${deviceId}`);
            const originalText = button.textContent;
            
            // Tampilkan loading state: ganti teks dengan spinner
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            button.disabled = true; // Nonaktifkan tombol sementara
            
            // Konversi status ke integer (1 atau 0) untuk dikirim ke server
            // Lebih reliable karena server mudah memproses integer
            const statusToSend = newStatus ? 1 : 0;
            console.log('📤 Sending status as:', statusToSend);
            
            // Kirim request AJAX ke server Laravel
            $.ajax({
                url: `/api/devices/${deviceId}`, // URL API endpoint
                method: 'POST', // Method POST (dengan _method=PUT untuk REST)
                data: {
                    status: statusToSend, // Status baru
                    _token: csrfToken,    // Token keamanan
                    _method: 'PUT'        // Method override untuk REST API
                },
                dataType: 'json', // Expect response dalam format JSON
                success: function(response) {
                    console.log('✅ SUCCESS Response:', response);
                    
                    // Jika update berhasil
                    if (response.success) {
                        // Tampilkan notifikasi sukses
                        showNotification(
                            `${response.device.label} ${newStatus ? 'dinyalakan' : 'dimatikan'}!`, 
                            'success'
                        );
                        // Update tampilan device
                        updateDeviceUI(deviceId, newStatus, response.device);
                    } else {
                        // Jika server return success=false
                        showNotification('Gagal update! ' + (response.message || ''), 'error');
                        resetButton(deviceId, !newStatus); // Reset tombol ke state sebelumnya
                    }
                },
                error: function(xhr, status, error) {
                    // Jika terjadi error HTTP (404, 500, dll)
                    console.error('❌ AJAX Error:', {
                        status: xhr.status,
                        response: xhr.responseText,
                        error: error
                    });
                    showNotification('Error! Lihat console untuk detail.', 'error');
                    resetButton(deviceId, !newStatus); // Reset tombol ke state sebelumnya
                }
            });
        }

        /**
         * UPDATE DEVICE UI - Update tampilan device setelah status berubah
         * Mengubah: badge status, icon, tombol, dan timestamp
         */
        function updateDeviceUI(deviceId, newStatus, deviceData) {
            console.log('🎨 Updating UI for device:', deviceId, 'to:', newStatus);
            
            // Dapatkan semua element yang perlu diupdate
            const deviceCard = document.querySelector(`#btn-${deviceId}`).closest('.bg-white');
            const statusBadge = deviceCard.querySelector('.inline-flex');
            const icon = deviceCard.querySelector('.fa-lightbulb').parentElement;
            const timestamp = deviceCard.querySelector('.text-xs');
            const button = document.getElementById(`btn-${deviceId}`);
            
            // UPDATE STATUS BADGE (ON/OFF indicator)
            statusBadge.className = `inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                ${newStatus ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
            statusBadge.innerHTML = `
                <span class="w-2 h-2 rounded-full mr-2 ${newStatus ? 'bg-green-500' : 'bg-red-500'}"></span>
                ${newStatus ? 'ON' : 'OFF'}
            `;
            
            // UPDATE ICON (Lampu)
            icon.className = `w-16 h-16 mx-auto rounded-full flex items-center justify-center 
                ${newStatus ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'}`;
            
            // UPDATE TOMBOL
            button.className = `w-full py-2 px-4 rounded-lg font-medium transition-colors
                ${newStatus ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600'} 
                text-white`;
            button.textContent = newStatus ? 'MATIKAN' : 'NYALAKAN';
            button.disabled = false; // Aktifkan kembali tombol
            // Update onclick function untuk status yang baru
            button.setAttribute('onclick', `toggleDevice(${deviceId}, ${!newStatus})`);
            
            // UPDATE TIMESTAMP (Waktu update)
            timestamp.textContent = `Update: ${new Date().toLocaleTimeString()}`;
            
            console.log('✅ UI Updated successfully');
        }

        /**
         * RESET BUTTON - Reset tombol ke state semula jika terjadi error
         * Digunakan ketika AJAX request gagal
         */
        function resetButton(deviceId, oldStatus) {
            const button = document.getElementById(`btn-${deviceId}`);
            button.className = `w-full py-2 px-4 rounded-lg font-medium transition-colors
                ${oldStatus ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600'} 
                text-white`;
            button.textContent = oldStatus ? 'MATIKAN' : 'NYALAKAN';
            button.disabled = false;
            button.setAttribute('onclick', `toggleDevice(${deviceId}, ${!oldStatus})`);
            console.log('🔄 Button reset to:', oldStatus ? 'MATIKAN' : 'NYALAKAN');
        }

        /**
         * SHOW NOTIFICATION - Tampilkan notifikasi sementara di pojok kanan atas
         */
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const messageEl = document.getElementById('notification-message');
            
            // Set warna notifikasi berdasarkan type
            notification.className = `fixed top-4 right-4 text-white px-6 py-3 rounded-lg shadow-lg z-50 
                ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            messageEl.textContent = message;
            
            // Tampilkan notifikasi
            notification.classList.remove('hidden');
            
            // Sembunyikan otomatis setelah 3 detik
            setTimeout(() => {
                notification.classList.add('hidden');
            }, 3000);
        }

        // Debug info saat page load - tampilkan data devices di console
        $(document).ready(function() {
            console.log('🔧 Page loaded with devices:', [
                @foreach($devices as $device)
                {
                    id: {{ $device->id }},
                    label: "{{ $device->label }}", 
                    status: {{ $device->status ? 'true' : 'false' }},
                    buttonText: "{{ $device->status ? 'MATIKAN' : 'NYALAKAN' }}"
                },
                @endforeach
            ]);
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Home Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .bg-orange-500 { background-color: #f97316; }
        .bg-orange-100 { background-color: #ffedd5; }
        .text-orange-800 { color: #9a3412; }
        .bg-blue-100 { background-color: #dbeafe; }
        .text-blue-800 { color: #1e40af; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-2">🏠 Smart Home Control</h1>
        <p class="text-center text-gray-600 mb-8">Kontrol 6 Perangkat Smart Home + Mode Auto/Manual</p>
        
        <!-- Notification -->
        <div id="notification" class="hidden fixed top-4 right-4 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <i class="fas fa-info-circle mr-2"></i>
            <span id="notification-message"></span>
        </div>

        <!-- System Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-blue-800">Sistem Info</h3>
                    <p class="text-sm text-blue-600">Device 1-3: Manual | Device 4-6: Auto Mode dengan DHT22</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-blue-600" id="current-time">Loading...</div>
                    <div class="text-xs text-blue-500">Auto refresh: 10s</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($devices as $device)
            <div class="bg-white rounded-xl shadow-lg border-2 
                {{ $device->mode === 'auto' ? 'border-blue-200' : 'border-gray-200' }} 
                transition-all duration-300 hover:shadow-xl"
                id="device-card-{{ $device->id }}">
                
                <!-- Device Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">{{ $device->label }}</h3>
                    <div class="flex items-center space-x-2">
                        <!-- Status Dot -->
                        <div class="w-3 h-3 rounded-full {{ $device->status ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}"
                             id="status-dot-{{ $device->id }}"></div>
                        <!-- Mode Badge -->
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold
                            {{ $device->mode === 'auto' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}"
                            id="mode-badge-{{ $device->id }}">
                            <i class="fas {{ $device->mode === 'auto' ? 'fa-robot' : 'fa-hand-paper' }} mr-1"></i>
                            {{ $device->mode === 'auto' ? 'AUTO' : 'MANUAL' }}
                        </span>
                    </div>
                </div>

                <!-- Device Icon -->
                <div class="mb-4">
                    <div class="w-20 h-20 mx-auto rounded-full flex items-center justify-center 
                        {{ $device->status ? 'bg-green-100 text-green-600 shadow-inner' : 'bg-gray-100 text-gray-400' }} 
                        transition-all duration-300"
                        id="icon-{{ $device->id }}">
                        <i class="fas fa-lightbulb text-3xl"></i>
                    </div>
                </div>

                <!-- Status Display -->
                <div class="mb-4">
                    <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-semibold
                        {{ $device->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}"
                        id="status-badge-{{ $device->id }}">
                        <span class="w-2 h-2 rounded-full mr-2 
                            {{ $device->status ? 'bg-green-500' : 'bg-red-500' }}"></span>
                        {{ $device->status ? 'ON' : 'OFF' }}
                    </span>
                </div>

                <!-- Threshold Info untuk Auto Mode -->
                @if($device->mode === 'auto' && $device->auto_threshold)
                <div class="mb-3 p-2 bg-blue-50 rounded-lg">
                    <div class="text-xs text-blue-700 font-medium">
                        <i class="fas fa-thermometer-half mr-1"></i>
                        AUTO THRESHOLD: <span id="threshold-value-{{ $device->id }}">{{ $device->auto_threshold }}</span>°C
                    </div>
                    <div class="text-xs text-blue-500 mt-1">
                        Menyala otomatis saat suhu ≥ {{ $device->auto_threshold }}°C
                    </div>
                </div>
                @endif

                <!-- Control Buttons -->
                <div class="space-y-2">
                    <!-- Toggle Button untuk Manual Mode -->
                    @if($device->mode === 'manual')
                    <button onclick="toggleDevice({{ $device->id }}, {{ $device->status ? 'false' : 'true' }})" 
                            class="w-full py-3 px-4 rounded-lg font-bold transition-all duration-200
                                {{ $device->status ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }} 
                                text-white shadow-md hover:shadow-lg"
                            id="btn-{{ $device->id }}">
                        <i class="fas fa-power-off mr-2"></i>
                        {{ $device->status ? 'MATIKAN' : 'NYALAKAN' }}
                    </button>
                    @else
                    <!-- Auto Mode Display -->
                    <div class="w-full py-3 px-4 rounded-lg font-bold bg-blue-500 text-white opacity-90 cursor-not-allowed">
                        <i class="fas fa-robot mr-2"></i>AUTO MODE
                    </div>
                    @endif

                    <!-- Mode Switch Buttons -->
                    <div class="flex space-x-2">
                        <button onclick="switchMode({{ $device->id }}, 'manual')" 
                                class="flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors
                                    {{ $device->mode === 'manual' ? 'bg-gray-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}"
                                {{ $device->mode === 'manual' ? 'disabled' : '' }}>
                            <i class="fas fa-hand-paper mr-1"></i>Manual
                        </button>
                        <button onclick="switchMode({{ $device->id }}, 'auto')" 
                                class="flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors
                                    {{ $device->mode === 'auto' ? 'bg-blue-600 text-white' : 'bg-blue-200 text-blue-700 hover:bg-blue-300' }}"
                                {{ $device->mode === 'auto' ? 'disabled' : '' }}>
                            <i class="fas fa-robot mr-1"></i>Auto
                        </button>
                    </div>

                    <!-- Threshold Control untuk Auto Mode -->
                    @if($device->mode === 'auto')
                    <div class="flex items-center space-x-2">
                        <span class="text-xs text-gray-600 flex-1">Suhu:</span>
                        <input type="number" 
                               value="{{ $device->auto_threshold }}" 
                               step="0.5"
                               min="20"
                               max="40"
                               class="w-16 py-1 px-2 text-xs border rounded focus:outline-none focus:border-blue-500"
                               id="threshold-input-{{ $device->id }}">
                        <span class="text-xs text-gray-600">°C</span>
                        <button onclick="updateThreshold({{ $device->id }})" 
                                class="py-1 px-2 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition-colors">
                            <i class="fas fa-save"></i>
                        </button>
                    </div>
                    @endif
                </div>

                <!-- Last Update -->
                <div class="mt-3 text-xs text-gray-500 text-center" id="timestamp-{{ $device->id }}">
                    <i class="far fa-clock mr-1"></i>
                    Update: {{ $device->updated_at->format('H:i:s') }}
                </div>
            </div>
            @endforeach
        </div>

        <!-- System Status Footer -->
        <div class="mt-8 text-center">
            <div class="inline-flex items-center bg-green-50 text-green-700 px-4 py-2 rounded-full border border-green-200">
                <div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>
                <span class="text-sm font-medium">Sistem Online - Smart Home Ready</span>
            </div>
        </div>
    </div>

    <script>
        // CSRF Token untuk keamanan
        const csrfToken = '{{ csrf_token() }}';

        /**
         * FUNCTION TOGGLE DEVICE - Untuk kontrol manual
         */
        function toggleDevice(deviceId, newStatus) {
            console.log('🚀 TOGGLE DEVICE:', { deviceId, newStatus });

            const button = document.getElementById(`btn-${deviceId}`);
            
            // Cek jika device dalam mode auto
            if (button.disabled) {
                showNotification('Device dalam mode auto, tidak bisa dikontrol manual', 'warning');
                return;
            }

            const originalText = button.textContent;
            
            // Loading state
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
            button.disabled = true;
            
            const statusToSend = newStatus ? 1 : 0;
            
            $.ajax({
                url: `/api/devices/${deviceId}`,
                method: 'POST',
                data: {
                    status: statusToSend,
                    _token: csrfToken,
                    _method: 'PUT'
                },
                dataType: 'json',
                success: function(response) {
                    console.log('✅ SUCCESS Response:', response);
                    
                    if (response.success) {
                        showNotification(
                            `${response.device.label} ${newStatus ? 'dinyalakan' : 'dimatikan'}!`, 
                            'success'
                        );
                        updateDeviceUI(deviceId, newStatus, response.device);
                    } else {
                        if (response.message && response.message.includes('mode auto')) {
                            showNotification(response.message, 'warning');
                        } else {
                            showNotification('Gagal update! ' + (response.message || ''), 'error');
                        }
                        resetButton(deviceId, !newStatus);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ AJAX Error:', error);
                    showNotification('Error! Lihat console untuk detail.', 'error');
                    resetButton(deviceId, !newStatus);
                }
            });
        }

        /**
         * UPDATE DEVICE UI - Update tampilan setelah perubahan
         */
        function updateDeviceUI(deviceId, newStatus, deviceData) {
            console.log('🎨 Updating UI for device:', deviceId, 'to:', newStatus);
            
            // Update status dot
            const statusDot = document.getElementById(`status-dot-${deviceId}`);
            statusDot.className = `w-3 h-3 rounded-full ${newStatus ? 'bg-green-500 animate-pulse' : 'bg-red-500'}`;
            
            // Update status badge
            const statusBadge = document.getElementById(`status-badge-${deviceId}`);
            statusBadge.className = `inline-flex items-center px-3 py-2 rounded-full text-sm font-semibold ${newStatus ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
            statusBadge.innerHTML = `
                <span class="w-2 h-2 rounded-full mr-2 ${newStatus ? 'bg-green-500' : 'bg-red-500'}"></span>
                ${newStatus ? 'ON' : 'OFF'}
            `;
            
            // Update icon
            const icon = document.getElementById(`icon-${deviceId}`);
            icon.className = `w-20 h-20 mx-auto rounded-full flex items-center justify-center ${newStatus ? 'bg-green-100 text-green-600 shadow-inner' : 'bg-gray-100 text-gray-400'} transition-all duration-300`;
            
            // Update button (hanya untuk manual mode)
            if (deviceData.mode === 'manual') {
                const button = document.getElementById(`btn-${deviceId}`);
                button.className = `w-full py-3 px-4 rounded-lg font-bold transition-all duration-200 ${newStatus ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600'} text-white shadow-md hover:shadow-lg`;
                button.textContent = newStatus ? 'MATIKAN' : 'NYALAKAN';
                button.disabled = false;
                button.setAttribute('onclick', `toggleDevice(${deviceId}, ${!newStatus})`);
            }
            
            // Update timestamp
            const timestamp = document.getElementById(`timestamp-${deviceId}`);
            timestamp.innerHTML = `<i class="far fa-clock mr-1"></i>Update: ${new Date().toLocaleTimeString()}`;
            
            console.log('✅ UI Updated successfully');
        }

        /**
         * RESET BUTTON - Reset tombol jika error
         */
        function resetButton(deviceId, oldStatus) {
            const button = document.getElementById(`btn-${deviceId}`);
            button.className = `w-full py-3 px-4 rounded-lg font-bold transition-all duration-200 ${oldStatus ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600'} text-white shadow-md hover:shadow-lg`;
            button.textContent = oldStatus ? 'MATIKAN' : 'NYALAKAN';
            button.disabled = false;
            button.setAttribute('onclick', `toggleDevice(${deviceId}, ${!oldStatus})`);
        }

        /**
         * SWITCH MODE - Ganti mode manual/auto
         */
        function switchMode(deviceId, newMode) {
            console.log('🔄 Switching mode device:', deviceId, 'to:', newMode);
            
            showNotification(`Mengubah mode device ${deviceId} ke ${newMode.toUpperCase()}...`, 'warning');
            
            const threshold = newMode === 'auto' ? 28.0 : null;
            
            $.ajax({
                url: `/api/devices/${deviceId}/mode`,
                method: 'POST',
                data: {
                    mode: newMode,
                    auto_threshold: threshold,
                    _token: csrfToken
                },
                dataType: 'json',
                success: function(response) {
                    console.log('✅ Mode switch response:', response);
                    
                    if (response.success) {
                        showNotification(
                            `Mode ${response.device.label} berhasil diubah ke ${newMode.toUpperCase()}`, 
                            'success'
                        );
                        // Reload page untuk update tampilan lengkap
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showNotification('Gagal mengubah mode! ' + (response.message || ''), 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Mode switch error:', error);
                    showNotification('Error mengubah mode! Lihat console untuk detail.', 'error');
                }
            });
        }

        /**
         * UPDATE THRESHOLD - Ubah suhu threshold untuk auto mode
         */
        function updateThreshold(deviceId) {
            const input = document.getElementById(`threshold-input-${deviceId}`);
            const newThreshold = parseFloat(input.value);
            
            if (isNaN(newThreshold) || newThreshold < 20 || newThreshold > 40) {
                showNotification('Threshold harus antara 20°C - 40°C', 'error');
                return;
            }
            
            console.log('🌡️ Updating threshold device:', deviceId, 'to:', newThreshold);
            
            showNotification(`Mengubah threshold device ${deviceId} ke ${newThreshold}°C...`, 'warning');
            
            $.ajax({
                url: `/api/devices/${deviceId}/mode`,
                method: 'POST',
                data: {
                    mode: 'auto',
                    auto_threshold: newThreshold,
                    _token: csrfToken
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification(
                            `Threshold ${response.device.label} berhasil diubah ke ${newThreshold}°C`, 
                            'success'
                        );
                        // Update tampilan threshold
                        const thresholdValue = document.getElementById(`threshold-value-${deviceId}`);
                        if (thresholdValue) {
                            thresholdValue.textContent = newThreshold;
                        }
                    } else {
                        showNotification('Gagal mengubah threshold!', 'error');
                    }
                },
                error: function(xhr) {
                    console.error('Threshold update error:', xhr.responseText);
                    showNotification('Error mengubah threshold!', 'error');
                }
            });
        }

        /**
         * SHOW NOTIFICATION - Tampilkan notifikasi
         */
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const messageEl = document.getElementById('notification-message');
            const icon = notification.querySelector('i');
            
            // Set warna dan icon berdasarkan type
            let bgColor = 'bg-green-500';
            let iconClass = 'fa-check-circle';
            
            if (type === 'error') {
                bgColor = 'bg-red-500';
                iconClass = 'fa-exclamation-circle';
            } else if (type === 'warning') {
                bgColor = 'bg-orange-500';
                iconClass = 'fa-exclamation-triangle';
            }
            
            notification.className = `fixed top-4 right-4 text-white px-6 py-3 rounded-lg shadow-lg z-50 ${bgColor}`;
            icon.className = `fas ${iconClass} mr-2`;
            messageEl.textContent = message;
            
            notification.classList.remove('hidden');
            
            setTimeout(() => {
                notification.classList.add('hidden');
            }, 3000);
        }

        /**
         * AUTO REFRESH STATUS - Sync status dari server
         */
        function refreshDeviceStatus() {
            $.ajax({
                url: '/api/devices/status',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        response.data.forEach(device => {
                            // Update timestamp saja, biar terlihat fresh
                            const timestamp = document.getElementById(`timestamp-${device.id}`);
                            if (timestamp) {
                                timestamp.innerHTML = `<i class="far fa-clock mr-1"></i>Update: ${new Date().toLocaleTimeString()}`;
                            }
                        });
                    }
                }
            });
        }

        /**
         * UPDATE CURRENT TIME - Update waktu real-time
         */
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
            });
            const dateString = now.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            document.getElementById('current-time').innerHTML = `
                <div class="font-semibold">${timeString}</div>
                <div class="text-xs">${dateString}</div>
            `;
        }

        // Initialize saat page load
        $(document).ready(function() {
            console.log('🔧 Smart Home System Initialized');
            console.log('📱 Devices loaded:', [
                @foreach($devices as $device)
                {
                    id: {{ $device->id }},
                    label: "{{ $device->label }}", 
                    status: {{ $device->status ? 'true' : 'false' }},
                    mode: "{{ $device->mode }}",
                    auto_threshold: {{ $device->auto_threshold ?? 'null' }}
                },
                @endforeach
            ]);

            // Start auto refresh
            setInterval(refreshDeviceStatus, 10000); // 10 detik
            setInterval(updateCurrentTime, 1000); // 1 detik
            
            // Initial update
            updateCurrentTime();
            
            // Show welcome message
            setTimeout(() => {
                showNotification('Sistem Smart Home siap digunakan!', 'success');
            }, 1000);
        });
    </script>
</body>
</html>
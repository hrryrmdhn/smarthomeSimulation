<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Home Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">🏠 Smart Home Control</h1>
        
        <!-- Notification -->
        <div id="notification" class="hidden fixed top-4 right-4 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <span id="notification-message"></span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($devices as $device)
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <!-- Device Icon & Status -->
                <div class="mb-4">
                    <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center 
                        {{ $device->status ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' }}">
                        <i class="fas fa-lightbulb text-2xl"></i>
                    </div>
                </div>

                <!-- Device Name -->
                <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $device->label }}</h3>
                
                <!-- Status -->
                <div class="mb-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $device->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        <span class="w-2 h-2 rounded-full mr-2 
                            {{ $device->status ? 'bg-green-500' : 'bg-red-500' }}"></span>
                        {{ $device->status ? 'ON' : 'OFF' }}
                    </span>
                </div>

                <!-- Toggle Button -->
                <button onclick="toggleDevice({{ $device->id }}, {{ $device->status ? 'false' : 'true' }})" 
                        class="w-full py-2 px-4 rounded-lg font-medium transition-colors
                            {{ $device->status ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }} 
                            text-white"
                        id="btn-{{ $device->id }}">
                    {{ $device->status ? 'MATIKAN' : 'NYALAKAN' }}
                </button>

                <!-- Last Update -->
                <div class="mt-3 text-xs text-gray-500">
                    Update: {{ $device->updated_at->format('H:i:s') }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <script>
        const csrfToken = '{{ csrf_token() }}';

        function toggleDevice(deviceId, newStatus) {
            console.log('🚀 TOGGLE DEVICE:', {
                deviceId: deviceId,
                newStatus: newStatus,
                type: typeof newStatus
            });

            const button = document.getElementById(`btn-${deviceId}`);
            const originalText = button.textContent;
            
            // Show loading
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            button.disabled = true;
            
            // Pastikan kirim sebagai integer (1 atau 0) - lebih reliable
            const statusToSend = newStatus ? 1 : 0;
            console.log('📤 Sending status as:', statusToSend);
            
            // Send request
            $.ajax({
                url: `/api/devices/${deviceId}`,
                method: 'POST',
                data: {
                    status: statusToSend, // Kirim sebagai integer
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
                        showNotification('Gagal update! ' + (response.message || ''), 'error');
                        resetButton(deviceId, !newStatus);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ AJAX Error:', {
                        status: xhr.status,
                        response: xhr.responseText,
                        error: error
                    });
                    showNotification('Error! Lihat console untuk detail.', 'error');
                    resetButton(deviceId, !newStatus);
                }
            });
        }

        function updateDeviceUI(deviceId, newStatus, deviceData) {
            console.log('🎨 Updating UI for device:', deviceId, 'to:', newStatus);
            
            const deviceCard = document.querySelector(`#btn-${deviceId}`).closest('.bg-white');
            const statusBadge = deviceCard.querySelector('.inline-flex');
            const statusDot = deviceCard.querySelector('.w-2');
            const icon = deviceCard.querySelector('.fa-lightbulb').parentElement;
            const timestamp = deviceCard.querySelector('.text-xs');
            const button = document.getElementById(`btn-${deviceId}`);
            
            // Update status badge
            statusBadge.className = `inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                ${newStatus ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
            statusBadge.innerHTML = `
                <span class="w-2 h-2 rounded-full mr-2 ${newStatus ? 'bg-green-500' : 'bg-red-500'}"></span>
                ${newStatus ? 'ON' : 'OFF'}
            `;
            
            // Update icon
            icon.className = `w-16 h-16 mx-auto rounded-full flex items-center justify-center 
                ${newStatus ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'}`;
            
            // Update button
            button.className = `w-full py-2 px-4 rounded-lg font-medium transition-colors
                ${newStatus ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600'} 
                text-white`;
            button.textContent = newStatus ? 'MATIKAN' : 'NYALAKAN';
            button.disabled = false;
            button.setAttribute('onclick', `toggleDevice(${deviceId}, ${!newStatus})`);
            
            // Update timestamp
            timestamp.textContent = `Update: ${new Date().toLocaleTimeString()}`;
            
            console.log('✅ UI Updated successfully');
        }

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

        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const messageEl = document.getElementById('notification-message');
            
            notification.className = `fixed top-4 right-4 text-white px-6 py-3 rounded-lg shadow-lg z-50 
                ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            messageEl.textContent = message;
            
            notification.classList.remove('hidden');
            
            setTimeout(() => {
                notification.classList.add('hidden');
            }, 3000);
        }

        // Debug info saat page load
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
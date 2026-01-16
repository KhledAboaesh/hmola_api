// API_URL is now defined in index.html to be dynamic

function checkAuth() {
    const token = localStorage.getItem('admin_token');
    if (token) {
        document.getElementById('login-screen').style.display = 'none';
        document.getElementById('admin-panel').style.display = 'block';
        showSection('stats');
    }
}

async function adminLogin() {
    const phone = document.getElementById('login-phone').value;
    const password = document.getElementById('login-password').value;
    const errorEl = document.getElementById('login-error');

    try {
        const response = await fetch(`${API_URL}/login.php`, {
            method: 'POST',
            body: JSON.stringify({ phone, password })
        });
        const res = await response.json();

        if (res.status === 'success') {
            localStorage.setItem('admin_token', res.data.token);
            localStorage.setItem('admin_name', res.data.name);
            document.getElementById('login-screen').style.display = 'none';
            document.getElementById('admin-panel').style.display = 'block';
            showSection('stats');
        } else {
            errorEl.innerText = res.message;
            errorEl.style.display = 'block';
        }
    } catch (e) {
        errorEl.innerText = 'حدث خطأ في الاتصال بالسيرفر';
        errorEl.style.display = 'block';
    }
}

function adminLogout() {
    localStorage.clear();
    location.reload();
}

function showSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(s => s.style.display = 'none');

    // Deactivate all nav links
    document.querySelectorAll('.sidebar nav a').forEach(a => a.classList.remove('active'));

    // Show selected
    const target = document.getElementById(`${sectionId}-section`);
    if (target) target.style.display = 'block';

    // Activate link
    const link = document.querySelector(`.sidebar nav a[onclick*="${sectionId}"]`);
    if (link) link.classList.add('active');

    // Update Header Title
    const titles = {
        'stats': 'الرئيسية والمؤشرات',
        'vehicles': 'إدارة أنواع المركبات',
        'labor': 'خيارات العمالة المتاحة',
        'pending_drivers': 'طلبات انضمام السائقين',
        'users': 'قاعدة بيانات المستخدمين',
        'requests': 'سجل الطلبات والرحلات',
        'settings': 'إعدادات النظام'
    };
    document.getElementById('page-title').innerText = titles[sectionId] || 'لوحة التحكم';

    // Show/Hide Add Button
    const addBtn = document.getElementById('add-btn');
    if (['vehicles', 'labor'].includes(sectionId)) {
        addBtn.style.display = 'flex';
    } else {
        addBtn.style.display = 'none';
    }

    // Refresh Lucide Icons
    if (window.lucide) window.lucide.createIcons();

    // Load Data
    if (sectionId === 'vehicles') loadVehicles();
    if (sectionId === 'labor') loadLabor();
    if (sectionId === 'pending_drivers') loadPendingDrivers();
    if (sectionId === 'users') loadUsers();
    if (sectionId === 'requests') loadRequests();
    if (sectionId === 'stats') loadStats();
    if (sectionId === 'settings') loadSettings();
}

// Stats & Charts
let rideChart = null;
let adminMap = null;
let mapMarkers = [];

async function loadStats() {
    try {
        const response = await fetch(`${API_URL}/stats.php`);
        const res = await response.json();
        if (res.status === 'success') {
            const d = res.data;
            document.getElementById('stat-users').innerText = d.summary.total_users;
            document.getElementById('stat-drivers').innerText = d.summary.verified_drivers;
            document.getElementById('stat-rides').innerText = d.summary.completed_rides;
            renderChart(d.chart);
            initAdminMap();
            loadMapData();
        }
    } catch (e) { console.error(e); }
}

function initAdminMap() {
    if (adminMap) return;
    adminMap = L.map('adminMap').setView([24.7136, 46.6753], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(adminMap);

    // Set interval to refresh map every 30 seconds
    setInterval(loadMapData, 30000);
}

async function loadMapData() {
    if (!adminMap) return;
    try {
        const response = await fetch(`${API_URL}/live_operations.php`);
        const res = await response.json();
        if (res.status === 'success') {
            // Clear old markers
            mapMarkers.forEach(m => adminMap.removeLayer(m));
            mapMarkers = [];

            // Add Drivers
            res.data.drivers.forEach(d => {
                const marker = L.marker([d.latitude, d.longitude], {
                    icon: L.divIcon({
                        className: 'custom-driver-icon',
                        html: `<div style="background: #2D5AF0; width: 12px; height: 12px; border: 2px solid white; border-radius: 50%; box-shadow: 0 0 10px rgba(45,90,240,0.5);"></div>`
                    })
                }).addTo(adminMap).bindPopup(`<b>سائق: ${d.name}</b><br>${d.phone}`);
                mapMarkers.push(marker);
            });

            // Add Requests
            res.data.requests.forEach(r => {
                const marker = L.marker([r.pickup_lat, r.pickup_lng], {
                    icon: L.divIcon({
                        className: 'custom-request-icon',
                        html: `<div style="background: #10B981; width: 10px; height: 10px; border: 2px solid white; border-radius: 50%;"></div>`
                    })
                }).addTo(adminMap).bindPopup(`<b>طلب جديد #${r.id}</b>`);
                mapMarkers.push(marker);
            });
        }
    } catch (e) { console.error(e); }
}

function renderChart(data) {
    const ctx = document.getElementById('ridesChart').getContext('2d');
    if (rideChart) rideChart.destroy();

    rideChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(i => i.date),
            datasets: [{
                label: 'عدد الرحلات',
                data: data.map(i => i.count),
                borderColor: '#2D5AF0',
                backgroundColor: 'rgba(45, 90, 240, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#F3F4F6' } },
                x: { grid: { display: false } }
            }
        }
    });
}

// Settings Logic
async function loadSettings() {
    try {
        const response = await fetch(`${API_URL}/settings.php`);
        const res = await response.json();
        if (res.status === 'success') {
            document.getElementById('setting-commission').value = res.data.commission_percentage || 10;
            document.getElementById('setting-support').value = res.data.support_phone || '';
        }
    } catch (e) { console.error(e); }
}

async function saveSettings() {
    const commission = document.getElementById('setting-commission').value;
    const support = document.getElementById('setting-support').value;

    const payload = {
        commission_percentage: commission,
        support_phone: support
    };

    try {
        const response = await fetch(`${API_URL}/settings.php`, {
            method: 'POST',
            body: JSON.stringify(payload)
        });
        const res = await response.json();
        if (res.status === 'success') {
            alert('تم حفظ الإعدادات بنجاح');
        } else {
            alert('فشل الحفظ: ' + res.message);
        }
    } catch (e) { alert('خطأ في الاتصال بالخادم'); }
}

// Operational Reset
function confirmResetTrips() {
    if (confirm("هل أنت متأكد من رغبتك في تصفير كافة الرحلات والعروض؟ لا يمكن التراجع عن هذا الإجراء.")) {
        resetTrips();
    }
}

async function resetTrips() {
    try {
        const response = await fetch(`${API_URL}/reset_trips.php`, { method: 'POST' });
        const res = await response.json();
        if (res.status === 'success') {
            alert("تم التصفير بنجاح");
            showSection('stats');
        } else {
            alert("فشل التصفير: " + res.message);
        }
    } catch (e) { alert("خطأ في الاتصال بالسيرفر"); }
}

// Vehicle Management
async function loadVehicles() {
    try {
        const response = await fetch(`${API_URL}/vehicles.php`);
        const res = await response.json();
        renderTable('vehicles-table', res.data, 'vehicle');
    } catch (e) { console.error(e); }
}

// Labor Management
async function loadLabor() {
    try {
        const response = await fetch(`${API_URL}/labor_options.php`);
        const res = await response.json();
        renderTable('labor-table', res.data, 'labor');
    } catch (e) { console.error(e); }
}

// Pending Drivers
async function loadPendingDrivers() {
    try {
        const response = await fetch(`${API_URL}/pending_drivers.php`);
        const res = await response.json();
        const tbody = document.querySelector('#pending-drivers-table tbody');
        tbody.innerHTML = res.data.map(d => `
            <tr>
                <td>${d.id}</td>
                <td>${d.name}</td>
                <td>${d.phone}</td>
                <td>${d.vehicle_type || 'غير محدد'}</td>
                <td>${d.plate_number}</td>
                <td>${new Date(d.created_at).toLocaleDateString()}</td>
                <td>
                    <button class="btn btn-primary" onclick="viewLicense('${d.license_photo}')"><i data-lucide="eye"></i>رخصة</button>
                    <button class="btn btn-primary" style="background: #059669;" onclick="approveDriver(${d.id})"><i data-lucide="check"></i>قبول</button>
                </td>
            </tr>
        `).join('');
        if (window.lucide) window.lucide.createIcons();
    } catch (e) { console.error(e); }
}

// Users Management
async function loadUsers() {
    try {
        const response = await fetch(`${API_URL}/users.php`);
        const res = await response.json();
        const tbody = document.querySelector('#users-table tbody');
        tbody.innerHTML = res.data.map(u => `
            <tr>
                <td>${u.id}</td>
                <td>${u.name}</td>
                <td>${u.phone}</td>
                <td><span class="badge badge-info">${u.role}</span></td>
                <td>${new Date(u.created_at).toLocaleDateString()}</td>
                <td>
                    <button class="btn btn-danger" onclick="deleteUser(${u.id})"><i data-lucide="trash-2"></i></button>
                </td>
            </tr>
        `).join('');
        if (window.lucide) window.lucide.createIcons();
    } catch (e) { console.error(e); }
}

// Requests Management
async function loadRequests() {
    try {
        const response = await fetch(`${API_URL}/requests.php`);
        const res = await response.json();
        const tbody = document.querySelector('#requests-table tbody');
        tbody.innerHTML = res.data.map(r => `
            <tr>
                <td>${r.id}</td>
                <td>${r.client_name}</td>
                <td>${r.driver_name || '-'}</td>
                <td>${r.vehicle_name_ar}</td>
                <td>${r.final_price || '-'}</td>
                <td><span class="badge ${r.status === 'completed' ? 'badge-active' : 'badge-info'}">${_getStatusAr(r.status)}</span></td>
            </tr>
        `).join('');
        if (window.lucide) window.lucide.createIcons();
    } catch (e) { console.error(e); }
}

function _getStatusAr(status) {
    const map = { 'pending': 'قيد الانتظار', 'accepted': 'مقبول', 'in_progress': 'قيد التنفيذ', 'completed': 'مكتمل', 'cancelled': 'ملغى' };
    return map[status] || status;
}

function renderTable(tableId, data, type) {
    const tbody = document.querySelector(`#${tableId} tbody`);
    tbody.innerHTML = data.map(item => `
        <tr>
            <td>${item.id}</td>
            <td>${item.name_ar}</td>
            <td>${item.name_en}</td>
            <td><span class="badge ${item.is_active ? 'badge-active' : 'badge-inactive'}">${item.is_active ? 'نشط' : 'معطل'}</span></td>
            <td>
                <button class="btn btn-primary" onclick="openEditModal('${type}', ${JSON.stringify(item).replace(/"/g, '&quot;')})"><i data-lucide="edit-3"></i></button>
                <button class="btn btn-danger" onclick="deleteItem('${type}', ${item.id})"><i data-lucide="trash-2"></i></button>
            </td>
        </tr>
    `).join('');
    if (window.lucide) window.lucide.createIcons();
}

// Modals
function openAddModal() {
    const activeSection = document.querySelector('.sidebar nav a.active');
    if (!activeSection) return;
    const clickAttr = activeSection.getAttribute('onclick');
    const match = clickAttr ? clickAttr.match(/'([^']+)'/) : null;
    if (!match) return;
    const currentSection = match[1];

    document.getElementById('modal-title').innerText = currentSection === 'vehicles' ? 'إضافة مركبة جديدة' : 'إضافة خيار عمالة';
    document.getElementById('data-form').reset();
    document.getElementById('item-id').value = '';

    // Show/hide image field for vehicles
    const imageField = document.getElementById('vehicle-image-field');
    if (currentSection === 'vehicles') {
        imageField.style.display = 'block';
        document.getElementById('image-preview').style.display = 'none';
        document.getElementById('vehicle-image').onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    document.getElementById('preview-img').src = ev.target.result;
                    document.getElementById('image-preview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        };
    } else {
        imageField.style.display = 'none';
    }

    document.getElementById('modal').style.display = 'block';
}

function openEditModal(type, item) {
    document.getElementById('modal-title').innerText = 'تعديل البيانات';
    document.getElementById('item-id').value = item.id;
    document.getElementById('name-ar').value = item.name_ar;
    document.getElementById('name-en').value = item.name_en;
    document.getElementById('is-active').value = item.is_active ? "1" : "0";

    // Handle image field for vehicles
    const imageField = document.getElementById('vehicle-image-field');
    const previewArea = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');

    if (type === 'vehicles') {
        imageField.style.display = 'block';
        if (item.image) {
            previewImg.src = `${HOST_URL}/${item.image}`;
            previewArea.style.display = 'block';
        } else {
            previewArea.style.display = 'none';
        }
        document.getElementById('vehicle-image').onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    previewImg.src = ev.target.result;
                    previewArea.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        };
    } else {
        imageField.style.display = 'none';
    }

    document.getElementById('modal').style.display = 'block';
}

function closeModal() { document.getElementById('modal').style.display = 'none'; }

document.getElementById('data-form').onsubmit = async (e) => {
    e.preventDefault();
    const activeSection = document.querySelector('.sidebar nav a.active');
    if (!activeSection) return;
    const clickAttr = activeSection.getAttribute('onclick');
    const match = clickAttr ? clickAttr.match(/'([^']+)'/) : null;
    if (!match) return;
    const currentSection = match[1];

    const endpoint = currentSection === 'vehicles' ? 'vehicles.php' : 'labor_options.php';
    const id = document.getElementById('item-id').value;

    let body;
    let headers = {};

    if (currentSection === 'vehicles') {
        // Use FormData for vehicles to handle image upload
        const formData = new FormData();
        if (id) formData.append('id', id);
        formData.append('name_ar', document.getElementById('name-ar').value);
        formData.append('name_en', document.getElementById('name-en').value);
        formData.append('is_active', document.getElementById('is-active').value);

        const fileInput = document.getElementById('vehicle-image');
        if (fileInput.files[0]) {
            formData.append('image', fileInput.files[0]);
        }
        body = formData;
    } else {
        // Use JSON for labor options
        const payload = {
            name_ar: document.getElementById('name-ar').value,
            name_en: document.getElementById('name-en').value,
            is_active: document.getElementById('is-active').value
        };
        if (id) payload.id = id;
        body = JSON.stringify(payload);
        headers['Content-Type'] = 'application/json';
    }

    const response = await fetch(`${API_URL}/${endpoint}`, {
        method: id ? 'POST' : 'POST', // Some PHP setups prefer POST for file uploads even for updates
        body: body,
        headers: headers
    });

    const res = await response.json();
    if (res.status === 'success') {
        closeModal();
        if (currentSection === 'vehicles') loadVehicles(); else loadLabor();
    } else {
        alert(res.message);
    }
};

async function deleteItem(type, id) {
    if (!confirm('هل أنت متأكد من الحذف؟')) return;
    const endpoint = type === 'vehicle' ? 'vehicles.php' : 'labor_options.php';
    try {
        const response = await fetch(`${API_URL}/${endpoint}`, {
            method: 'DELETE',
            body: JSON.stringify({ id })
        });
        const res = await response.json();
        if (res.status === 'success') { if (type === 'vehicle') loadVehicles(); else loadLabor(); }
    } catch (e) { console.error(e); }
}

async function approveDriver(id) {
    try {
        const response = await fetch(`${API_URL}/pending_drivers.php`, {
            method: 'POST',
            body: JSON.stringify({ user_id: id })
        });
        const res = await response.json();
        if (res.status === 'success') loadPendingDrivers();
    } catch (e) { console.error(e); }
}

function viewLicense(photo) {
    document.getElementById('license-image').src = `../${photo}`;
    document.getElementById('image-modal').style.display = 'block';
}

function closeImageModal() { document.getElementById('image-modal').style.display = 'none'; }

async function deleteUser(id) {
    if (!confirm('سيتم حذف المستخدم نهائياً، هل أنت متأكد؟')) return;
    try {
        const response = await fetch(`${API_URL}/users.php`, {
            method: 'DELETE',
            body: JSON.stringify({ id })
        });
        const res = await response.json();
        if (res.status === 'success') loadUsers();
    } catch (e) { console.error(e); }
}

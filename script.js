const DB_NAME = 'TodoAppDB';
const DB_VERSION = 1;
const STORE_NAME = 'todos';
const THEME_KEY = 'todo_theme_preference';

const DEFAULT_TODOS = [
    {
        id: 1,
        title: "PBO",
        description: "Tugas Pemrograman Berorientasi Objek",
        completed: false,
        image: null,
        notificationTime: "",
        notified: false
    },
    {
        id: 2,
        title: "Matdis 2",
        description: "Tugas Harian Matematika Diskrit 2",
        completed: false,
        image: null,
        notificationTime: "",
        notified: false
    },
    {
        id: 3,
        title: "KKA",
        description: "Perancangan Kecerdasan Artifisial",
        completed: false,
        image: null,
        notificationTime: "",
        notified: false
    }
];

let dbInstance = null;
let currentEditId = null;
let currentImageData = null;
let cameraStream = null;
let swRegistration = null;
let lastFocusedElement = null;

function openDatabase() {
    return new Promise((resolve, reject) => {
        if (dbInstance) {
            resolve(dbInstance);
            return;
        }

        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains(STORE_NAME)) {
                const store = db.createObjectStore(STORE_NAME, { keyPath: 'id' });
                store.createIndex('completed', 'completed', { unique: false });
                store.createIndex('notificationTime', 'notificationTime', { unique: false });
            }
        };

        request.onsuccess = (event) => {
            dbInstance = event.target.result;
            resolve(dbInstance);
        };

        request.onerror = (event) => {
            reject(event.target.error);
        };
    });
}

function dbGetAll() {
    return openDatabase().then((db) => {
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readonly');
            const store = tx.objectStore(STORE_NAME);
            const request = store.getAll();

            request.onsuccess = () => resolve(request.result || []);
            request.onerror = () => reject(request.error);
        });
    });
}

function dbAdd(todo) {
    return openDatabase().then((db) => {
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            const request = store.add(todo);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    });
}

function dbUpdate(todo) {
    return openDatabase().then((db) => {
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            const request = store.put(todo);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    });
}

function dbDelete(id) {
    return openDatabase().then((db) => {
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            const request = store.delete(id);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    });
}

function dbGetById(id) {
    return openDatabase().then((db) => {
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readonly');
            const store = tx.objectStore(STORE_NAME);
            const request = store.get(id);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    });
}

function initServiceWorker() {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('sw.js')
            .then((registration) => {
                swRegistration = registration;
            })
            .catch(() => {});
    }
}

function checkScheduledNotifications() {
    dbGetAll().then((todos) => {
        const now = new Date();
        todos.forEach((todo) => {
            if (!todo.completed && todo.notificationTime && !todo.notified) {
                const targetTime = new Date(todo.notificationTime);
                if (targetTime <= now) {
                    todo.notified = true;
                    dbUpdate(todo).then(() => {
                        triggerNotification(todo);
                    });
                }
            }
        });
    });
}

function triggerNotification(todo) {
    const title = `Pengingat Tugas: ${todo.title}`;
    const options = {
        body: todo.description ? todo.description : 'Saatnya menyelesaikan tugas kuliah ini!',
        icon: todo.image ? todo.image : undefined,
        tag: `todo-notif-${todo.id}`,
        requireInteraction: true
    };

    if (swRegistration && 'showNotification' in swRegistration) {
        swRegistration.showNotification(title, options);
    } else if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, options);
    }
}

function announceA11y(message) {
    const liveRegion = document.getElementById('sr-announcements');
    if (liveRegion) {
        liveRegion.textContent = '';
        setTimeout(() => {
            liveRegion.textContent = message;
        }, 50);
    }
}

function initThemePreference() {
    const savedTheme = localStorage.getItem(THEME_KEY);
    const themeToggleBtn = document.getElementById('theme-toggle');

    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        if (themeToggleBtn) {
            themeToggleBtn.setAttribute('aria-pressed', 'true');
            themeToggleBtn.setAttribute('aria-label', 'Ganti ke mode terang (saat ini mode gelap)');
            themeToggleBtn.innerHTML = '<i class="fa-solid fa-sun" aria-hidden="true"></i> <span>Mode Terang</span>';
        }
    } else {
        document.body.classList.remove('dark-mode');
        if (themeToggleBtn) {
            themeToggleBtn.setAttribute('aria-pressed', 'false');
            themeToggleBtn.setAttribute('aria-label', 'Ganti ke mode gelap (saat ini mode terang)');
            themeToggleBtn.innerHTML = '<i class="fa-solid fa-moon" aria-hidden="true"></i> <span>Mode Gelap</span>';
        }
    }
}

function toggleTheme() {
    const isDark = document.body.classList.toggle('dark-mode');
    const themeToggleBtn = document.getElementById('theme-toggle');

    localStorage.setItem(THEME_KEY, isDark ? 'dark' : 'light');

    if (themeToggleBtn) {
        themeToggleBtn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        themeToggleBtn.setAttribute('aria-label', isDark ? 'Ganti ke mode terang (saat ini mode gelap)' : 'Ganti ke mode gelap (saat ini mode terang)');
        themeToggleBtn.innerHTML = isDark 
            ? '<i class="fa-solid fa-sun" aria-hidden="true"></i> <span>Mode Terang</span>'
            : '<i class="fa-solid fa-moon" aria-hidden="true"></i> <span>Mode Gelap</span>';
    }

    announceA11y(isDark ? 'Mode gelap diaktifkan.' : 'Mode terang diaktifkan.');
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDatetime(dtString) {
    if (!dtString) return '';
    const date = new Date(dtString);
    if (isNaN(date.getTime())) return dtString;
    return date.toLocaleString('id-ID', {
        dateStyle: 'short',
        timeStyle: 'short'
    });
}

function renderTodoList() {
    const todoListElement = document.getElementById('todo-list');
    const todoCountElement = document.getElementById('todo-count');
    if (!todoListElement) return;

    dbGetAll().then((todos) => {
        todoListElement.innerHTML = '';

        const total = todos.length;
        const completedCount = todos.filter(t => t.completed).length;

        if (todoCountElement) {
            todoCountElement.textContent = `${total} Tasks ${total > 0 ? `(${completedCount} Selesai)` : ''}`;
        }

        if (todos.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.setAttribute('role', 'status');
            empty.innerHTML = `
                <i class="fa-regular fa-clipboard" aria-hidden="true"></i>
                <p>Belum ada tugas. Silakan tambahkan tugas baru melalui form di samping!</p>
            `;
            todoListElement.appendChild(empty);
            return;
        }

        todos.forEach((todo) => {
            const card = document.createElement('article');
            card.className = `todo-card ${todo.completed ? 'completed' : ''} ${currentEditId === todo.id ? 'active' : ''}`;
            card.dataset.id = todo.id;
            card.setAttribute('role', 'listitem');

            const todoMain = document.createElement('div');
            todoMain.className = 'todo-main';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'todo-checkbox';
            checkbox.checked = todo.completed;
            checkbox.id = `todo-check-${todo.id}`;
            checkbox.setAttribute('aria-label', `Tandai tugas ${todo.title} sebagai ${todo.completed ? 'belum selesai' : 'selesai'}`);

            todoMain.appendChild(checkbox);

            if (todo.image) {
                const img = document.createElement('img');
                img.src = todo.image;
                img.alt = `Foto lampiran tugas ${todo.title}`;
                img.className = 'todo-thumb';
                img.tabIndex = 0;
                img.setAttribute('role', 'button');
                img.setAttribute('aria-label', `Lihat ukuran penuh foto tugas ${todo.title}`);
                img.addEventListener('click', () => {
                    const win = window.open();
                    if (win) {
                        win.document.write(`<img src="${todo.image}" alt="Foto tugas ${escapeHtml(todo.title)}" style="max-width:100%;height:auto;">`);
                    }
                });
                todoMain.appendChild(img);
            }

            const info = document.createElement('div');
            info.className = 'todo-info';

            const title = document.createElement('h3');
            title.textContent = todo.title;
            info.appendChild(title);

            const desc = document.createElement('p');
            desc.textContent = todo.description || 'Tidak ada deskripsi.';
            info.appendChild(desc);

            if (todo.notificationTime) {
                const badge = document.createElement('div');
                badge.className = 'todo-notif-badge';
                badge.setAttribute('aria-label', `Pengingat dijadwalkan pada ${formatDatetime(todo.notificationTime)}`);
                badge.innerHTML = `<i class="fa-regular fa-bell" aria-hidden="true"></i> <span>${formatDatetime(todo.notificationTime)}</span>`;
                info.appendChild(badge);
            }

            todoMain.appendChild(info);

            const actions = document.createElement('div');
            actions.className = 'todo-actions';

            const editBtn = document.createElement('button');
            editBtn.type = 'button';
            editBtn.className = 'btn-action btn-edit';
            editBtn.setAttribute('aria-label', `Edit tugas ${todo.title}`);
            editBtn.innerHTML = '<i class="fa-solid fa-pen-to-square" aria-hidden="true"></i> <span>Edit</span>';

            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'btn-action btn-delete';
            deleteBtn.setAttribute('aria-label', `Hapus tugas ${todo.title}`);
            deleteBtn.innerHTML = '<i class="fa-solid fa-trash" aria-hidden="true"></i> <span>Hapus</span>';

            actions.appendChild(editBtn);
            actions.appendChild(deleteBtn);

            card.appendChild(todoMain);
            card.appendChild(actions);

            todoListElement.appendChild(card);
        });
    });
}

function handleImageFileUpload(event) {
    const file = event.target.files && event.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        alert('Berkas harus berupa gambar (JPEG, PNG, dll.)');
        return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        currentImageData = e.target.result;
        displayImagePreview(currentImageData);
        announceA11y('Foto tugas berhasil dilampirkan dari berkas.');
    };
    reader.readAsDataURL(file);
}

function displayImagePreview(dataUrl) {
    const previewContainer = document.getElementById('image-preview-container');
    const previewImg = document.getElementById('image-preview');

    if (previewContainer && previewImg) {
        previewImg.src = dataUrl;
        previewContainer.style.display = 'flex';
    }
}

function removeImageAttachment() {
    currentImageData = null;
    const fileInput = document.getElementById('image-upload');
    if (fileInput) fileInput.value = '';

    const previewContainer = document.getElementById('image-preview-container');
    const previewImg = document.getElementById('image-preview');

    if (previewContainer && previewImg) {
        previewImg.src = '';
        previewContainer.style.display = 'none';
    }

    announceA11y('Foto lampiran dihapus.');
}

function openCameraModal() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert('Fitur kamera tidak didukung pada browser ini. Silakan gunakan tombol Pilih Berkas.');
        return;
    }

    lastFocusedElement = document.activeElement;

    const modal = document.getElementById('camera-modal');
    const video = document.getElementById('camera-stream');

    navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } },
        audio: false
    })
    .then((stream) => {
        cameraStream = stream;
        video.srcObject = stream;
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');

        const captureBtn = document.getElementById('btn-capture-photo');
        if (captureBtn) captureBtn.focus();

        announceA11y('Jendela kamera aktif. Arahkan kamera dan tekan Ambil Foto.');
    })
    .catch(() => {
        alert('Tidak dapat mengakses kamera perangkat. Pastikan izin kamera telah diberikan di browser.');
    });
}

function closeCameraModal() {
    const modal = document.getElementById('camera-modal');
    const video = document.getElementById('camera-stream');

    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }

    if (video) {
        video.srcObject = null;
    }

    if (modal) {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }

    if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
        lastFocusedElement.focus();
    }
}

function capturePhotoFromCamera() {
    const video = document.getElementById('camera-stream');
    const canvas = document.getElementById('camera-canvas');

    if (!video || !canvas) return;

    const width = video.videoWidth || 640;
    const height = video.videoHeight || 480;

    canvas.width = width;
    canvas.height = height;

    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, width, height);

    currentImageData = canvas.toDataURL('image/jpeg', 0.85);

    closeCameraModal();
    displayImagePreview(currentImageData);
    announceA11y('Foto berhasil dijepret dari kamera dan dilampirkan.');
}

function handleFormSubmit(event) {
    event.preventDefault();

    const titleInput = document.getElementById('title');
    const descInput = document.getElementById('description');
    const notifInput = document.getElementById('notification-time');

    const titleValue = titleInput ? titleInput.value.trim() : '';
    const descValue = descInput ? descInput.value.trim() : '';
    const notifValue = notifInput ? notifInput.value : '';

    if (!titleValue) {
        if (titleInput) {
            titleInput.setAttribute('aria-invalid', 'true');
            titleInput.focus();
        }
        announceA11y('Peringatan: Nama mata kuliah wajib diisi!');
        alert('Nama mata kuliah wajib diisi!');
        return;
    }

    if (titleInput) {
        titleInput.removeAttribute('aria-invalid');
    }

    if (notifValue && 'Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    if (currentEditId !== null) {
        dbGetById(currentEditId).then((existingTodo) => {
            if (!existingTodo) return;

            const updatedTodo = {
                ...existingTodo,
                title: titleValue,
                description: descValue,
                image: currentImageData,
                notificationTime: notifValue,
                notified: existingTodo.notificationTime === notifValue ? existingTodo.notified : false
            };

            return dbUpdate(updatedTodo).then(() => {
                resetForm();
                renderTodoList();
                announceA11y(`Tugas ${titleValue} berhasil diperbarui.`);
            });
        });
    } else {
        const newTodo = {
            id: Date.now(),
            title: titleValue,
            description: descValue,
            completed: false,
            image: currentImageData,
            notificationTime: notifValue,
            notified: false
        };

        dbAdd(newTodo).then(() => {
            resetForm();
            renderTodoList();
            announceA11y(`Tugas baru ${titleValue} berhasil ditambahkan.`);
        });
    }
}

function startEditTodo(id) {
    dbGetById(id).then((todo) => {
        if (!todo) return;

        currentEditId = todo.id;

        const titleInput = document.getElementById('title');
        const descInput = document.getElementById('description');
        const notifInput = document.getElementById('notification-time');
        const formTitle = document.getElementById('form-title');
        const submitBtn = document.getElementById('btn-submit');
        const cancelBtn = document.getElementById('btn-cancel');

        if (titleInput) titleInput.value = todo.title;
        if (descInput) descInput.value = todo.description || '';
        if (notifInput) notifInput.value = todo.notificationTime || '';

        currentImageData = todo.image || null;
        if (currentImageData) {
            displayImagePreview(currentImageData);
        } else {
            removeImageAttachment();
        }

        if (formTitle) formTitle.textContent = 'Edit Tugas';
        if (submitBtn) {
            submitBtn.textContent = 'Simpan Perubahan';
            submitBtn.setAttribute('aria-label', `Simpan perubahan pada tugas ${todo.title}`);
        }
        if (cancelBtn) cancelBtn.style.display = 'inline-flex';

        if (titleInput) titleInput.focus();
        renderTodoList();
        announceA11y(`Sedang mengedit tugas ${todo.title}. Form telah terisi.`);
    });
}

function resetForm() {
    currentEditId = null;
    currentImageData = null;

    const form = document.getElementById('todo-form');
    if (form) form.reset();

    const formTitle = document.getElementById('form-title');
    const submitBtn = document.getElementById('btn-submit');
    const cancelBtn = document.getElementById('btn-cancel');

    if (formTitle) formTitle.textContent = 'Tambah Tugas Baru';
    if (submitBtn) {
        submitBtn.textContent = 'Tambah Tugas';
        submitBtn.setAttribute('aria-label', 'Simpan dan tambahkan tugas baru ke daftar');
    }
    if (cancelBtn) cancelBtn.style.display = 'none';

    removeImageAttachment();
}

function deleteTodoItem(id) {
    dbGetById(id).then((todo) => {
        const title = todo ? todo.title : 'tugas ini';
        if (confirm(`Apakah Anda yakin ingin menghapus tugas "${title}"?`)) {
            dbDelete(id).then(() => {
                if (currentEditId === id) {
                    resetForm();
                }
                renderTodoList();
                announceA11y(`Tugas ${title} berhasil dihapus.`);
            });
        }
    });
}

function toggleTodoCompletion(id, isCompleted) {
    dbGetById(id).then((todo) => {
        if (!todo) return;
        todo.completed = isCompleted;
        dbUpdate(todo).then(() => {
            renderTodoList();
            announceA11y(`Tugas ${todo.title} ditandai sebagai ${isCompleted ? 'selesai' : 'belum selesai'}.`);
        });
    });
}

function setupEventListeners() {
    const themeBtn = document.getElementById('theme-toggle');
    if (themeBtn) {
        themeBtn.addEventListener('click', toggleTheme);
    }

    const form = document.getElementById('todo-form');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }

    const cancelBtn = document.getElementById('btn-cancel');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            resetForm();
            renderTodoList();
            announceA11y('Proses edit dibatalkan.');
        });
    }

    const fileInput = document.getElementById('image-upload');
    if (fileInput) {
        fileInput.addEventListener('change', handleImageFileUpload);
    }

    const fileLabel = document.querySelector('label[for="image-upload"]');
    if (fileLabel) {
        fileLabel.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (fileInput) fileInput.click();
            }
        });
    }

    const openCameraBtn = document.getElementById('btn-open-camera');
    if (openCameraBtn) {
        openCameraBtn.addEventListener('click', openCameraModal);
    }

    const closeCameraBtn = document.getElementById('btn-close-camera');
    if (closeCameraBtn) {
        closeCameraBtn.addEventListener('click', closeCameraModal);
    }

    const capturePhotoBtn = document.getElementById('btn-capture-photo');
    if (capturePhotoBtn) {
        capturePhotoBtn.addEventListener('click', capturePhotoFromCamera);
    }

    const removeImageBtn = document.getElementById('btn-remove-image');
    if (removeImageBtn) {
        removeImageBtn.addEventListener('click', removeImageAttachment);
    }

    const cameraModal = document.getElementById('camera-modal');
    if (cameraModal) {
        cameraModal.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeCameraModal();
            }
        });
    }

    const todoListElement = document.getElementById('todo-list');
    if (todoListElement) {
        todoListElement.addEventListener('click', (event) => {
            const card = event.target.closest('.todo-card');
            if (!card) return;

            const todoId = parseInt(card.dataset.id, 10);

            if (event.target.closest('.btn-edit')) {
                startEditTodo(todoId);
                return;
            }

            if (event.target.closest('.btn-delete')) {
                deleteTodoItem(todoId);
                return;
            }
        });

        todoListElement.addEventListener('change', (event) => {
            if (event.target.classList.contains('todo-checkbox')) {
                const card = event.target.closest('.todo-card');
                if (!card) return;
                const todoId = parseInt(card.dataset.id, 10);
                toggleTodoCompletion(todoId, event.target.checked);
            }
        });
    }
}

function initializeApp() {
    initThemePreference();
    setupEventListeners();
    initServiceWorker();

    openDatabase()
        .then(() => dbGetAll())
        .then((existingTodos) => {
            if (existingTodos.length === 0) {
                const seedPromises = DEFAULT_TODOS.map(t => dbAdd(t));
                return Promise.all(seedPromises);
            }
        })
        .then(() => {
            renderTodoList();
            setInterval(checkScheduledNotifications, 10000);
        })
        .catch((err) => {
            console.error('Inisialisasi aplikasi gagal:', err);
        });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp);
} else {
    initializeApp();
}

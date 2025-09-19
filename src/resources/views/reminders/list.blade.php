<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ingetin Aku</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
/* Modal fade-in animation */
.modal-show {
  animation: fadeIn 0.3s ease-out forwards;
}
.modal-hide {
  animation: fadeOut 0.3s ease-out forwards;
}
@keyframes fadeIn { from {opacity:0;} to {opacity:1;} }
@keyframes fadeOut { from {opacity:1;} to {opacity:0;} }
</style>
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-4xl mx-auto bg-white p-6 rounded-xl shadow-lg">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Ingetin Aku 🙏</h1>

    <button id="addBtn" class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white px-6 py-2 rounded-xl shadow hover:from-blue-600 hover:to-indigo-600 transition-all mb-6">+ Add Reminder</button>

    <!-- List -->
    <ul id="reminderList" class="space-y-4"></ul>
</div>

<!-- Modal -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 relative transform scale-95 transition-transform duration-200">
    <h2 id="modalTitle" class="text-2xl font-semibold mb-4 text-gray-700">Add Reminder</h2>
    <div id="formErrors" class="text-red-500 text-sm mb-2 hidden"></div>
    <form id="reminderForm" class="space-y-4">
      <div>
        <label class="block text-gray-600 font-medium mb-1">Title</label>
        <input type="text" id="title" placeholder="Reminder title" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" required>
      </div>
      <div>
        <label class="block text-gray-600 font-medium mb-1">Description</label>
        <textarea id="description" placeholder="Describe your reminder" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none"></textarea>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-gray-600 font-medium mb-1">⏰ Remind At</label>
          <input type="datetime-local" id="remind_at" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" required>
        </div>
        <div>
          <label class="block text-gray-600 font-medium mb-1">⌚ Event At</label>
          <input type="datetime-local" id="event_at" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" required>
        </div>
      </div>
      <input type="hidden" id="reminderId">
      <div class="flex justify-end gap-3 mt-4">
        <button type="button" id="cancelBtn" class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition">Cancel</button>
        <button type="submit" class="px-5 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-lg hover:from-blue-600 hover:to-indigo-600 transition">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Toast -->
<div id="toast" class="fixed top-6 right-6 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg opacity-0 transition-opacity"></div>
<script>
const API_BASE = '/api';
let accessToken = localStorage.getItem('access_token');
let refreshToken = localStorage.getItem('refresh_token');
if (!accessToken) {
    window.location.href = '/login';
}

let reminders = [];

// DOM Elements
const modal = document.getElementById('modal');
const addBtn = document.getElementById('addBtn');
const cancelBtn = document.getElementById('cancelBtn');
const form = document.getElementById('reminderForm');
const list = document.getElementById('reminderList');
const modalTitle = document.getElementById('modalTitle');
const toast = document.getElementById('toast');
const titleInput = document.getElementById('title');
const descriptionInput = document.getElementById('description');
const remindInput = document.getElementById('remind_at');
const eventInput = document.getElementById('event_at');
const statusInput = document.getElementById('status');
const idInput = document.getElementById('reminderId');
const formErrors = document.getElementById('formErrors');

// Toast function
function showToast(msg, color = 'green') {
    toast.textContent = msg;
    toast.style.backgroundColor = color === 'green' ? '#22c55e' : '#ef4444';
    toast.style.opacity = 1;
    setTimeout(() => { toast.style.opacity = 0; }, 3000);
}

async function refreshAndRetry(callback) {
    try {
        const res = await fetch(`${API_BASE}/session`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${refreshToken}`
            },
        });

        const data = await res.json();

        if (!data.ok && data.err === "ERR_INVALID_REFRESH_TOKEN") {
            localStorage.removeItem('access_token')
            localStorage.removeItem('refresh_token')
            window.location.href = '/login';
        } else {
            accessToken = data.data.access_token;
            localStorage.setItem('access_token', data.data.access_token);

            if (typeof callback === 'function') {
                callback();
            }
        }
    } catch (err) {
        showToast('Failed to refresh session', 'red');
        console.error(err);
    }
}

async function loadReminders() {
    try {
        const res = await fetch(`${API_BASE}/reminders?limit=5`, {
            headers: { 'Authorization': `Bearer ${accessToken}` }
        });
        const data = await res.json();

        if (!data.ok && data.err === "ERR_INVALID_ACCESS_TOKEN") {
            return refreshAndRetry(() => loadReminders());
        }

        reminders = data.data || data;
        render();
    } catch (err) {
        showToast('Failed to load reminders', 'red');
        console.error(err);
    }
}

async function storeReminder(payload) {
    try {
        const res = await fetch(`${API_BASE}/reminders`, {
            method: 'POST',
            headers: { 
                'Content-Type':'application/json', 
                'Authorization': `Bearer ${accessToken}` 
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (!data.ok) {
            if (data.err === "ERR_INVALID_ACCESS_TOKEN") {
                return refreshAndRetry(() => storeReminder(payload));
            }

            if (data.err === "ERR_BAD_REQUEST") {
                if (data.msg?.errors) {
                    formErrors.innerHTML = '';
                    for (const field in data.msg.errors) {
                        data.msg.errors[field].forEach(msg => {
                            const p = document.createElement('p');
                            p.textContent = `${field}: ${msg}`;
                            formErrors.appendChild(p);
                        });
                    }
                    formErrors.classList.remove('hidden');
                } else {
                    formErrors.textContent = data.msg?.message || 'Something went wrong';
                    formErrors.classList.remove('hidden');
                }
            }
            return;
        }

        // Success
        formErrors.classList.add('hidden');
        closeModal();
        showToast('Reminder created!');
        await loadReminders();
    } catch (err) {
        showToast('Failed to save reminder', 'red');
        console.error(err);
    }
}

async function updateReminder(id, payload) {
    try {
        const res = await fetch(`${API_BASE}/reminders/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type':'application/json', 'Authorization': `Bearer ${accessToken}` },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (!data.ok && data.err === "ERR_INVALID_ACCESS_TOKEN") {
            return refreshAndRetry(() => updateReminder(id, payload));
        }

        closeModal();
        showToast('Reminder saved!');
        await loadReminders();
    } catch (err) {
        showToast('Failed to load reminders', 'red');
        console.error(err);
    }
}

function formatDate(ts) {
    const date = new Date(Number(ts) * 1000); // timestamp in seconds
    const dd = String(date.getDate()).padStart(2, '0');
    const mm = String(date.getMonth() + 1).padStart(2, '0'); // months are 0-based
    const yy = String(date.getFullYear()).slice(-2);
    const hh = String(date.getHours()).padStart(2, '0'); // 24-hour
    const min = String(date.getMinutes()).padStart(2, '0');
    return `${dd}/${mm}/${yy} ${hh}:${min}`;
}

function formatForDateTimeLocal(ts) {
    const d = new Date(ts * 1000);
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    return d.toISOString().slice(0,16);
}

function render() {
    const list = document.getElementById('reminderList');
    list.innerHTML = '';

    reminders.forEach((r, i) => {
        const li = document.createElement('li');
        li.className = "p-5 bg-white rounded-2xl shadow-md hover:shadow-xl transition-shadow flex justify-between items-center group";

        li.innerHTML = `
            <div class="flex flex-col space-y-1">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-lg text-gray-800">${r.title}</h3>
                </div>
                <p class="text-gray-600">${r.description}</p>
                <p class="text-gray-500 text-sm mt-1">
                    ⏰ Remind: ${formatDate(r.remind_at)}
                </p>
                <p class="text-gray-500 text-sm mt-1">
                    ⌚ Event: ${formatDate(r.event_at)}
                </p>
            </div>
            <div class="flex gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                <button onclick="editReminder(${i})" class="p-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition">✏️</button>
                <button onclick="deleteReminder(${i})" class="p-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">🗑️</button>
            </div>
        `;
        list.appendChild(li);
    });
}

function openModal(edit = false) {
  modal.classList.remove('hidden');
  modal.classList.add('modal-show');
  modalTitle.textContent = edit ? 'Edit Reminder' : 'Add Reminder';
}

function closeModal() {
  modal.classList.add('modal-hide');
  setTimeout(() => {
    modal.classList.remove('modal-show', 'modal-hide');
    modal.classList.add('hidden');
  }, 300);
  form.reset();
  idInput.value = '';
}

// Add/Edit
addBtn.addEventListener('click', () => openModal(false));
cancelBtn.addEventListener('click', closeModal);

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const payload = {
    title: titleInput.value,
    description: descriptionInput.value,
    remind_at: Math.floor(new Date(remindInput.value).getTime() / 1000),
    event_at: Math.floor(new Date(eventInput.value).getTime() / 1000)
  };
  const id = idInput.value;
  try {
    let res;
    if (id) {
      await updateReminder(id, payload);
    } else {
      await storeReminder(payload);
    }
  } catch (err) {
    showToast('Operation failed', 'red');
    console.error(err);
  }
});

function editReminder(i) {
    const r = reminders[i];
    titleInput.value = r.title;
    descriptionInput.value = r.description;

    remindInput.value = formatForDateTimeLocal(r.remind_at);
    eventInput.value  = formatForDateTimeLocal(r.event_at);

    idInput.value = r.id;
    openModal(true);
}

async function deleteReminder(i) {
    const r = reminders[i];
    if (!confirm('Are you sure you want to delete this reminder?')) return;
    try {
        const res = await fetch(`${API_BASE}/reminders/${r.id}`, {
            method: 'DELETE',
            headers: { 'Authorization': `Bearer ${accessToken}` }
        });

        const data = await res.json();

        if (!data.ok && data.err === "ERR_INVALID_ACCESS_TOKEN") {
            return refreshAndRetry(() => deleteReminder(i));
        }

        showToast('Reminder deleted!');
        await loadReminders();
    } catch (err) {
        showToast('Delete failed', 'red');
        console.error(err);
    }
}

// Initial load
loadReminders();
</script>

</body>
</html>
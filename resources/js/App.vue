<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import StudentForm from './components/StudentForm.vue'
import StudentList from './components/StudentList.vue'

axios.defaults.baseURL = '/api'
axios.defaults.headers.common['Accept'] = 'application/json'

// Send the Bearer token automatically with every request
axios.interceptors.request.use((config) => {
  const t = localStorage.getItem('token')
  if (t) config.headers.Authorization = 'Bearer ' + t
  return config
})

const token = ref(localStorage.getItem('token'))
const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))
const isAdmin = computed(() =>
  ['admin', 'administrator'].includes(String(user.value?.role).toLowerCase())
)

const credentials = ref({ email: '', password: '' })
const students = ref([])
const selected = ref(null)
const errors = ref({})
const message = ref('')
const messageType = ref('success')
const loading = ref(false)

function notify(text, type = 'success') {
  message.value = text
  messageType.value = type
  setTimeout(() => (message.value = ''), 4000)
}

async function login() {
  loading.value = true
  try {
    const { data } = await axios.post('/login', credentials.value)
    localStorage.setItem('token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))
    token.value = data.token
    user.value = data.user
    credentials.value = { email: '', password: '' }
    notify('Login successful! Welcome.')
    await loadStudents()
  } catch (e) {
    notify('Login failed. Check your email/password.', 'error')
  } finally {
    loading.value = false
  }
}

function logout() {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  token.value = null
  user.value = null
  students.value = []
  selected.value = null
}

async function loadStudents() {
  loading.value = true
  try {
    const { data } = await axios.get('/students')
    students.value = data.data ?? data
  } catch (e) {
    if (e.response?.status === 401) {
      logout()
      notify('Session expired. Please log in again.', 'error')
    } else {
      notify('Could not load students.', 'error')
    }
  } finally {
    loading.value = false
  }
}

async function saveStudent(student) {
  errors.value = {}
  try {
    if (student.id) {
      await axios.put('/students/' + student.id, student)
      notify('Student updated successfully!')
    } else {
      await axios.post('/students', student)
      notify('Student added successfully!')
    }
    selected.value = null
    loadStudents()
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors
      notify('Please fix the validation errors.', 'error')
    } else if (e.response?.status === 403) {
      notify('Not allowed: Administrator only.', 'error')
    } else {
      notify('Something went wrong.', 'error')
    }
  }
}

async function deleteStudent(id) {
  if (!confirm('Delete this student?')) return
  try {
    await axios.delete('/students/' + id)
    notify('Student deleted successfully!')
    loadStudents()
  } catch (e) {
    if (e.response?.status === 403) notify('Not allowed: Administrator only.', 'error')
    else notify('Delete failed.', 'error')
  }
}

onMounted(() => { if (token.value) loadStudents() })
</script>

<template>
  <!-- LOGIN SCREEN -->
  <div v-if="!token" class="login-page">
    <form class="login-card" @submit.prevent="login">
      <h1>Student Management System</h1>
      <p class="subtitle">Sign in to continue</p>

      <p v-if="message" :class="['alert', messageType]">{{ message }}</p>

      <label>Email</label>
      <input v-model="credentials.email" type="email" required placeholder="Enter your email" />

      <label>Password</label>
      <input v-model="credentials.password" type="password" required placeholder="Enter your password" />

      <button type="submit" :disabled="loading">{{ loading ? 'Signing in...' : 'Login' }}</button>
    </form>
  </div>

  <!-- MAIN SCREEN -->
  <div v-else class="container">
    <header class="hero">
      <div>
        <h1>🎓 Student Management System</h1>
        <p>Laravel REST API + Vue.js 3</p>
      </div>
      <div class="hero-right">
        <strong>{{ user?.name }}</strong>
        <div class="hero-actions">
          <span class="pill">{{ isAdmin ? 'Administrator' : 'Regular User' }}</span>
          <button class="logout" @click="logout">Logout</button>
        </div>
      </div>
    </header>

    <p v-if="message" :class="['alert', messageType]">{{ message }}</p>

    <div v-if="isAdmin" class="info-box admin">
      <strong>Administrator Access</strong><br />
      You can add, edit, and delete students.
    </div>
    <div v-else class="info-box">
      <strong>Regular User Access</strong><br />
      You can view student records, but you cannot add, edit, or delete students.
    </div>

    <StudentForm v-if="isAdmin" :student="selected" :errors="errors" @save="saveStudent" @cancel="selected = null" />

    <div class="card toolbar">
      <div>
        <strong>Student Records</strong><br />
        <span class="muted">Total Students: {{ students.length }}</span>
      </div>
      <button class="refresh" :disabled="loading" @click="loadStudents">
        {{ loading ? 'Loading...' : 'Refresh' }}
      </button>
    </div>

    <StudentList :students="students" :is-admin="isAdmin" @edit="selected = $event" @delete="deleteStudent" />
  </div>
</template>

<style>
body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; color: #1f2937; }
.container { max-width: 960px; margin: 30px auto; padding: 0 16px; }

.hero { background: #1e3a8a; color: #fff; border-radius: 14px; padding: 24px 28px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
.hero h1 { margin: 0 0 8px; font-size: 20px; }
.hero p { margin: 0; font-size: 15px; }
.hero-right { text-align: right; }
.hero-actions { display: flex; align-items: center; gap: 12px; margin-top: 6px; justify-content: flex-end; }
.pill { background: #dbeafe; color: #1e3a8a; padding: 6px 14px; border-radius: 999px; font-size: 13px; }
button.logout { background: #dc2626; padding: 12px 18px; font-weight: bold; margin: 0; border-radius: 8px; }

.card { background: #fff; padding: 18px; border-radius: 10px; margin: 16px 0; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0; border-bottom-left-radius: 0; border-bottom-right-radius: 0; }
.muted { color: #6b7280; font-size: 14px; }
button.refresh { background: #0f766e; padding: 12px 20px; font-weight: bold; border-radius: 8px; margin: 0; }

.alert { padding: 14px 16px; border-radius: 6px; margin: 12px 0; }
.alert.success { background: #dcfce7; color: #166534; }
.alert.error { background: #fee2e2; color: #991b1b; }
.info-box { background: #dbeafe; color: #1e3a8a; padding: 16px 18px; border-radius: 6px; margin: 12px 0; line-height: 1.6; }
.info-box.admin { background: #ede9fe; color: #4c1d95; }

input, select { display: block; width: 100%; padding: 10px; margin: 8px 0 4px; box-sizing: border-box; border: 1px solid #d1d5db; border-radius: 6px; }
button { background: #2563eb; color: #fff; border: 0; padding: 8px 14px; border-radius: 4px; cursor: pointer; margin-right: 6px; }
button:disabled { opacity: .6; cursor: not-allowed; }
button.danger { background: #dc2626; }
button.secondary { background: #6b7280; }
.error-text { color: #dc2626; font-size: 13px; }

table { width: 100%; border-collapse: collapse; background: #fff; }
th { background: #1e3a8a; color: #fff; padding: 12px; text-align: left; }
td { padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: left; }

.login-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px; background: #eef2ff; }
.login-card { width: 100%; max-width: 380px; background: #fff; border-radius: 14px; box-shadow: 0 6px 24px rgba(0,0,0,.1); padding: 32px; }
.login-card h1 { margin: 0; text-align: center; font-size: 22px; color: #1e3a8a; }
.login-card .subtitle { text-align: center; color: #6b7280; margin: 4px 0 16px; font-size: 14px; }
.login-card label { font-size: 14px; font-weight: bold; display: block; margin-top: 10px; }
.login-card button { width: 100%; margin: 20px 0 0; padding: 12px; font-size: 15px; border-radius: 8px; }
</style>
<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import StudentForm from './components/StudentForm.vue'
import StudentList from './components/StudentList.vue'

axios.defaults.baseURL = '/api'
axios.defaults.headers.common['Accept'] = 'application/json'

const token = ref(localStorage.getItem('token'))
const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))
const isAdmin = computed(() => ['admin', 'administrator'].includes(String(user.value?.role).toLowerCase()))

const credentials = ref({ email: '', password: '' })
const students = ref([])
const selected = ref(null)
const errors = ref({})
const message = ref('')
const messageType = ref('success')
const loading = ref(false)

function setToken(t) { axios.defaults.headers.common['Authorization'] = 'Bearer ' + t }
if (token.value) setToken(token.value)

function notify(text, type = 'success') {
  message.value = text
  messageType.value = type
  setTimeout(() => (message.value = ''), 4000)
}

async function login() {
  loading.value = true
  try {
    const { data } = await axios.post('/login', credentials.value)
    token.value = data.token
    user.value = data.user
    localStorage.setItem('token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))
    setToken(data.token)
    credentials.value = { email: '', password: '' }
    notify('Logged in!')
    loadStudents()
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
  delete axios.defaults.headers.common['Authorization']
}

async function loadStudents() {
  loading.value = true
  try {
    const { data } = await axios.get('/students')
    students.value = data.data ?? data
  } catch (e) {
    if (e.response?.status === 401) logout()
    else notify('Could not load students.', 'error')
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
  <!-- Login screen -->
  <div v-if="!token" class="login-page">
    <form class="login-card" @submit.prevent="login">
      <h1>Student Management</h1>
      <p class="subtitle">Sign in to continue</p>

      <p v-if="message" :class="['alert', messageType]">{{ message }}</p>

      <label>Email</label>
      <input v-model="credentials.email" type="email" required placeholder="you@example.com" />

      <label>Password</label>
      <input v-model="credentials.password" type="password" required placeholder="Password" />

      <button type="submit" :disabled="loading">{{ loading ? 'Signing in...' : 'Login' }}</button>
    </form>
  </div>

  <!-- Student management -->
  <div v-else class="container">
    <div class="topbar">
      <div>
        <h1>Student Management</h1>
        <span class="role-badge">{{ isAdmin ? 'Administrator' : 'Regular User' }}: {{ user?.name }}</span>
      </div>
      <button class="secondary" @click="logout">Logout</button>
    </div>

    <p v-if="message" :class="['alert', messageType]">{{ message }}</p>
    <p v-if="loading" class="loading">Loading...</p>

    <StudentForm v-if="isAdmin" :student="selected" :errors="errors" @save="saveStudent" @cancel="selected = null" />
    <p v-else class="alert info">You have view-only access. Only an Administrator can add, edit or delete students.</p>

    <StudentList :students="students" :is-admin="isAdmin" @edit="selected = $event" @delete="deleteStudent" />
  </div>
</template>

<style>
body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; }
.container { max-width: 800px; margin: 30px auto; padding: 0 16px; }
.card { background: #fff; padding: 16px; border-radius: 8px; margin: 16px 0; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
input, select { display: block; width: 100%; padding: 8px; margin: 8px 0 4px; box-sizing: border-box; }
button { background: #2563eb; color: #fff; border: 0; padding: 8px 14px; border-radius: 4px; cursor: pointer; margin-right: 6px; }
button:disabled { opacity: .6; cursor: not-allowed; }
button.danger { background: #dc2626; }
button.secondary { background: #6b7280; }
.alert { padding: 10px; border-radius: 4px; }
.alert.success { background: #dcfce7; color: #166534; }
.alert.error { background: #fee2e2; color: #991b1b; }
.alert.info { background: #dbeafe; color: #1e40af; }
.error-text { color: #dc2626; font-size: 13px; }
table { width: 100%; border-collapse: collapse; background: #fff; }
th, td { padding: 8px; border-bottom: 1px solid #e5e7eb; text-align: left; }

.login-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px; background: linear-gradient(135deg, #eff6ff, #e0e7ff); }
.login-card { width: 100%; max-width: 380px; background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.12); padding: 32px; }
.login-card h1 { margin: 0; text-align: center; font-size: 24px; color: #1f2937; }
.login-card .subtitle { text-align: center; color: #6b7280; margin: 4px 0 16px; font-size: 14px; }
.login-card label { font-size: 14px; font-weight: bold; color: #374151; display: block; margin-top: 8px; }
.login-card input { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; }
.login-card button { width: 100%; margin: 20px 0 0; padding: 11px; font-size: 16px; border-radius: 8px; }

.topbar { display: flex; justify-content: space-between; align-items: center; }
.topbar h1 { margin: 0 0 4px; }
.role-badge { display: inline-block; background: #e0e7ff; color: #3730a3; padding: 3px 10px; border-radius: 999px; font-size: 13px; }
.loading { color: #6b7280; }
</style>
<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import StudentForm from './components/StudentForm.vue'
import StudentList from './components/StudentList.vue'

axios.defaults.baseURL = '/api'
axios.defaults.headers.common['Accept'] = 'application/json'

const token = ref(localStorage.getItem('token'))
const credentials = ref({ email: '', password: '' })
const students = ref([])
const selected = ref(null)
const errors = ref({})
const message = ref('')
const messageType = ref('success')

function setToken(t) { axios.defaults.headers.common['Authorization'] = 'Bearer ' + t }
if (token.value) setToken(token.value)

function notify(text, type = 'success') {
  message.value = text
  messageType.value = type
  setTimeout(() => (message.value = ''), 4000)
}

async function login() {
  try {
    const { data } = await axios.post('/login', credentials.value)
    token.value = data.token
    localStorage.setItem('token', data.token)
    setToken(data.token)
    notify('Logged in!')
    loadStudents()
  } catch (e) {
    notify('Login failed. Check your email/password.', 'error')
  }
}

function logout() {
  localStorage.removeItem('token')
  token.value = null
  delete axios.defaults.headers.common['Authorization']
}

async function loadStudents() {
  try {
    const { data } = await axios.get('/students')
    students.value = data.data ?? data
  } catch (e) {
    notify('Could not load students.', 'error')
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
    notify('Delete failed.', 'error')
  }
}

onMounted(() => { if (token.value) loadStudents() })
</script>

<template>
  <div class="container">
    <h1>Student Management</h1>

    <p v-if="message" :class="['alert', messageType]">{{ message }}</p>

    <!-- Login screen -->
    <form v-if="!token" class="card" @submit.prevent="login">
      <h2>Login</h2>
      <input v-model="credentials.email" type="email" placeholder="Email" />
      <input v-model="credentials.password" type="password" placeholder="Password" />
      <button type="submit">Login</button>
    </form>

    <!-- Student management -->
    <div v-else>
      <button class="secondary" @click="logout">Logout</button>
      <StudentForm :student="selected" :errors="errors" @save="saveStudent" @cancel="selected = null" />
      <StudentList :students="students" @edit="selected = $event" @delete="deleteStudent" />
    </div>
  </div>
</template>

<style>
body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; }
.container { max-width: 800px; margin: 30px auto; padding: 0 16px; }
.card { background: #fff; padding: 16px; border-radius: 8px; margin: 16px 0; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
input { display: block; width: 100%; padding: 8px; margin: 8px 0 4px; box-sizing: border-box; }
button { background: #2563eb; color: #fff; border: 0; padding: 8px 14px; border-radius: 4px; cursor: pointer; margin-right: 6px; }
button.danger { background: #dc2626; }
button.secondary { background: #6b7280; }
.alert { padding: 10px; border-radius: 4px; }
.alert.success { background: #dcfce7; color: #166534; }
.alert.error { background: #fee2e2; color: #991b1b; }
.error-text { color: #dc2626; font-size: 13px; }
table { width: 100%; border-collapse: collapse; background: #fff; }
th, td { padding: 8px; border-bottom: 1px solid #e5e7eb; text-align: left; }
</style>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  student: Object,
  errors: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['save', 'cancel'])

const emptyForm = () => ({
  student_number: '',
  first_name: '',
  last_name: '',
  email: '',
  course: '',
  year_level: ''
})

const form = ref(emptyForm())

watch(() => props.student, (student) => {
  form.value = student
    ? { ...student }
    : emptyForm()
}, { immediate: true })

const submitForm = () => {
  emit('save', { ...form.value })
}
</script>

<template>
  <form class="card" @submit.prevent="submitForm">

    <h2>{{ form.id ? 'Edit Student' : 'Add Student' }}</h2>

    <label>Student Number</label>
    <input v-model="form.student_number" placeholder="Enter student number" required />
    <span v-if="errors.student_number" class="error-text">
      {{ errors.student_number[0] }}
    </span>

    <label>First Name</label>
    <input v-model="form.first_name" placeholder="Enter first name" required />
    <span v-if="errors.first_name" class="error-text">
      {{ errors.first_name[0] }}
    </span>

    <label>Last Name</label>
    <input v-model="form.last_name" placeholder="Enter last name" required />
    <span v-if="errors.last_name" class="error-text">
      {{ errors.last_name[0] }}
    </span>

    <label>Email</label>
    <input v-model="form.email" type="email" placeholder="Enter email" required />
    <span v-if="errors.email" class="error-text">
      {{ errors.email[0] }}
    </span>

    <label>Course</label>
    <input v-model="form.course" placeholder="Enter course" required />
    <span v-if="errors.course" class="error-text">
      {{ errors.course[0] }}
    </span>

    <label>Year Level</label>
    <select v-model="form.year_level" required>
      <option value="" disabled>Select Year Level</option>
      <option :value="1">1st Year</option>
      <option :value="2">2nd Year</option>
      <option :value="3">3rd Year</option>
      <option :value="4">4th Year</option>
    </select>
    <span v-if="errors.year_level" class="error-text">
      {{ errors.year_level[0] }}
    </span>

    <br /><br />

    <button type="submit">
      {{ form.id ? 'Update Student' : 'Add Student' }}
    </button>

    <button
      v-if="form.id"
      type="button"
      class="secondary"
      @click="emit('cancel')"
    >
      Cancel
    </button>

  </form>
</template>

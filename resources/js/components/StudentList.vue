
<script setup>
defineProps({
  students: {
    type: Array,
    default: () => []
  }
})

defineEmits(['edit', 'delete'])
</script>

<template>
  <div class="card">
    <h2>Students</h2>

    <p v-if="students.length === 0">No students found.</p>

    <table v-else>
      <thead>
        <tr>
          <th>ID</th>
          <th>Student Number</th>
          <th>Name</th>
          <th>Email</th>
          <th>Course</th>
          <th>Year Level</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="s in students" :key="s.id">
          <td>{{ s.id }}</td>
          <td>{{ s.student_number }}</td>
          <td>{{ s.first_name }} {{ s.last_name }}</td>
          <td>{{ s.email }}</td>
          <td>{{ s.course }}</td>
          <td>{{ s.year_level }}</td>
          <td>
            <button @click="$emit('edit', s)">Edit</button>
            <button class="danger" @click="$emit('delete', s.id)">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

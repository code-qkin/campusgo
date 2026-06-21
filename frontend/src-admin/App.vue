<script setup lang="ts">
import { ref } from 'vue';
import { AdminUser } from './types';
import AdminLogin from './components/AdminLogin.vue';
import AdminShell from './components/AdminShell.vue';

const storedAdmin = localStorage.getItem('admin_user')
const storedToken = localStorage.getItem('admin_token')

const parseAdmin = (raw: any): AdminUser => ({
  id: String(raw.id),
  fullName: raw.full_name || raw.fullName || '',
  email: raw.email,
  role: raw.role,
  campusId: raw.campus_id ? String(raw.campus_id) : (raw.campusId || null),
  campusName: raw.campusName || null,
})

const admin = ref<AdminUser | null>(
  storedAdmin && storedToken ? parseAdmin(JSON.parse(storedAdmin)) : null
)

const handleLoginSuccess = async (user: AdminUser) => {
  if (user.campusId) {
    try {
      const res = await fetch('http://127.0.0.1:8000/api/campuses')
      const campuses = await res.json()
      const campus = campuses.find((c: any) => String(c.id) === user.campusId)
      user.campusName = campus?.name || null
    } catch (e) {
      // silently fail
    }
  }
  admin.value = user
  localStorage.setItem('admin_user', JSON.stringify(user))
}

const handleLogout = () => {
  localStorage.removeItem('admin_token')
  localStorage.removeItem('admin_user')
  admin.value = null
}
</script>

<template>
  <AdminLogin v-if="!admin" @login-success="handleLoginSuccess" />
  <AdminShell v-else :admin="admin" @logout="handleLogout" />
</template>
<script setup lang="ts">
import { ref } from 'vue';
import { Driver } from './types';
import DriverLogin from './components/DriverLogin.vue';
import DriverShell from './components/DriverShell.vue';


// check if driver is already logged in
const storedDriver = localStorage.getItem('driver_user')
const storedToken = localStorage.getItem('driver_token')

const driver = ref<Driver | null>(
  storedDriver && storedToken
    ? JSON.parse(storedDriver)
    : null
)


const handleLoginSuccess = (user: Driver) => {
    driver.value = user;
};

const handleLogout = () => {
    driver.value = null;
};
</script>
<template>
    <DriverLogin v-if="!driver" @login-success="handleLoginSuccess" />
    <DriverShell v-else :driver="driver" @logout="handleLogout" />
</template>
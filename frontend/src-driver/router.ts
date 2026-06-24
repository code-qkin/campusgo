import { createRouter, createWebHashHistory } from 'vue-router'
import DriverLogin from './components/DriverLogin.vue'
import DriverSignup from './components/DriverSignup.vue'
import DriverPending from './components/DriverPending.vue'
import DriverShell from './components/DriverShell.vue'
import AvailableRides from './components/AvailableRides.vue'
import ActiveRide from './components/ActiveRide.vue'
import RideHistory from './components/RideHistory.vue'
import DriverPasswordChange from './components/DriverPasswordChange.vue'

const isAuth    = () => !!localStorage.getItem('driver_token')
const isApproved = () => localStorage.getItem('driver_status') === 'approved'

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    {
      path: '/login',
      component: DriverLogin,
      beforeEnter: () => {
        if (isAuth()) return isApproved() ? '/available' : '/pending'
      },
    },
    {
      path: '/signup',
      component: DriverSignup,
      beforeEnter: () => {
        if (isAuth()) return isApproved() ? '/available' : '/pending'
      },
    },
    {
      path: '/pending',
      component: DriverPending,
      beforeEnter: () => { if (!isAuth()) return '/login' },
    },
    {
      path: '/',
      component: DriverShell,
      beforeEnter: () => {
        if (!isAuth()) return '/login'
        if (!isApproved()) return '/pending'
      },
      children: [
        { path: '', redirect: '/available' },
        { path: 'available', component: AvailableRides },
        { path: 'active', component: ActiveRide },
        { path: 'history', component: RideHistory },
        { path: 'password', component: DriverPasswordChange },
      ],
    },
    { path: '/:pathMatch(.*)*', redirect: '/login' },
  ],
})

export default router

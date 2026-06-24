import { createRouter, createWebHashHistory } from 'vue-router'
import AdminLogin from './components/AdminLogin.vue'
import AdminShell from './components/AdminShell.vue'
import DashboardOverview from './components/DashboardOverview.vue'
import ManageStudents from './components/ManageStudents.vue'
import ManageCampuses from './components/ManageCampuses.vue'
import ManageRoutes from './components/ManageRoutes.vue'
import ManageStops from './components/ManageStops.vue'
import ManageDrivers from './components/ManageDrivers.vue'
import AdminPasswordChange from './components/AdminPasswordChange.vue'

const isAuth = () => !!localStorage.getItem('admin_token')

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    {
      path: '/login',
      component: AdminLogin,
      beforeEnter: () => { if (isAuth()) return '/dashboard' },
    },
    {
      path: '/',
      component: AdminShell,
      beforeEnter: () => { if (!isAuth()) return '/login' },
      children: [
        { path: '', redirect: '/dashboard' },
        { path: 'dashboard', component: DashboardOverview },
        { path: 'students', component: ManageStudents },
        { path: 'campuses', component: ManageCampuses },
        { path: 'routes', component: ManageRoutes },
        { path: 'stops', component: ManageStops },
        { path: 'drivers', component: ManageDrivers },
        { path: 'password', component: AdminPasswordChange },
      ],
    },
    { path: '/:pathMatch(.*)*', redirect: '/dashboard' },
  ],
})

export default router

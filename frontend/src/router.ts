import { createRouter, createWebHistory } from 'vue-router';

const isAuthenticated = () => !!localStorage.getItem('campusgo_token');

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: () => (isAuthenticated() ? '/home' : '/welcome') },
    {
      path: '/welcome',
      component: () => import('./components/WelcomeScreen.vue'),
    },
    {
      path: '/login',
      component: () => import('./components/AuthScreen.vue'),
      props: { initialMode: 'login' },
    },
    {
      path: '/signup',
      component: () => import('./components/AuthScreen.vue'),
      props: { initialMode: 'signup' },
    },
    {
      path: '/reset-password',
      component: () => import('./components/AuthScreen.vue'),
      props: { initialMode: 'reset' },
    },
    {
      path: '/auth/callback',
      component: () => import('./views/OAuthCallback.vue'),
    },
    {
      path: '/',
      component: () => import('./layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        { path: 'home',       component: () => import('./components/HomeDashboard.vue') },
        { path: 'carpool',    component: () => import('./components/CarpoolHub.vue') },
        { path: 'lost-found', component: () => import('./components/LostFoundHub.vue') },
        { path: 'history',    component: () => import('./components/HistoryHub.vue') },
        { path: 'settings',   component: () => import('./components/SettingsHub.vue') },
      ],
    },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
});

router.beforeEach((to) => {
  if (to.meta.requiresAuth && !isAuthenticated()) {
    return '/login';
  }
});

export default router;

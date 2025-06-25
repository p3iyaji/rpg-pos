import Home from '@/views/Home.vue';
import Login from '@/views/Auth/Login.vue';
import Register from '@/views/Auth/Register.vue';
import Dashboard from '@/views/Dashboard.vue'
import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import Products from '@/views/products/Products.vue';

const routes = [
    {
        path: '/',
        name: 'home',
        component: Home,
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: { requiresAuth: true },
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { requiresGuest: true },
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: { requiresGuest: true },
    },
    {
        path: '/404',
        name: '404',
        component: () => import('../views/errors/404.vue')
    },
    {
        path: '/500',
        name: '500',
        component: () => import('../views/errors/500.vue')
    },
    {
        path: '/products',
        name: 'products',
        component: Products,
        meta: { requiresAuth: true }
    }


];



const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach((to, from, next) => {
    const auth = useAuthStore();

    if (to.matched.some(record => record.meta.requiresAuth) && !auth.isAuthenticated) next({ name: "login" })
    else if (to.matched.some(record => record.meta.requiresGuest) && auth.isAuthenticated)
        next({ name: "dashboard" })
    else next()
})


export default router;
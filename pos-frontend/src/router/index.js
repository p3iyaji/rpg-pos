import Home from '@/views/Home.vue';
import Login from '@/views/Auth/Login.vue';
import Register from '@/views/Auth/Register.vue';
import Dashboard from '@/views/Dashboard.vue'
import UnitList from '@/views/units/UnitList.vue';
import AddUnit from '@/views/units/AddUnit.vue';

import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import ProductList from '@/views/products/ProductList.vue';
import UpdateUnit from '@/views/units/UpdateUnit.vue';
import CategoryList from '@/views/categories/CategoryList.vue';
import UpdateCategory from '@/views/categories/UpdateCategory.vue';
import AddCategory from '@/views/categories/AddCategory.vue';
import Addproduct from '@/views/products/Addproduct.vue';
import UpdateProduct from '@/views/products/UpdateProduct.vue';
import ForgotPassword from '@/views/Auth/ForgotPassword.vue';
import POS from '@/views/products/POS.vue';
import DiscountList from '@/views/discounts/DiscountList.vue';
import AddDiscount from '@/views/discounts/AddDiscount.vue';
import UpdateDiscount from '@/views/discounts/UpdateDiscount.vue';
import CustomerList from '@/views/customers/CustomerList.vue';
import AddCustomer from '@/views/customers/AddCustomer.vue';
import UpdateCustomer from '@/views/customers/UpdateCustomer.vue';

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
        path: '/forgot-password',
        name: 'forgot-password',
        component: ForgotPassword,
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
        component: ProductList,
        meta: { requiresAuth: true }
    },
    {
        path: '/add-product',
        name: 'add-product',
        component: Addproduct,
        meta: { requiresAuth: true }
    },
    {
        path: '/products/:id/edit',
        name: 'upddate-product',
        component: UpdateProduct
    },
    {
        path: '/units',
        name: 'units',
        component: UnitList,
        meta: { requiresAuth: true }
    },
    {
        path: '/add-unit',
        name: 'add-unit',
        component: AddUnit,
        meta: { requiresAuth: true }
    },
    {
        path: '/units/:id/edit',
        name: 'update-unit',
        component: UpdateUnit
    },
    {
        path: '/categories',
        name: 'categories',
        component: CategoryList,
        meta: { requiresAuth: true }
    },
    {
        path: '/add-category',
        name: 'add-category',
        component: AddCategory,
        meta: { requiresAuth: true }
    },
    {
        path: '/categories/:id/edit',
        name: 'update-category',
        component: UpdateCategory,
        meta: { requiresAuth: true }
    },
    {
        path: '/pos',
        name: 'pos',
        component: POS,
        meta: { requiresAuth: true },
    },
    {
        path: '/discounts',
        name: 'discounts',
        component: DiscountList,
        meta: { requiresAuth: true },
    },
    {
        path: '/add-discount',
        name: 'add-discount',
        component: AddDiscount,
        meta: { requiresAuth: true }
    },
    {
        path: '/discounts/:id/edit',
        name: 'update-discount',
        component: UpdateDiscount,
        meta: { requiresAuth: true }
    },
    {
        path: '/customers',
        name: 'customers',
        component: CustomerList,
        meta: { requiresAuth: true },
    },
    {
        path: '/add-customer',
        name: 'add-customer',
        component: AddCustomer,
        meta: { requiresAuth: true }
    },
    {
        path: '/customers/:id/edit',
        name: 'update-customer',
        component: UpdateCustomer,
        meta: { requiresAuth: true }
    },



];



const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach(async (to, from, next) => {
    const auth = useAuthStore();

    // For protected routes
    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!auth.isAuthenticated) {
            // Try to get user first (in case we have a valid session but the store was cleared)
            try {
                await auth.getUser();
                if (auth.isAuthenticated) {
                    return next();
                }
            } catch (error) {
                console.error('Auth check failed:', error);
            }
            return next({ name: 'login', query: { redirect: to.fullPath } });
        }
    }

    // For guest-only routes
    if (to.matched.some(record => record.meta.requiresGuest)) {
        if (auth.isAuthenticated) {
            return next({ name: 'dashboard' });
        }
    }

    next();
});


export default router;
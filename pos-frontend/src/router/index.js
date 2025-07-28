import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

const routes = [
    {
        path: '/',
        name: 'dashboard',
        component: () => import('@/views/Dashboard.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/Auth/Login.vue'),
        meta: { requiresGuest: true },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('@/views/Auth/Register.vue'),
        meta: { requiresGuest: true },
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: () => import('@/views/Auth/ForgotPassword.vue'),
        meta: { requiresGuest: true },
    },
    {
        path: '/404',
        name: '404',
        component: () => import('@/views/errors/404.vue')
    },
    {
        path: '/500',
        name: '500',
        component: () => import('@/views/errors/500.vue')
    },
    {
        path: '/products',
        name: 'products',
        component: () => import('@/views/products/ProductList.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/add-product',
        name: 'add-product',
        component: () => import('@/views/products/Addproduct.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/products/:id/edit',
        name: 'upddate-product',
        component: () => import('@/views/products/UpdateProduct.vue')
    },
    {
        path: '/units',
        name: 'units',
        component: () => import('@/views/units/UnitList.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/add-unit',
        name: 'add-unit',
        component: () => import('@/views/units/AddUnit.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/units/:id/edit',
        name: 'update-unit',
        component: () => import('@/views/units/UpdateUnit.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/categories',
        name: 'categories',
        component: () => import('@/views/categories/CategoryList.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/add-category',
        name: 'add-category',
        component: () => import('@/views/categories/AddCategory.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/categories/:id/edit',
        name: 'update-category',
        component: () => import('@/views/categories/UpdateCategory.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/pos',
        name: 'pos',
        component: () => import('@/views/products/POS.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/discounts',
        name: 'discounts',
        component: () => import('@/views/discounts/DiscountList.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/add-discount',
        name: 'add-discount',
        component: () => import('@/views/discounts/AddDiscount.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/discounts/:id/edit',
        name: 'update-discount',
        component: () => import('@/views/discounts/UpdateDiscount.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/customers',
        name: 'customers',
        component: () => import('@/views/customers/CustomerList.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/add-customer',
        name: 'add-customer',
        component: () => import('@/views/customers/AddCustomer.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/customers/:id/edit',
        name: 'update-customer',
        component: () => import('@/views/customers/UpdateCustomer.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/sales',
        name: 'sales',
        component: () => import('@/views/sales/OrdersList.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/orders/:id',
        name: 'order-details',
        component: () => import('@/views/sales/OrderDetails.vue'),
        meta: { requiresAuth: true },
        props: true
    },
    {
        path: '/suppliers',
        name: 'suppliers',
        component: () => import('@/views/suppliers/SupplierList.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/add-supplier',
        name: 'add-supplier',
        component: () => import('@/views/suppliers/AddSupplier.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/suppliers/:id/edit',
        name: 'update-supplier',
        component: () => import('@/views/suppliers/UpdateSupplier.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/expenses',
        name: 'expenses',
        component: () => import('@/views/expenses/ExpenseList.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/add-expense',
        name: 'add-expense',
        component: () => import('@/views/expenses/AddExpense.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/expenses/:id/edit',
        name: 'update-expense',
        component: () => import('@/views/expenses/UpdateExpense.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/profit-loss-report',
        name: 'profit-loss-report',
        component: () => import('@/views/reports/ProfitAndLoss.vue'),
        meta: { requiresAuth: true }
    },
    // {
    //     path: '/purchase-orders',
    //     name: 'purchase-orders',
    //     component: () => import('@/views/purchase-orders/PurchaseOrders.vue'),
    //     children: [
    //         {
    //             path: '',
    //             name: 'purchase-orders-list',
    //             component: () => import('@/components/purchase-orders/PurchaseOrderList.vue')
    //         },
    //         {
    //             path: 'new',
    //             name: 'purchase-order-view',
    //             component: () => import('@/components/purchase-orders/PurchaseOrderDetail.vue'),
    //             props: true
    //         },
    //         {
    //             path: ':id/edit',
    //             name: 'purchase-order-edit',
    //             component: () => import('@/components/purchase-orders/PurchaseOrderForm.vue'),
    //             props: route => ({ po: route.params.po })
    //         }
    //     ]
    // }
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
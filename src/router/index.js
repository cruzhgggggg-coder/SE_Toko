import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

import Dashboard  from '@/pages/Dashboard.vue'
import Kasir      from '@/pages/Kasir.vue'
import Inventory  from '@/pages/Inventory.vue'
import Pelanggan  from '@/pages/Pelanggan.vue'
import Laporan    from '@/pages/Laporan.vue'
import Pengaturan from '@/pages/Pengaturan.vue'
import Backup     from '@/pages/Backup.vue'

import PrintReceipt from '@/pages/PrintReceipt.vue'

import Login         from '@/pages/Login.vue'
import RoleSelection from '@/pages/RoleSelection.vue'

const routes = [
  { path: '/select-role', component: RoleSelection, name: 'RoleSelection', meta: { layout: 'blank', public: true } },
  { path: '/login',       component: Login,         name: 'Login',         meta: { layout: 'blank', public: true } },
  { path: '/',            component: Dashboard,     name: 'Dashboard',     meta: { roles: ['owner'] } },
  { path: '/kasir',       component: Kasir,         name: 'Kasir',         meta: { roles: ['owner', 'kasir'] } },
  { path: '/inventory',   component: Inventory,     name: 'Inventory',     meta: { roles: ['owner', 'admin'] } },
  { path: '/pelanggan',   component: Pelanggan,     name: 'Pelanggan',     meta: { roles: ['owner', 'kasir'] } },
  { path: '/laporan',     component: Laporan,       name: 'Laporan',       meta: { roles: ['owner'] } },
  { path: '/pengaturan',  component: Pengaturan,    name: 'Pengaturan',    meta: { roles: ['owner'] } },
  { path: '/backup',      component: Backup,        name: 'Backup',        meta: { roles: ['owner'] } },

  { path: '/print-receipt/:id', component: PrintReceipt, name: 'PrintReceipt', meta: { roles: ['owner', 'kasir', 'admin'], layout: 'blank' } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  // 1. Check if public route
  if (to.meta.public) {
    return next()
  }

  // 2. Check if authenticated
  if (!auth.isAuthenticated) {
    if (to.name !== 'RoleSelection' && to.name !== 'Login') {
      return next('/select-role')
    }
    return next()
  }

  // 3. Check if role is selected
  if (!auth.role && to.name !== 'RoleSelection') {
    return next('/select-role')
  }

  // 4. Check role permissions
  const userRole = auth.role // 'owner', 'admin', 'kasir'
  
  if (to.meta.roles && !to.meta.roles.includes(userRole)) {
    if (userRole === 'kasir') {
       return next('/kasir')
    }
    if (userRole === 'admin') {
       return next('/inventory')
    }
    return next('/')
  }

  // Prevent role-selection if already authenticated and at the right place
  if (auth.isAuthenticated && auth.role && to.name === 'RoleSelection') {
    if (auth.role === 'kasir') return next('/kasir')
    if (auth.role === 'admin') return next('/inventory')
    return next('/')
  }

  next()
})

export default router

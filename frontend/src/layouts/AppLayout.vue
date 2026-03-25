<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- PrimeVue Toast for global notifications -->
        <Toast />
        
        <!-- Offline/Sync Banners (PrimeVue Message) -->
        <div v-if="!online" class="p-0">
            <Message severity="error" :closable="false" class="m-0 rounded-none w-full flex justify-center py-2">
                You are currently offline. Some features may not work.
            </Message>
        </div>
        <div v-if="online && syncing" class="p-0">
            <Message severity="warn" :closable="false" class="m-0 rounded-none w-full flex justify-center py-2">
                Syncing offline changes...
            </Message>
        </div>

        <!-- Desktop Layout -->
        <div class="flex flex-1">
            <!-- Sidebar (PrimeVue style) -->
            <aside class="hidden lg:flex flex-col w-64 bg-white border-r border-gray-200 fixed inset-y-0 z-30">
                <div class="h-16 flex items-center px-6 border-b border-gray-100">
                    <router-link to="/" class="no-underline">
                        <span class="text-2xl font-bold tracking-tight text-primary">Vedant</span>
                        <span class="text-2xl font-bold tracking-tight text-gray-900">Billing</span>
                    </router-link>
                </div>

                <div class="flex-1 overflow-y-auto p-3">
                    <Menu :model="sidebarItems" class="border-none w-full">
                        <template #item="{ item, props }">
                            <router-link v-if="item.route" v-slot="{ href, navigate, isActive }" :to="item.route" custom>
                                <a :href="href" v-bind="props.action" @click="navigate" :class="{ 'bg-primary-50 text-primary font-bold': isActive }">
                                    <span :class="item.icon" class="mr-2" />
                                    <span class="text-sm">{{ item.label }}</span>
                                </a>
                            </router-link>
                        </template>
                    </Menu>
                </div>

                <!-- User Profile Section -->
                <div class="p-4 border-t border-gray-100">
                    <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors" @click="toggleUserMenu">
                        <Avatar :label="userInitials" size="large" shape="circle" class="bg-primary-100 text-primary font-bold" />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ authStore.user?.name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ authStore.activeBusiness?.name }}</p>
                        </div>
                        <i class="pi pi-chevron-up text-gray-400 text-xs"></i>
                    </div>
                    <TieredMenu ref="userMenu" :model="userMenuItems" :popup="true" />
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col lg:ml-64 min-w-0">
                <!-- Top Header (Mobile Only) -->
                <header class="lg:hidden h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sticky top-0 z-20">
                    <router-link to="/" class="no-underline">
                        <span class="text-xl font-bold text-primary">Vedant</span>
                        <span class="text-xl font-bold text-gray-900">Billing</span>
                    </router-link>
                    <Button icon="pi pi-bars" text severity="secondary" @click="mobileMenuOpen = true" />
                </header>

                <main class="flex-1 p-4 lg:p-8">
                    <slot></slot>
                </main>
            </div>
        </div>

        <!-- Mobile Sidebar (PrimeVue Drawer) -->
        <Drawer v-model:visible="mobileMenuOpen" header="Navigation">
            <Menu :model="sidebarItems" class="border-none w-full">
                <template #item="{ item, props }">
                    <router-link v-if="item.route" v-slot="{ href, navigate, isActive }" :to="item.route" custom>
                        <a :href="href" v-bind="props.action" @click="() => { navigate(); mobileMenuOpen = false; }" :class="{ 'bg-primary-50 text-primary font-bold': isActive }">
                            <span :class="item.icon" class="mr-2" />
                            <span>{{ item.label }}</span>
                        </a>
                    </router-link>
                </template>
            </Menu>
            
            <template #footer>
                <div class="p-4 border-t border-gray-100">
                    <Button label="Logout" icon="pi pi-sign-out" severity="danger" text class="w-full justify-start" @click="handleLogout" />
                </div>
            </template>
        </Drawer>

        <!-- Mobile Bottom Nav -->
        <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 h-16 flex items-center justify-around z-40 px-2 pb-safe">
            <Button icon="pi pi-home" text severity="secondary" @click="router.push('/')" v-tooltip.top="'Home'" />
            <Button icon="pi pi-file" text severity="secondary" @click="router.push('/invoices')" v-tooltip.top="'Invoices'" />
            <Button icon="pi pi-plus" size="large" rounded class="mb-8 shadow-lg" @click="router.push('/invoices/create')" />
            <Button icon="pi pi-box" text severity="secondary" @click="router.push('/products')" v-tooltip.top="'Products'" />
            <Button icon="pi pi-ellipsis-h" text severity="secondary" @click="mobileMenuOpen = true" v-tooltip.top="'More'" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useAppearance } from '../composables/useAppearance'

// PrimeVue Components
import Menu from 'primevue/menu'
import TieredMenu from 'primevue/tieredmenu'
import Button from 'primevue/button'
import Avatar from 'primevue/avatar'
import Drawer from 'primevue/drawer'
import Message from 'primevue/message'
import Toast from 'primevue/toast'

const router = useRouter()
const authStore = useAuthStore()
const { initAppearance } = useAppearance()

const mobileMenuOpen = ref(false)
const userMenu = ref()

const toggleUserMenu = (event: any) => {
    userMenu.value.toggle(event)
}

const sidebarItems = computed(() => [
    { label: 'Dashboard', icon: 'pi pi-th-large', route: '/' },
    { label: 'Customers', icon: 'pi pi-users', route: '/customers' },
    { label: 'Products', icon: 'pi pi-box', route: '/products' },
    { separator: true },
    { label: 'Sales', items: [
        { label: 'Invoices', icon: 'pi pi-file-edit', route: '/invoices' },
        { label: 'Estimates', icon: 'pi pi-file', route: '/quotations' },
        { label: 'Delivery Challans', icon: 'pi pi-truck', route: '/delivery-challans' },
    ]},
    { label: 'Purchases', items: [
        { label: 'Purchase Invoices', icon: 'pi pi-shopping-cart', route: '/purchases' },
        { label: 'Vendors', icon: 'pi pi-building', route: '/vendors' },
        { label: 'Catalog Scans', icon: 'pi pi-camera', route: '/invoice-scans' },
    ]},
    { separator: true },
    { label: 'Finance', items: [
        { label: 'Cashbook', icon: 'pi pi-wallet', route: '/cashbook' },
        { label: 'Reports', icon: 'pi pi-chart-bar', route: '/reports' },
    ]},
    { label: 'Settings', items: [
        { label: 'Business Settings', icon: 'pi pi-cog', route: '/settings/business' },
        { label: 'Team Management', icon: 'pi pi-users', route: '/settings/team' },
        { label: 'Billing & Plans', icon: 'pi pi-credit-card', route: '/billing' },
    ]}
])

const userMenuItems = [
    { label: 'Switch Business', icon: 'pi pi-sync', command: () => router.push('/businesses') },
    { label: 'My Profile', icon: 'pi pi-user', command: () => router.push('/settings/profile') },
    { separator: true },
    { label: 'Logout', icon: 'pi pi-sign-out', command: () => handleLogout() }
]

// offline status
const online = ref(navigator.onLine)
const syncing = ref(false)
const updateOnlineStatus = () => { online.value = navigator.onLine }
const updateSyncStatus = (e: Event) => { syncing.value = e.type === 'sync-start' }

onMounted(() => {
    initAppearance()
    window.addEventListener('online', updateOnlineStatus)
    window.addEventListener('offline', updateOnlineStatus)
    window.addEventListener('sync-start', updateSyncStatus)
    window.addEventListener('sync-complete', updateSyncStatus)
})

onUnmounted(() => {
    window.removeEventListener('online', updateOnlineStatus)
    window.removeEventListener('offline', updateOnlineStatus)
    window.removeEventListener('sync-start', updateSyncStatus)
    window.removeEventListener('sync-complete', updateSyncStatus)
})

const userInitials = computed(() => {
    const name = authStore.user?.name || 'U'
    return name.split(' ').map((n: string) => n[0]).join('').substring(0, 2).toUpperCase()
})

const handleLogout = () => {
    authStore.logout()
}
</script>

<style scoped>
:deep(.p-menuitem-link) {
    padding: 0.75rem 1rem;
}
:deep(.p-menu) {
    border: none;
    background: transparent;
}
</style>

import { defineStore } from 'pinia'
import client from '../api/client'


export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('user') || 'null'),
        token: localStorage.getItem('token') || null,
        isLoading: false,
        error: null as string | null,
        userBusinesses: JSON.parse(localStorage.getItem('userBusinesses') || '[]') as any[],
        activeBusiness: JSON.parse(localStorage.getItem('activeBusiness') || 'null')
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        hasSelectedBusiness: (state) => !!state.activeBusiness,
        currentBusinessId: (state) => state.activeBusiness?.id,
        currentSubscription: (state) => {
            if (!state.activeBusiness?.subscriptions?.length) return null;
            return state.activeBusiness.subscriptions[0];
        },
        hasFeature: (state) => (featureSlug: string) => {
            const subscription = state.activeBusiness?.subscriptions?.[0];
            if (!subscription?.plan?.features) return false;

            const feature = subscription.plan.features.find((f: any) => f.slug === featureSlug);
            if (!feature) return false;

            if (feature.type === 'boolean') {
                return Number(feature.pivot.limit) === 1;
            }

            return true;
        }
    },

    actions: {
        async login(credentials: any) {
            // Cannot login if offline
            if (!navigator.onLine) {
                this.error = 'You must be online to log in.'
                return false
            }

            this.isLoading = true
            this.error = null
            try {
                const response = await client.post('/login', credentials)
                this.token = response.data.token
                this.user = response.data.user

                localStorage.setItem('token', this.token!)
                localStorage.setItem('user', JSON.stringify(this.user))

                // Fetch businesses immediately after login
                await this.fetchBusinesses()

                return true
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Login failed'
                return false
            } finally {
                this.isLoading = false
            }
        },

        async fetchBusinesses() {
            try {
                const response = await client.get('/businesses')
                this.userBusinesses = response.data
                localStorage.setItem('userBusinesses', JSON.stringify(this.userBusinesses))
                return this.userBusinesses
            } catch (err) {
                if (!navigator.onLine && this.userBusinesses.length > 0) {
                    // Offline and have cached businesses, ignore error
                    return this.userBusinesses
                }
                console.error('Failed to fetch businesses', err)
                throw err
            }
        },

        setActiveBusiness(business: any) {
            this.activeBusiness = business
            localStorage.setItem('activeBusiness', JSON.stringify(business))
        },

        logout() {
            this.token = null
            this.user = null
            this.userBusinesses = []
            this.activeBusiness = null
            this.error = null

            localStorage.removeItem('token')
            localStorage.removeItem('user')
            localStorage.removeItem('activeBusiness')
            localStorage.removeItem('userBusinesses')

            window.location.href = '/login'
        }
    }
})

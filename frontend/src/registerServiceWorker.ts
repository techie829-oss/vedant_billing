import { registerSW } from 'virtual:pwa-register'

const intervalMS = 5 * 60 * 1000 // 5 minutes

export const updateSW = registerSW({
    immediate: true,
    onRegistered(swRegistration) {
        console.log('SW Registered', swRegistration)
        if (swRegistration) {
            // Periodically check for updates
            setInterval(() => {
                console.log('Checking for SW updates...')
                swRegistration.update()
            }, intervalMS)
        }
    },
    onNeedRefresh() {
        console.log('New content available, refreshing page...')
        // Force refresh to grab the new assets
        window.location.reload()
    },
    onOfflineReady() {
        console.log('App is ready to work offline')
    },
    onRegisterError(error) {
        console.error('SW registration error', error)
    }
})

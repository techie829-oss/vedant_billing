import { registerSW } from 'virtual:pwa-register'

export const updateSW = registerSW({
    onNeedRefresh() {
        // We configured 'autoUpdate' in vite.config.ts, so this might not be needed
        // unless we change to 'prompt'. But good to have structure.
        console.log('New content available, refreshing...')
    },
    onOfflineReady() {
        console.log('App is ready to work offline')
    },
})

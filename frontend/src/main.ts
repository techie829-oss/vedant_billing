import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './style.css'
import App from './App.vue'
import router from './router'
import './registerServiceWorker'

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')

// Global "Enter to Next" Navigation
window.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
        const target = e.target as HTMLElement;
        const tagName = target.tagName.toUpperCase();

        // Only handle Input and Select elements
        // Ignore Textareas (Enter needed for new lines)
        if (tagName === 'INPUT' || tagName === 'SELECT') {
            // Ignore specialized inputs if needed (e.g. type="submit")
            if (target.getAttribute('type') === 'submit') return;

            e.preventDefault(); // Prevent form submission

            // Find all focusable elements in the document
            // Selector includes inputs, selects, textareas, and buttons that are not disabled or hidden
            const selector = 'input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex="-1"])';
            const focusable = Array.from(document.querySelectorAll(selector)) as HTMLElement[];

            // Filter out elements that are not visible (optional, but safer)
            const visibleFocusable = focusable.filter(el => {
                return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length);
            });

            const index = visibleFocusable.indexOf(target);
            if (index > -1 && index < visibleFocusable.length - 1) {
                let nextIndex = index + 1;
                let next = visibleFocusable[nextIndex];

                // Skip destructive buttons (e.g. Delete/Remove with red text)
                // This prevents accidental deletion when speed-entering data
                while (next && next.tagName === 'BUTTON' && (
                    next.className.includes('text-red-') ||
                    next.className.includes('bg-red-')
                )) {
                    nextIndex++;
                    next = visibleFocusable[nextIndex];
                }

                if (next) {
                    next.focus();

                    // If it's an input/select, select the text for quick edit
                    if (next.tagName === 'INPUT') {
                        (next as HTMLInputElement).select();
                    }
                }
            }
        }
    }
});

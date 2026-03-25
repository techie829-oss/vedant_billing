import { ref } from 'vue'

export type ThemeColor = 'indigo' | 'blue' | 'teal' | 'violet' | 'rose' | 'amber' | 'emerald' | 'slate'

export interface ThemeOption {
    id: ThemeColor
    label: string
    bg: string        // Tailwind class for swatch bg
    primaryHex: string
    primaryHoverHex: string
    lightBgHex: string
    textHex: string
    ringHex: string
}

export const THEME_OPTIONS: ThemeOption[] = [
    {
        id: 'indigo',
        label: 'Indigo',
        bg: 'bg-indigo-600',
        primaryHex: '#4f46e5',
        primaryHoverHex: '#4338ca',
        lightBgHex: '#eef2ff',
        textHex: '#4338ca',
        ringHex: '#6366f1',
    },
    {
        id: 'blue',
        label: 'Blue',
        bg: 'bg-blue-600',
        primaryHex: '#2563eb',
        primaryHoverHex: '#1d4ed8',
        lightBgHex: '#eff6ff',
        textHex: '#1d4ed8',
        ringHex: '#3b82f6',
    },
    {
        id: 'teal',
        label: 'Teal',
        bg: 'bg-teal-600',
        primaryHex: '#0d9488',
        primaryHoverHex: '#0f766e',
        lightBgHex: '#f0fdfa',
        textHex: '#0f766e',
        ringHex: '#14b8a6',
    },
    {
        id: 'violet',
        label: 'Violet',
        bg: 'bg-violet-600',
        primaryHex: '#7c3aed',
        primaryHoverHex: '#6d28d9',
        lightBgHex: '#f5f3ff',
        textHex: '#6d28d9',
        ringHex: '#8b5cf6',
    },
    {
        id: 'rose',
        label: 'Rose',
        bg: 'bg-rose-600',
        primaryHex: '#e11d48',
        primaryHoverHex: '#be123c',
        lightBgHex: '#fff1f2',
        textHex: '#be123c',
        ringHex: '#f43f5e',
    },
    {
        id: 'amber',
        label: 'Amber',
        bg: 'bg-amber-500',
        primaryHex: '#f59e0b',
        primaryHoverHex: '#d97706',
        lightBgHex: '#fffbeb',
        textHex: '#b45309',
        ringHex: '#f59e0b',
    },
    {
        id: 'emerald',
        label: 'Emerald',
        bg: 'bg-emerald-600',
        primaryHex: '#059669',
        primaryHoverHex: '#047857',
        lightBgHex: '#ecfdf5',
        textHex: '#047857',
        ringHex: '#10b981',
    },
    {
        id: 'slate',
        label: 'Slate',
        bg: 'bg-slate-700',
        primaryHex: '#334155',
        primaryHoverHex: '#1e293b',
        lightBgHex: '#f1f5f9',
        textHex: '#1e293b',
        ringHex: '#475569',
    },
]

export const SCALE_OPTIONS = [75, 85, 100, 110, 125] as const
export type ScaleValue = typeof SCALE_OPTIONS[number]

const STORAGE_KEY_SCALE = 'vb_ui_scale'
const STORAGE_KEY_THEME = 'vb_ui_theme'

// Reactive state (shared across instances)
const uiScale = ref<number>(parseInt(localStorage.getItem(STORAGE_KEY_SCALE) ?? '100', 10))
const themeColor = ref<ThemeColor>((localStorage.getItem(STORAGE_KEY_THEME) as ThemeColor) ?? 'indigo')

function applyScale(scale: number) {
    document.documentElement.style.fontSize = `${scale}%`
}

function applyTheme(colorId: ThemeColor) {
    const theme = THEME_OPTIONS.find(t => t.id === colorId)
    if (!theme) return
    const root = document.documentElement
    root.style.setProperty('--color-primary', theme.primaryHex)
    root.style.setProperty('--color-primary-hover', theme.primaryHoverHex)
    root.style.setProperty('--color-primary-light', theme.lightBgHex)
    root.style.setProperty('--color-primary-text', theme.textHex)
    root.style.setProperty('--color-primary-ring', theme.ringHex)
}

function setScale(scale: number) {
    uiScale.value = scale
    localStorage.setItem(STORAGE_KEY_SCALE, String(scale))
    applyScale(scale)
}

function setTheme(colorId: ThemeColor) {
    themeColor.value = colorId
    localStorage.setItem(STORAGE_KEY_THEME, colorId)
    applyTheme(colorId)
}

function resetAppearance() {
    setScale(100)
    setTheme('indigo')
}

/** Call this once on app mount to apply stored preferences */
function initAppearance() {
    applyScale(uiScale.value)
    applyTheme(themeColor.value)
}

export function useAppearance() {
    return {
        uiScale,
        themeColor,
        SCALE_OPTIONS,
        THEME_OPTIONS,
        setScale,
        setTheme,
        resetAppearance,
        initAppearance,
    }
}

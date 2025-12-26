<template>
    <div class="relative" ref="container">
        <input type="text" v-model="displayValue" @input="onInput" @focus="openDropdown"
            @keydown.down.prevent="highlightNext" @keydown.up.prevent="highlightPrev"
            @keydown.enter.prevent="selectHighlighted" @keydown.tab="selectHighlightedOrClose"
            class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-xs sm:text-sm sm:leading-6"
            placeholder="Select or enter product" />
        <div v-if="isOpen && filteredItems.length > 0"
            class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
            <div v-for="(item, index) in filteredItems" :key="item.id" @mousedown.prevent="selectItem(item)"
                class="relative cursor-pointer select-none py-2 pl-3 pr-9"
                :class="{ 'bg-indigo-600 text-white': index === highlightedIndex, 'text-gray-900': index !== highlightedIndex }"
                @mouseenter="highlightedIndex = index">
                <div class="flex justify-between">
                    <span class="block truncate" :class="{ 'font-semibold': isSelected(item) }">{{ item.name }}</span>
                    <span class="opacity-75 text-xs self-center">₹{{ item.sale_price }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps<{
    items: any[]
    modelValue: string | number | null
    initialDisplay?: string
}>()

const emit = defineEmits(['update:modelValue', 'change', 'select'])

const displayValue = ref('')
const isOpen = ref(false)
const highlightedIndex = ref(0)
const container = ref<HTMLElement | null>(null)

// Initialize display value including watching for external updates
// If we receive a modelValue (ID) but no initialDisplay, we try to find the name in items.
// Update display value when modelValue changes or items load
watch([() => props.modelValue, () => props.items], ([newVal, newItems]) => {
    if (newVal) {
        const item = newItems.find(i => i.id === newVal)
        if (item && displayValue.value !== item.name) {
            displayValue.value = item.name
        }
    }
}, { immediate: true })

watch(() => props.initialDisplay, (val) => {
    if (val && !props.modelValue) {
        displayValue.value = val
    }
}, { immediate: true })

const filteredItems = computed(() => {
    if (!displayValue.value) return props.items
    const lower = displayValue.value.toLowerCase()
    return props.items.filter(i =>
        i.name.toLowerCase().includes(lower)
    )
})

const onInput = () => {
    isOpen.value = true
    highlightedIndex.value = 0
    // When user types, we reset the ID (standard behavior being "Custom" until selected)
    emit('update:modelValue', null)
    emit('change', displayValue.value)
}

const openDropdown = () => {
    isOpen.value = true
    highlightedIndex.value = 0
}

const selectItem = (item: any) => {
    displayValue.value = item.name
    emit('update:modelValue', item.id)
    emit('select', item)
    isOpen.value = false
}

const highlightNext = () => {
    if (!isOpen.value) {
        isOpen.value = true
        return
    }
    if (highlightedIndex.value < filteredItems.value.length - 1) {
        highlightedIndex.value++
    }
}

const highlightPrev = () => {
    if (highlightedIndex.value > 0) {
        highlightedIndex.value--
    }
}

const selectHighlighted = () => {
    if (isOpen.value && filteredItems.value.length > 0) {
        selectItem(filteredItems.value[highlightedIndex.value])
    } else {
        // Treat as custom entry (Already handled by onInput, just close)
        isOpen.value = false
    }
}

const selectHighlightedOrClose = () => {
    if (isOpen.value && filteredItems.value.length > 0) {
        // Optional: Select matching item on tab?
        // Let's just close to behave like standard input unless they used arrows
        isOpen.value = false
    } else {
        isOpen.value = false
    }
}

const isSelected = (item: any) => {
    return item.id === props.modelValue
}

// Click outside to close
const handleClickOutside = (event: MouseEvent) => {
    if (container.value && !container.value.contains(event.target as Node)) {
        isOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

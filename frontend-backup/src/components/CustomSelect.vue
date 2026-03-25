<template>
    <div class="relative" ref="container">
        <label v-if="label" :for="id" class="block text-sm font-medium leading-6 text-gray-900 mb-2">
            {{ label }}
        </label>

        <button type="button" :id="id" @click="isOpen = !isOpen"
            class="relative w-full cursor-default rounded-md bg-white py-1.5 pl-3 pr-10 text-left text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
            :class="{ 'ring-2 ring-indigo-600': isOpen }">
            <span class="block truncate">{{ selectedLabel }}</span>
            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </span>
        </button>

        <transition enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95">
            <div v-if="isOpen"
                class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                <!-- Search Input -->
                <div v-if="searchable" class="sticky top-0 z-10 bg-white px-2 py-2">
                    <input type="text" v-model="searchQuery" ref="searchInput"
                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        placeholder="Search..." @click.stop>
                </div>

                <!-- Linear options or Groups -->
                <template v-for="(groupOrOption, index) in filteredOptions" :key="index">
                    <!-- Group -->
                    <div v-if="isGroup(groupOrOption)">
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                            {{ groupOrOption.label }}
                        </div>
                        <div v-for="option in groupOrOption.options" :key="option.value" @click="select(option)"
                            class="relative cursor-default select-none py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white group"
                            :class="{ 'bg-indigo-600 text-white': modelValue === option.value, 'text-gray-900': modelValue !== option.value }">
                            <div class="flex flex-col">
                                <span class="font-normal block truncate"
                                    :class="{ 'font-semibold': modelValue === option.value }">
                                    {{ option.label }}
                                </span>
                                <!-- Optional description in dropdown -->
                                <span v-if="option.description"
                                    class="text-xs text-gray-500 group-hover:text-indigo-200 mt-0.5"
                                    :class="{ 'text-indigo-200': modelValue === option.value }">
                                    {{ option.description }}
                                </span>
                            </div>

                            <span v-if="modelValue === option.value"
                                class="absolute inset-y-0 right-0 flex items-center pr-4"
                                :class="{ 'text-white': modelValue === option.value, 'text-indigo-600': modelValue !== option.value }">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>
                        </div>
                    </div>

                    <!-- Single Option (if not grouped) -->
                    <div v-else @click="select(groupOrOption)"
                        class="relative cursor-default select-none py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white group"
                        :class="{ 'bg-indigo-600 text-white': modelValue === groupOrOption.value, 'text-gray-900': modelValue !== groupOrOption.value }">
                        <div class="flex flex-col">
                            <span class="font-normal block truncate"
                                :class="{ 'font-semibold': modelValue === groupOrOption.value }">
                                {{ groupOrOption.label }}
                            </span>
                            <span v-if="groupOrOption.description"
                                class="text-xs text-gray-500 group-hover:text-indigo-200 mt-0.5"
                                :class="{ 'text-indigo-200': modelValue === groupOrOption.value }">
                                {{ groupOrOption.description }}
                            </span>
                        </div>

                        <span v-if="modelValue === groupOrOption.value"
                            class="absolute inset-y-0 right-0 flex items-center pr-4"
                            :class="{ 'text-white': modelValue === groupOrOption.value, 'text-indigo-600': modelValue !== groupOrOption.value }">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </span>
                    </div>
                </template>

                <div v-if="filteredOptions.length === 0" class="px-3 py-4 text-center text-sm text-gray-500">
                    No results found
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'

interface Option {
    label: string
    value: string
    description?: string
}

interface Group {
    label: string
    options: Option[]
}

const props = defineProps<{
    modelValue: string
    options: (Option | Group)[]
    label?: string
    id?: string
    searchable?: boolean
}>()

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const container = ref<HTMLElement | null>(null)
const searchQuery = ref('')
const searchInput = ref<HTMLInputElement | null>(null)

// Helper to check type
const isGroup = (item: Option | Group): item is Group => {
    return 'options' in item
}

// Flatten options to find selected label easily
const flattenedOptions = computed(() => {
    const flat: Option[] = []
    props.options.forEach(item => {
        if (isGroup(item)) {
            flat.push(...item.options)
        } else {
            flat.push(item)
        }
    })
    return flat
})

const filteredOptions = computed(() => {
    if (!props.searchable || !searchQuery.value) return props.options

    const query = searchQuery.value.toLowerCase()

    // Filter logic handling groups
    return props.options.reduce((acc: (Option | Group)[], item) => {
        if (isGroup(item)) {
            const filteredGroupOptions = item.options.filter(opt =>
                opt.label.toLowerCase().includes(query) ||
                (opt.description && opt.description.toLowerCase().includes(query))
            )
            if (filteredGroupOptions.length > 0) {
                acc.push({ ...item, options: filteredGroupOptions })
            }
        } else {
            if (item.label.toLowerCase().includes(query) || (item.description && item.description.toLowerCase().includes(query))) {
                acc.push(item)
            }
        }
        return acc
    }, [])
})

const selectedLabel = computed(() => {
    const found = flattenedOptions.value.find(o => o.value === props.modelValue)
    return found ? found.label : 'Select...'
})

const select = (option: Option) => {
    emit('update:modelValue', option.value)
    isOpen.value = false
    searchQuery.value = ''
}

// Click outside to close
const handleClickOutside = (event: MouseEvent) => {
    if (container.value && !container.value.contains(event.target as Node)) {
        isOpen.value = false
        searchQuery.value = '' // Clear search when closing
    }
}

watch(isOpen, (val) => {
    if (val && props.searchable) {
        nextTick(() => {
            searchInput.value?.focus()
        })
    } else if (!val) {
        searchQuery.value = '' // Clear search when dropdown closes
    }
})

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

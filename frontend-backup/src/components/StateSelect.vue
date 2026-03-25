<template>
    <div class="relative">
        <select :value="modelValue" @change="updateValue" :disabled="loadingStates" :required="required"
            class="block w-full appearance-none rounded-md border-0 py-2 pl-3.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
            :class="inputClass">
            <option value="" disabled>Select State</option>
            <option v-for="state in states" :key="state.code" :value="state.name">
                {{ state.name }} ({{ state.code }})
            </option>
        </select>
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
            <svg v-if="loadingStates" class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <svg v-else class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                    clip-rule="evenodd" />
            </svg>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useGeneralStore } from '../stores/general'
import { storeToRefs } from 'pinia'

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    required: {
        type: Boolean,
        default: false
    },
    inputClass: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['update:modelValue'])

const generalStore = useGeneralStore()
const { states, loadingStates } = storeToRefs(generalStore)

onMounted(() => {
    generalStore.fetchStates()
})

const updateValue = (event: Event) => {
    const target = event.target as HTMLSelectElement
    emit('update:modelValue', target.value)
}
</script>

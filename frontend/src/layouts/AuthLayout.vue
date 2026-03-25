<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center items-center p-4 relative overflow-hidden">
    <!-- Abstract Background (PrimeVue style) -->
    <div class="absolute inset-0 z-0 pointer-events-none">
        <div class="absolute top-[-10%] right-[-10%] w-[50%] h-[50%] bg-primary-100/30 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[50%] h-[50%] bg-primary-200/20 rounded-full blur-[120px]"></div>
    </div>

    <div class="w-full max-w-md z-10">
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center p-3 bg-white rounded-2xl shadow-sm border border-gray-100 mb-4">
            <span class="text-3xl font-black text-primary tracking-tighter">Vedant</span>
            <span class="text-3xl font-bold text-gray-900 tracking-tighter">Billing</span>
        </div>
        <h2 class="text-xl font-bold text-gray-600 uppercase tracking-widest text-[10px]">
          <slot name="title">Authentication</slot>
        </h2>
      </div>

      <Card class="border-none shadow-xl overflow-hidden bg-white/80 backdrop-blur-md">
        <template #content>
            <div class="px-2 py-4">
                <slot></slot>
            </div>
        </template>
      </Card>

      <div class="mt-8">
        <slot name="footer"></slot>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import Card from 'primevue/card'
</script>

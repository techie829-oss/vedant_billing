<template>
  <div class="min-h-screen bg-[#f3f4f6] flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Background Decor -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
       <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] rounded-full bg-blue-400 opacity-20 blur-[100px]"></div>
       <div class="absolute top-[20%] -right-[5%] w-[30%] h-[30%] rounded-full bg-indigo-500 opacity-20 blur-[100px]"></div>
       <div class="absolute -bottom-[10%] left-[20%] w-[35%] h-[35%] rounded-full bg-purple-500 opacity-20 blur-[100px]"></div>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
      <div class="text-center mb-6">
        <h2 class="text-center text-3xl font-extrabold text-gray-900 tracking-tight">
          <span class="text-indigo-600">Billing</span>Book
        </h2>
        <h2 class="mt-2 text-center text-xl font-medium text-gray-600">
          <slot name="title">Sign in to your account</slot>
        </h2>
      </div>

      <div class="bg-white/80 backdrop-blur-xl py-8 px-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-2xl sm:px-10 border border-white/50">
        <slot></slot>
      </div>
      
      <div class="mt-6">
        <slot name="footer"></slot>
      </div>
    </div>
  </div>
</template>

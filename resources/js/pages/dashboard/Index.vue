<template>
  <Head title="Dashboard" />

  <AppLayout>
    <div
      class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6 bg-gray-100 min-h-screen"
    >
      <!-- Header Section -->
      <div class="grid auto-rows-min gap-6 md:grid-cols-3">
        <div class="col-span-full">
          <h1 class="text-3xl font-bold text-gray-800 mb-2">Welcome to Your Dashboard</h1>
          <p class="text-gray-600">
            Explore your upcoming courses and start learning today!
          </p>
        </div>
      </div>

      <!-- Course Card Section -->
      <div class="about-container max-w-4xl mx-auto">
        <div
          class="course-card bg-white rounded-xl shadow-lg p-6 transform transition duration-300 hover:scale-105 hover:shadow-xl"
        >
          <h2 class="text-2xl font-semibold text-indigo-600 mb-3">
            หลักสูตร "สร้าง AI Chat Bot, AI Agent ด้วย Laravel 12.x และ Prism"
          </h2>
          <div class="flex items-center gap-2 text-gray-600 mb-2">
            <svg
              class="w-5 h-5 text-indigo-500"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
              ></path>
            </svg>
            <p class="date-time">วันที่ 30-31 ส.ค. 2568 เวลา 09.00-17.00 น. (2 วัน)</p>
          </div>
          <div class="flex items-center gap-2 text-gray-600">
            <svg
              class="w-5 h-5 text-indigo-500"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
              ></path>
            </svg>
            <p class="duration">รวม 14 ชั่วโมง</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";
</script>

<style scoped>
.course-card {
  animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
  0% {
    opacity: 0;
    transform: translateY(10px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

<template>
  <Head title="About" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="about-container">
      <div class="course-card">
        <h1>หลักสูตร "สร้าง AI Chat Bot, AI Agent ด้วย Laravel 12.x และ Prism"</h1>
        <p class="date-time">วันที่ 30-31 ส.ค. 2568 เวลา 09.00-17.00 น. (2 วัน)</p>
        <p class="duration">( รวม 14 ชั่วโมง )</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { dashboard, home, test } from "@/routes";
import { type BreadcrumbItem } from "@/types";
import { Head } from "@inertiajs/vue3";
// No script logic is needed for this static page.

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Home',
        href: home().url,
    },
    {
        title: 'Test',
        href: test().url,
    },
];
</script>

<style scoped>
.about-container {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 2rem;
  min-height: 80vh;
  text-align: center;
}
.course-card {
  max-width: 600px;
}
h1 {
  font-size: 1.75rem;
  margin-bottom: 1rem;
}
.date-time {
  font-size: 1.1rem;
  color: #555;
}
.duration {
  font-size: 1rem;
  color: #777;
  margin-top: 0.5rem;
}
</style>

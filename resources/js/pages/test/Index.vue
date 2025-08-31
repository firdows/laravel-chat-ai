<template>
    <Head title="Test" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">Test</div>
            <div>
                {{ text }}
            </div>
            <div class="flex space-x-1">
                <input type="text" v-model="inputText" class="border p-3" />
                <button v-on:click="btnSubmit" class="rounded-sm border bg-green-300 p-2">Submit</button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { home, test } from '@/routes';
import { store } from '@/routes/test';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

defineProps({
    text: String,
});
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
const inputText = ref('');

const btnSubmit = () => {
    // let res = router.post(store(), { title: inputText.value });
    // console.log();
    axios.post(store(), { text: inputText.value }).then(async (response) => {
        // companyList.value = await response.data?.company;
        console.log(response);
    });
};
</script>

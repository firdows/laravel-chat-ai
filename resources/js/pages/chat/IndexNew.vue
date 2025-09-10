<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { home } from '@/routes';
import { message as chatMessage, index } from '@/routes/chat';
import { type BreadcrumbItem } from '@/types';
import { Chat } from '@ai-sdk/vue';
import { Head, usePage } from '@inertiajs/vue3';
import type { UIMessage } from 'ai';
import { createIdGenerator, TextStreamChatTransport } from 'ai';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    historyAll: {
        type: Object,
        default: [],
    },
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Home',
        href: home().url,
    },
    {
        title: 'Chat AI',
        href: index().url,
    },
];
const page = usePage();
const user = computed(() => page.props.auth.user);
const csrfToken = computed(() => page.props.csrf);

const messages = ref<UIMessage[]>([]);

onMounted(() => {
    props.historyAll?.forEach((his) => {
        // console.log(his);
        messageList.value.push({ id: his.id, role: his.role, parts: [{ type: 'text', text: his.parts.text }], created_at: his.created_at });
    });
});

const chat = new Chat({
    transport: new TextStreamChatTransport({
        api: chatMessage().url,
        fetch: (url, options) => {
            return fetch(url, {
                ...options,
                headers: {
                    ...options?.headers,
                    'X-CSRF-TOKEN': String(csrfToken.value ?? ''),
                },
            });
        },
        // headers: {
        //     'X-CSRF-TOKEN': String(csrfToken.value ?? ''),
        // }
    }),
    generateId: createIdGenerator({ prefix: 'msgc', size: 16 }),
    messages: messages.value,
});

const messageList = computed(() => chat.messages); // computed property for type inference
const input = ref('');
const isSubmitting = computed(() => chat.status === 'submitted' || chat.status === 'streaming');
const chatError = computed(() => chat.error?.message);

const handleSubmit = (e: Event) => {
    e.preventDefault();
    console.log(input.value);
    chat.sendMessage({ text: input.value });
    input.value = '';
};

function stop() {
    chat.stop();
}

function formatThaiDate(dateString: string | Date): string {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${day}/${month}/${year} ${hours}:${minutes}`;
}
</script>

<template>
    <Head title="Chat AI" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-2 flex h-[90vh] flex-col overflow-hidden rounded-xl border bg-gray-100">
            <header class="flex items-center justify-between border-b bg-blue-500 px-4 py-3">
                <h1 class="px-4 text-lg font-bold text-white">My AI Chat</h1>
                <span class="text-white">สวัสดีคุณ {{ user.name }} ID: {{ user.id }}</span>
            </header>

            <div class="flex-1 overflow-y-auto p-4">
                <div
                    v-for="message in messageList"
                    :key="message.id"
                    :class="['my-2 flex gap-3', message.role === 'user' ? 'flex-row-reverse' : 'flex-row']"
                >
                    <div class="flex flex-col">
                        <strong :class="['', message.role === 'user' ? 'mr-2 text-right' : 'ml-2']">{{ `${message.role} ` }}</strong>
                        <div :class="['mt-1 rounded-2xl px-4 py-2.5 whitespace-pre-wrap', message.role === 'user' ? 'bg-gray-200' : 'bg-yellow-200']">
                            {{ message.parts.map((part) => (part.type === 'text' ? part.text : '')).join('') }}
                        </div>
                        <span :class="['mt-1 text-xs text-gray-500', message.role === 'user' ? 'mr-2 text-right' : 'ml-2']">
                            {{ formatThaiDate(message.created_at) }}
                        </span>
                    </div>
                </div>

                <div v-if="isSubmitting" class="text-grey-500 flex items-center gap-2 text-3xl font-bold">
                    <span class="flex gap-0.5">
                        <span class="animate-bounce">.</span>
                        <span class="animate-bounce" style="animation-delay: 0.2s">.</span>
                        <span class="animate-bounce" style="animation-delay: 0.4s">.</span>
                    </span>
                </div>

                <div v-if="chatError" class="mt-4 text-red-400">
                    {{ chatError }}
                </div>
            </div>

            <div class="border-t bg-blue-500 p-4">
                <form @submit="handleSubmit" class="flex items-center gap-2">
                    <input class="flex-1 rounded-xl bg-white px-4 py-2" v-model="input" placeholder="Say something..." :disabled="isSubmitting" />

                    <button v-if="isSubmitting" type="button" @click="stop" class="border border-gray-700">Stop</button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

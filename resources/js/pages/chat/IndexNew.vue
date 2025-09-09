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
    historyAll: Object,
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

const messages = ref<UIMessage[]>([
    { id: 'message-0', role: 'user', parts: [{ type: 'text', text: 'Greetings.' }] },
    { id: 'message-1', role: 'assistant', parts: [{ type: 'text', text: 'Hello.' }] },
]);

const chat = new Chat({
    generateId: createIdGenerator({ prefix: 'msgc', size: 16 }),
    messages: messages[messages.length - 1],
    transport: new TextStreamChatTransport({
        api: chatMessage().url,
        headers: {
            'X-CSRF-TOKEN': String(csrfToken.value ?? ''),
        },
    }),
});

const messageList = computed(() => chat.messages); // computed property for type inference
onMounted(() => {
    props.historyAll.forEach((his) => {
        // console.log(his);
        messageList.value.push({ id: his.id, role: his.role, parts: [{ type: 'text', text: his.parts.text }] });
    });
});

const input = ref('');

const handleSubmit = (e: Event) => {
    e.preventDefault();
    chat.sendMessage({ text: input.value });
    input.value = '';
};
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
                <div v-for="message in messageList" :key="message.id">
                    <div :class="['my-2 flex gap-3', message.role === 'user' ? 'flex-row-reverse' : 'flex-row']">
                        <div class="flex flex-col">
                            <strong :class="['p-2', message.role === 'user' ? 'text-right' : '']">{{ `${message.role} ` }}</strong>
                            <div :class="['rounded-2xl px-4 py-2.5 whitespace-pre-wrap', message.role === 'user' ? 'bg-gray-200' : 'bg-yellow-200']">
                                {{ message.parts.map((part) => (part.type === 'text' ? part.text : '')).join('') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t p-4">
                <form @submit="handleSubmit" class="flex items-center gap-2">
                    <input class="flex-1 rounded-xl bg-white px-4 py-2" v-model="input" placeholder="Say something..." />
                </form>
            </div>
        </div>
    </AppLayout>
</template>

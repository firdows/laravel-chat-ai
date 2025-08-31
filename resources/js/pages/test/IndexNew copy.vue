<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { home, test } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Chat } from '@ai-sdk/vue';
import { usePage } from '@inertiajs/vue3';
import type { UIMessage } from 'ai';
import { createIdGenerator, DefaultChatTransport } from 'ai';
import { computed, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Home',
        href: home().url,
    },
    {
        title: 'Chat AI',
        href: test().url,
    },
];
const page = usePage();
const user = computed(() => page.props.auth.user);

const messages = ref<UIMessage[]>([
    { id: 'message-0', role: 'user', parts: [{ type: 'text', text: 'Greetings.' }] },
    { id: 'message-1', role: 'assistant', parts: [{ type: 'text', text: 'Hello.' }] },
]);

// const chat = new Chat({
//     generateId: createIdGenerator({ prefix: 'msgc', size: 16 }),
//     messages: messages.value,
//     transport: new DefaultChatTransport({
//         api: postChat(), // เปลี่ยนตามที่คุณต้องการ
//         headers: { 'X-CSRF-TOKEN': page.props.csrf },
//     }),
// });
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const chat = new Chat({
    generateId: createIdGenerator({ prefix: 'msgc', size: 16 }),
    transport: new DefaultChatTransport({
        api: '/test/chat',
        prepareSendMessagesRequest({ messages, id }) {
            return {
                headers: {
                    'X-CSRF-TOKEN': page.props.csrf,
                    Authorization: 'Bearer ...',
                },
                body: {
                    // ส่งเฉพาะข้อความล่าสุด และ ID ตัวสนทนา
                    message: messages[messages.length - 1],
                    chatId: id,
                },
            };
        },
    }),
});

const messageList = computed(() => chat.messages); // computed property for type inference
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
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">Chat AI</div>
        </div>
        <!-- <div class="stretch mx-auto flex w-full max-w-md flex-col py-24"> -->
        <div class="m-2 flex h-[90vh] flex-col overflow-hidden rounded-xl border p-4">
            <div v-for="message in messageList" :key="message.id" class="whitespace-pre-wrap">
                <strong>{{ `${message.role}: ` }}</strong>
                {{ message.parts.map((part) => (part.type === 'text' ? part.text : '')).join('') }}
            </div>

            <form @submit="handleSubmit">
                <input
                    class="fixed bottom-0 mb-8 w-full max-w-md rounded border border-gray-300 p-2"
                    v-model="input"
                    placeholder="Say something..."
                />
            </form>
        </div>
        <!-- </div> -->
    </AppLayout>
</template>

<template>
    <Head title="Test" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-2xl p-4">
            <h1 class="mb-4 text-2xl font-bold">Todo List</h1>

            <form @submit.prevent="addTodo" class="mb-4">
                <div class="flex gap-2">
                    <input v-model="newTodo" type="text" placeholder="Add new todo" class="w-full rounded border p-2" />
                    <button type="submit" class="rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600">Add</button>
                </div>
            </form>

            <ul class="space-y-2">
                <li
                    v-for="todo in todos"
                    :key="todo.id"
                    class="flex items-center justify-between rounded border p-2"
                    :class="{ 'bg-gray-100': todo.completed }"
                >
                    <div class="flex items-center gap-2">
                        <input type="checkbox" v-model="todo.completed" @change="updateTodo(todo)" />
                        <input
                            v-if="todo.editing"
                            v-model="todo.title"
                            @blur="updateTodo(todo)"
                            @keyup.enter="updateTodo(todo)"
                            class="rounded border p-1"
                        />
                        <span v-else :class="{ 'line-through dark:text-gray-800': todo.completed }">
                            {{ todo.title }}
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <button v-if="!todo.editing" @click="todo.editing = true" class="text-blue-500 hover:text-blue-700">Edit</button>
                        <button @click="deleteTodo(todo)" class="text-red-500 hover:text-red-700">Delete</button>
                    </div>
                </li>
            </ul>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
// import { Inertia } from "@inertiajs/inertia";
import AppLayout from '@/layouts/AppLayout.vue';
import { home } from '@/routes';
import { destroy, index, store, update } from '@/routes/todos';
import { Head, router } from '@inertiajs/vue3';

const breadcrumbs = [
    {
        title: 'Home',
        href: home().url,
    },
    {
        title: 'Todo List',
        href: index().url,
    },
];

const props = defineProps({
    todos: Array,
});

const newTodo = ref('');

const addTodo = () => {
    if (newTodo.value.trim()) {
        router.post(store(), {
            title: newTodo.value,
        });
        newTodo.value = '';
    }
};

const updateTodo = (todo) => {
    router.put(update(todo.id), {
        title: todo.title,
        completed: todo.completed,
    });
    todo.editing = false;
};

const deleteTodo = (todo) => {
    router.delete(destroy(todo.id));
};

// Add editing property to todos
props.todos.forEach((todo) => {
    todo.editing = false;
});
</script>

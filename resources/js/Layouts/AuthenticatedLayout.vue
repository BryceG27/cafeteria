<script setup>
import BaseLayout from './BaseLayout.vue';

import { useAuthStore } from '@/stores/auth';
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const auth = useAuthStore();

auth.setUser(page.props.auth.user);

</script>

<template>
    <BaseLayout>
        <template #page-top-content>
            <slot></slot>
        </template>
    </BaseLayout>
</template>

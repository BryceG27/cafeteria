<script setup>
import { Head } from "@inertiajs/vue3";

const props = defineProps({
    user : Array
});
    
</script>
<template>
    <Head :title="`${user.name} ${user.surname}`" />
    <AuthenticatedLayout>

    </AuthenticatedLayout>
</template>
<style>
    
</style>
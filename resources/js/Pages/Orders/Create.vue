<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";

const props = defineProps({
    menus : Array,
    order : Object,
    auth : Object,
})


const form = useForm({
    menu_id: null,
    products : [],
    customer_id : props.auth.user.id
})

</script>
<template>
    <Head title="Nuovo ordine" />

    <AuthenticatedLayout>
        <div class="content">
            <BaseBlock title="Inserisci nuovo ordine" class="mb-4">

            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>
<style>
    
</style>
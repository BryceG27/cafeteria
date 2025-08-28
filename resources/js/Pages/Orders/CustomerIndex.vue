<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

const props = defineProps({
    orders: Array,
    auth: Object,
});
</script>

<template>
    <Head title="Ordini" />    

    <AuthenticatedLayout>
        <div class="content">
            <SuccessMessage />
            <ErrorMessage />

            <BaseBlock title="Ordini" contentClass="pb-3">
                <template #options>
                    <Link :href="route('orders.create')" class="btn btn-alt-primary btn-sm">
                        <i class="fa fa-plus me-1"></i> Nuovo ordine
                    </Link>
                </template>
                <DataTable
                    striped-rows
                    :value="orders"
                >
                    <template #empty>
                        <div class="p-4 text-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun ordine creato</p>
                        </div>
                    </template>
                </DataTable>
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>
<style>
    
</style>
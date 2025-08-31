<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

import moment from 'moment';

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

                    <Column class="text-center">
                        <template #body="{ data }">
                            <Link
                                :href="route('orders.edit', data.id)"
                                class="btn btn-alt-warning btn-sm"
                            >
                                <i class="fa fa-pencil-alt"></i>
                            </Link>
                            <Link
                                :href="route('orders.destroy', data.id)"
                                class="btn btn-alt-danger btn-sm ms-2"
                                as="button"
                                method="delete"
                            >
                                <i class="fa fa-trash"></i>
                            </Link>
                        </template>
                    </Column>
                    <Column header="Prodotti"></Column>
                    <Column header="Totale">
                        <template #body="{ data }">
                            <span v-text="parseFloat(data.total_amount).toFixed(2)" /> &euro;
                        </template>
                    </Column>
                    <Column header="Valido il">
                        <template #body="{ data }">
                            <span v-text="moment(data.order_date).format('DD/MM/YYYY')" />
                        </template>
                    </Column>
                    <Column header="Stato" field="status">
                        <template #body="{ data }">
                            <span :class="`badge text-bg-${data.status_info.color}`" v-text="data.status_info.label" />
                        </template>
                    </Column>
                </DataTable>
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>
<style>
    
</style>
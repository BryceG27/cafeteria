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
    <Head title="Ordini clienti" />

    <AuthenticatedLayout>
        <div class="content">
            <SuccessMessage />
            <ErrorMessage />

            <BaseBlock title="Ordini clienti" contentClass="pb-3">
                <DataTable
                    striped-rows
                    :value="orders"
                >
                    <template #empty>
                        <div class="p-4 text-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun ordine inserito</p>
                        </div>
                    </template>

                    <Column header="Cliente">
                        <template #body="{ data }">

                        </template>
                    </Column>
                    <Column header="Prodotti">
                        <template #body="{ data }">

                        </template>
                    </Column>
                    <Column header="Totale">
                        <template #body="{ data }">

                        </template>
                    </Column>
                    <Column header="Creato il">
                        <template #body="{ data }">

                        </template>
                    </Column>
                    <Column header="Stato">
                        <template #body="{ data }">

                        </template>
                    </Column>
                </DataTable>
            </BaseBlock>
        </div>
    </AuthenticatedLayout>

</template>
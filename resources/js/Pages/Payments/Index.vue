<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

import DataTable from 'primevue/datatable'
import Column from 'primevue/column'

const props = defineProps({
    payments: Array,
    auth : Object,
})

</script>
<template>
    <Head title="Pagamenti" />

    <AuthenticatedLayout>
        <div class="content">
            <BaseBlock title="Pagamenti" content-class="pb-3">
                <DataTable
                    :value="payments"
                    stripedRows
                >
                    <Column header="Totale"></Column>
                    <Column header="Metodo di pagamento"></Column>
                    <Column header="Data"></Column>
                    <Column header="Stato"></Column>
                </DataTable>
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>
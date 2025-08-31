<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Popover from 'primevue/popover';

import moment from 'moment';

const props = defineProps({
    orders: Array,
    auth: Object,
});

console.log(props.orders);

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
                    paginator :rows="20" 
                    :rowsPerPageOptions="[5, 10, 20, 50]"
                >
                    <template #empty>
                        <div class="p-4 text-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun ordine inserito</p>
                        </div>
                    </template>

                    <Column class="text-center" v-if="auth.user.user_group_id == 1">
                        <template #body="{ data }">
                            <Link
                                :href="route('orders.edit', data.id)"
                                class="btn btn-alt-warning btn-sm"
                            >
                                <i class="fa fa-pencil-alt"></i>
                            </Link>
                        </template>
                    </Column>
                    <Column header="Cliente">
                        <template #body="{ data }">
                            {{ data.customer.child }}
                            <a class="link-info ms-1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-info"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="dropdown-item">
                                    <strong>Genitore: </strong><em v-text="`${data.customer.name} ${data.customer.surname}`" />
                                </li>
                            </ul>
                        </template>
                    </Column>
                    <Column header="Prodotti">
                        <template #body="{ data }">
                            <div class="d-flex align-items-center">
                                
                                <template v-if="data.child_allergies != null || data.child_allergies != ''">
                                    <a class="link-danger ms-3 attention" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-exclamation-triangle"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li class="dropdown-item">
                                            <strong>Allergie: </strong><span v-text="`${data.customer.child_allergies}`" />
                                        </li>
                                    </ul>
                                </template>
                            </div>

                        </template>
                    </Column>
                    <Column header="Totale">
                        <template #body="{ data }">
                            € {{ data.total_amount }}
                        </template>
                    </Column>
                    <Column header="Creato il">
                        <template #body="{ data }">
                            {{ moment(data.created_at).format('DD/MM/YYYY') }}
                        </template>
                    </Column>
                    <Column header="Valido il">
                        <template #body="{ data }">
                            {{ moment(data.order_date).format('DD/MM/YYYY') }}
                        </template>
                    </Column>
                    <Column header="Stato">
                        <template #body="{ data }">
                            <span :class="`badge text-bg-${data.status_info.color}`" v-text="data.status_info.label" />
                        </template>
                    </Column>
                </DataTable>
            </BaseBlock>
        </div>
    </AuthenticatedLayout>

</template>
<style scoped>
.attention {
    animation: blink 2s linear infinite;
}

@keyframes blink {
    0% { opacity: 1; }
    50% { opacity: 0.2; }
    100% { opacity: 1; }
}
</style>
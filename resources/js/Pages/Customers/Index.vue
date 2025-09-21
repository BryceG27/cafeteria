<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";
import { computed, ref } from "vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ToggleSwitch from 'primevue/toggleswitch';
import InputText from 'primevue/inputtext';

const props = defineProps({
    customers: Array,
    auth: Object,
});

const showOnlyActive = ref(true);
const customersFilter = ref('');
const filteredCustomers = computed(() => {
    if (showOnlyActive.value) {
        return props.customers.filter(customer => customer.is_active).filter(customer => {
            return `${customer.name} ${customer.surname}`.toLowerCase().includes(customersFilter.value.toLowerCase());
        });
    }
    return props.customers;
});

</script>
<template>
    <Head title="Clienti" />

    <AuthenticatedLayout>
        <div class="content">
            <SuccessMessage />
            <ErrorMessage />

            <BaseBlock title="Clienti" contentClass="pb-3">
                <DataTable
                    stripedRows
                    :value="filteredCustomers"
                    filterDisplay="row"
                >
                    <template #header>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Elenco clienti</h5>
                        </div>
                    </template>
                    <template #empty>
                        <div class="p-4 text-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun cliente inserito</p>
                        </div>
                    </template>
                    <Column field="id" class="text-center" v-if="auth.user.user_group_id == 1">
                        <template #body="{ data }">
                            <div class="d-none d-xl-inline me-1">
                                <Link 
                                    :href="route('users.toggle-active', data.id)"
                                    method="put"
                                    as="button"
                                    class="btn btn-sm clickable"
                                    :class="!data.is_active ? 'btn-alt-success' : 'btn-alt-warning'"
                                >
                                    <i class="fa" :class="!data.is_active ? 'fa-play' : 'fa-pause'"></i>
                                </Link>
                                <Link
                                    :href="route('customers.show', { 'customer' : data.id})"
                                    class="btn btn-alt-info btn-sm ms-1 clickable"
                                >
                                    <i class="fa fa-eye"></i>
                                </Link>
                            </div>
                            <div class="d-inline d-xl-none">
                                <button class="btn btn-sm btn-alt-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    ...
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <Link 
                                            :href="route('users.toggle-active', data.id)"
                                            method="put"
                                            as="button"
                                            class="dropdown-item d-flex gap-2 align-items-center"
                                            style="font-size: 13px"
                                        >
                                            <button 
                                                class="btn btn-sm me-1"
                                                :class="!data.is_active ? 'btn-alt-success' : 'btn-alt-warning'"
                                                type="button"
                                                style="width: 30%"
                                            >
                                                <i class="fa" :class="!data.is_active ? 'fa-play' : 'fa-pause'"></i>
                                            </button>
                                            <span v-text="!data.is_active ? 'Attiva' : 'Disattiva'" />
                                        </Link>
                                    </li>
                                    <li>
                                        <Link
                                            :href="route('customers.show', { customer : data.id})"
                                            class="dropdown-item d-flex gap-2 align-items-center"
                                            style="font-size: 13px"
                                        >
                                            <button
                                                class="btn btn-alt-info btn-sm me-1"
                                                type="button"
                                                style="width: 30%"
                                            >
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <span>
                                                Visualizza
                                            </span>
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                        </template>
                    </Column>
                    <Column header="Cliente" :showFilterMenu="false">
                        <template #body="{ data }">
                            <Link
                                :href="route('users.edit', data.id)"
                                v-text="`${data.name} ${data.surname}`"
                                v-if="data.user_group_id == 2"
                            />
                            <span 
                                v-else
                                v-text="`${data.name} ${data.surname}`"
                            />
                        </template>
                        <template #filter>
                            <InputText 
                                v-model="customersFilter" 
                                placeholder="Cerca cliente..." 
                                class="w-100" 
                            />
                        </template>
                    </Column>
                    <Column header="Figlio" field="child" />
                    <Column field="email" header="Email" />
                    <Column header="Totale pagato">
                        <template #body="{ data }">
                            {{ data.payments.reduce((acc, payment) => acc + parseFloat(payment.amount) , 0).toFixed(2) }} &euro;
                        </template>
                    </Column>
                    <Column header="Totale ordinato">
                        <template #body="{ data }">
                            {{ data.orders.reduce((acc, order) => acc + parseFloat(order.total_amount) , 0).toFixed(2) }} &euro;
                        </template>
                    </Column>
                    <Column field="is_active" header="Stato" :showFilterMenu="false" class="text-center">
                        <template #body="{ data }">
                            <span v-if="data.is_active" class="badge bg-success">Attivo</span>
                            <span v-else class="badge bg-danger">Inattivo</span>
                        </template>
                        <template #filter>
                            <div class="d-flex align-items-center gap-2">
                                <span>Mostra solo attivi</span>
                                <ToggleSwitch v-model="showOnlyActive" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.clickable {
    cursor: pointer;
}
</style>
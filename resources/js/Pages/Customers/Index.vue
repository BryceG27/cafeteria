<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

const props = defineProps({
    customers: Array,
    auth: Object,
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
                    :value="customers"
                >
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
                                    class="btn btn-sm"
                                    :class="!data.is_active ? 'btn-alt-success' : 'btn-alt-warning'"
                                >
                                    <i class="fa" :class="!data.is_active ? 'fa-play' : 'fa-pause'"></i>
                                </Link>
                                <Link
                                    :href="route('customers.show', { 'customer' : data.id})"
                                    class="btn btn-alt-info btn-sm ms-1"
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
                    <Column header="Cliente">
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
                    </Column>
                    <Column header="Figlio" field="child" />
                    <Column field="email" header="Email" />
                    <Column header="Totale pagato">
                        <template #body="{ data }">
                            &euro;
                        </template>
                    </Column>
                    <Column header="Totale ordinato">
                        <template #body="{ data }">
                            {{ data.orders.reduce((acc, order) => acc + parseFloat(order.total_amount) , 0).toFixed(2) }} &euro;
                        </template>
                    </Column>
                    <Column field="is_active" header="Stato">
                        <template #body="{ data }">
                            <span v-if="data.is_active" class="badge bg-success">Attivo</span>
                            <span v-else class="badge bg-danger">Inattivo</span>
                        </template>
                    </Column>
                </DataTable>
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>
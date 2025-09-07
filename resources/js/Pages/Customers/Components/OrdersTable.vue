<script setup>
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dropdown from 'primevue/dropdown';

import { Link } from "@inertiajs/vue3";
import moment from 'moment';

const props = defineProps({
    customer: Object,
    order_statuses: Array,
    auth: Object,
});

const emit = defineEmits(['on-status-change']);


</script>
<template>
    <div class="container-fluid pt-4">
        <div class="row">
            <div class="col-md-12">
                <DataTable
                    stripedRows
                    :value="customer.orders"
                    style="max-height: 50rem; overflow-y: auto"
                    editMode="cell" 
                    @cell-edit-complete="emit('on-status-change', $event)"
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
                                class="btn btn-alt-info btn-sm"
                            >
                                <i class="fa fa-eye"></i>
                            </Link>
                        </template>
                    </Column>
                    <Column header="Prodotti">
                        <template #body="{ data }">
                            <div class="row">
                                <div class="col-md-10 px-0">
                                    <table class="table table-bordered rounded-2 h-100">
                                        <tbody>
                                            <tr v-if="data.first_dish">
                                                <td class="py-2">
                                                    <strong>Primo:</strong> <span v-text="data.first_dish.name" />
                                                </td>
                                            </tr>
                                            <tr v-if="data.second_dish">
                                                <td class="py-2">
                                                    <strong>Secondo:</strong> <span v-text="data.second_dish.name" />
                                                </td>
                                            </tr>
                                            <tr v-if="data.side_dish">
                                                <td class="py-2">
                                                    <strong>Contorno:</strong> <span v-text="data.side_dish.name" />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </template>
                    </Column>
                    <Column header="Totale">
                        <template #body="{ data }">
                            {{ data.total_amount }} &euro;
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
                        <template #editor="{ data }">
                            <Dropdown 
                                v-model="data.status"
                                :options="order_statuses" 
                                optionLabel="label" 
                                optionValue="value" 
                                class="w-100"
                                placeholder="Seleziona stato"
                            />
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </div>
</template>
<style>
    
</style>
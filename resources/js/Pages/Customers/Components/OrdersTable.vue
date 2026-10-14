<script setup>
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Select from 'primevue/select';
import Divider from 'primevue/divider';

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
                    paginator
                    :rows="5"
                    :rowsPerPageOptions="[5,10,25,50]"
                >
                    <template #empty>
                        <div class="p-4 text-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun ordine inserito</p>
                        </div>
                    </template>
                    <Column header="#" field="id" class="text-center" v-if="auth.user.user_group_id == 1" />
                    <Column header="Prodotti">
                        <template #body="{ data }">
                            <div class="col-md-12 border rounded-2 px-0" v-if="data.menu">
                                <div class="row py-1" v-if="data.first_dish">
                                    <div class="col-md-10 offset-md-1">
                                        <span class="fs-14" style="font-weight: 500;" v-text="data.first_dish.name" />
                                        <br>
                                        <em class="fs-12">Primo piatto</em>
                                    </div>
                                </div>

                                <Divider class="my-1" v-if="data.first_dish && data.second_dish" />

                                <div class="row py-1" v-if="data.second_dish">
                                    <div class="col-md-10 offset-md-1">
                                        <span class="fs-14" style="font-weight: 500;" v-text="data.second_dish.name" />
                                        <br>
                                        <em class="fs-12">Secondo piatto</em>
                                    </div>
                                </div>

                                <Divider class="my-1" v-if="data.second_dish && data.side_dish" />

                                <div class="row py-1" v-if="data.side_dish">
                                    <div class="col-md-10 offset-md-1">
                                        <span class="fs-14" style="font-weight: 500;" v-text="data.side_dish.name" />
                                        <br>
                                        <em class="fs-12">Contorno</em>
                                    </div>
                                </div>

                                <template v-if="data.beverage">
                                    <Divider class="my-1" />

                                    <div class="row py-1">
                                        <div class="col-md-10 offset-md-1">
                                            <span class="fs-14" style="font-weight: 500;" v-text="data.beverage.name" />
                                            <br>
                                            <em class="fs-12">Bibita</em>
                                        </div>
                                    </div>
                                </template>

                            </div>

                            <div class="col-md-12 border rounded-2 px-0" v-else-if="data.special_menu">
                                <div class="row">
                                    <div class="col-md-10 offset-md-1">
                                        <span class="fs-14" style="font-weight: 500;" v-text="data.special_menu.product?.name" />
                                        <br>
                                        <em class="fs-12">Prodotto del menù</em>
                                    </div>
                                </div>

                                <Divider class="my-1" />

                                <div class="row">
                                    <div class="col-md-10 offset-md-1">
                                        <span class="fs-14" style="font-weight: 500;" v-text="data.beverage?.name" />
                                        <br>
                                        <em class="fs-12">Bibita</em>
                                    </div>
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
                            <Select 
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
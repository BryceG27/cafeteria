<script setup>
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Select from 'primevue/select';
import Divider from 'primevue/divider';
import DatePicker from 'primevue/datepicker';
import MultiSelect from 'primevue/multiselect';

import moment from 'moment';
import { ref } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';

const props = defineProps({
    customer: Object,
    order_statuses: Array,
    auth: Object,
});

const emit = defineEmits(['on-status-change']);

const filters = ref({
    created_at: { value: null, matchMode: FilterMatchMode.DATE_IS },
    order_date: { value: null, matchMode: FilterMatchMode.DATE_IS },
    status: { value: null, matchMode: FilterMatchMode.IN }
});

</script>
<template>
    <div class="pt-4 container-fluid">
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
                    v-model:filters="filters"
                    filterDisplay="menu"
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
                            <div class="px-0 border col-md-12 rounded-2" v-if="data.menu">
                                <div class="py-1 row" v-if="data.first_dish">
                                    <div class="col-md-10 offset-md-1">
                                        <span class="fs-14" style="font-weight: 500;" v-text="data.first_dish.name" />
                                        <br>
                                        <em class="fs-12">Primo piatto</em>
                                    </div>
                                </div>

                                <Divider class="my-1" v-if="data.first_dish && data.second_dish" />

                                <div class="py-1 row" v-if="data.second_dish">
                                    <div class="col-md-10 offset-md-1">
                                        <span class="fs-14" style="font-weight: 500;" v-text="data.second_dish.name" />
                                        <br>
                                        <em class="fs-12">Secondo piatto</em>
                                    </div>
                                </div>

                                <Divider class="my-1" v-if="data.second_dish && data.side_dish" />

                                <div class="py-1 row" v-if="data.side_dish">
                                    <div class="col-md-10 offset-md-1">
                                        <span class="fs-14" style="font-weight: 500;" v-text="data.side_dish.name" />
                                        <br>
                                        <em class="fs-12">Contorno</em>
                                    </div>
                                </div>

                                <template v-if="data.beverage">
                                    <Divider class="my-1" />

                                    <div class="py-1 row">
                                        <div class="col-md-10 offset-md-1">
                                            <span class="fs-14" style="font-weight: 500;" v-text="data.beverage.name" />
                                            <br>
                                            <em class="fs-12">Bibita</em>
                                        </div>
                                    </div>
                                </template>

                            </div>

                            <div class="px-0 border col-md-12 rounded-2" v-else-if="data.special_menu">
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
                    <Column header="Creato il" field="created_at" :showFilterMatchModes="false" :showApplyButton="false">
                        <template #body="{ data }">
                            {{ moment(data.created_at).format('DD/MM/YYYY') }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <DatePicker 
                                class="w-100"
                                v-model="filterModel.value"
                                @update:modelValue="filterCallback"
                                dateFormat="dd/mm/yy"
                            />
                        </template>
                    </Column>
                    <Column header="Valido il" field="order_date" :showFilterMatchModes="false" :showApplyButton="false">
                        <template #body="{ data }">
                            {{ moment(data.order_date).format('DD/MM/YYYY') }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <DatePicker 
                                class="w-100"
                                v-model="filterModel.value"
                                @update:modelValue="filterCallback"
                                dateFormat="dd/mm/yy"
                            />
                        </template>
                    </Column>
                    <Column header="Stato" field="status" :showFilterMatchModes="false" :showApplyButton="false">
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
                        <template #filter="{ filterModel, filterCallback }">
                            <MultiSelect
                                v-model="filterModel.value"
                                :options="order_statuses"
                                optionLabel="label"
                                optionValue="value"
                                class="w-100"
                                placeholder="Seleziona stato"
                                @update:modelValue="filterCallback"
                                append-to="self"
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
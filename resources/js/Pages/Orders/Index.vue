<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import BaseBlock from "@/Components/BaseBlock.vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Select from 'primevue/select';
import InputText from 'primevue/inputtext';
import DatePicker from 'primevue/datepicker';
import Fieldset from 'primevue/fieldset';
import Divider from 'primevue/divider';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const toast = useToast();

import { FilterMatchMode } from '@primevue/core/api';

import { Head, router, useForm } from "@inertiajs/vue3";
import { reactive, watch } from 'vue';
import moment from 'moment';
import debounce from "lodash/debounce"

const props = defineProps({
    flash : Object,
    errors : Object,
    orders: Object,
    auth: Object,
    order_statuses: Array,
});

watch(
    () => props.flash?.message,
    (newVal) => {
        if (newVal) {
            toast.add({
                severity: 'success',
                summary: 'Ordine aggiornato',
                detail: newVal,
                life: 3000
            });
        }
    }
);

watch(
    () => props.errors,
    (errors) => {
        if (errors && Object.keys(errors).length > 0) {
            Object.values(errors).forEach(error => {
                toast.add({
                    severity: 'error',
                    summary: 'Riscontrato errore',
                    detail: error,
                    life: 3000
                });
            });
        }
    },
    { deep: true }
);

const filters = reactive({
    child_name : { value : null, matchMode: FilterMatchMode.CONTAINS},
    order_date : { value: null, matchMode: FilterMatchMode.DATE_IS },
    created_at : { value: null, matchMode: FilterMatchMode.DATE_IS },
    'status_info.value' : { value: null, matchMode: FilterMatchMode.EQUALS }
});

const updateOrderStatus = (event) => {
    const form = useForm({
        status: event.newData.status,
    });

    form.put(route('orders.update-status', { order : event.newData.id }), {
        preserveScroll: true,
    });
}

const onPage = debounce((page = 1) => {
    router.get(route('orders.index'),
        { 
            page : page,
            child_name : filters.child_name?.value,
            order_date : filters.order_date.value ? (filters.order_date.value).toISOString() : null,
            created_at: filters.created_at.value ? (filters.created_at.value).toISOString() : null,
            status : filters['status_info.value'].value,
        },
        {
            preserveState : true,
            preserveScroll : true,
            replace : true
        })
}, 300)

</script>
<template>
    <Head title="Ordini clienti" />

    <AuthenticatedLayout>
        <Toast />

        <div class="content">
            <BaseBlock title="Ordini clienti" contentClass="pb-3">
                <DataTable
                    striped-rows
                    :value="orders.data"
                    scrollable
                    scroll-height="75vh"
                    editMode="cell" 
                    @cell-edit-complete="updateOrderStatus"
                    v-model:filters="filters"
                    filterDisplay="row"
                    lazy
                    paginator
                    :rows="50"
                    :totalRecords="orders.total"
                    @page="onPage($event.page + 1)"
                    @filter="onPage(1)""
                >
                    <template #empty>
                        <div class="p-4 text-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun ordine inserito</p>
                        </div>
                    </template>
                    <Column style="width: 15%" header="Cliente" field="child_name" :showFilterMenu="false">
                        <template #body="{ data }">
                            <span v-text="data.child_name" class="fs-14" />
                            <br>
                            <span class="text-muted fs-12">{{ data.customer.name }} {{ data.customer.surname }}</span>
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText 
                                v-model="filterModel.value" 
                                @input="filterCallback()"
                                placeholder="Cerca per nome o cognome" 
                                class="ms-2 w-100"
                            />
                        </template>
                    </Column>
                    <Column style="width: 15%" header="Nome menù" field="menu.name">
                        <template #body="{ data }">
                            <span v-if="data.menu" v-text="data.menu.name" />
                            <span v-else-if="data.special_menu" v-text="data.special_menu.name" />
                        </template>
                    </Column>
                    <Column style="width: 30%" header="Prodotti">
                        <template #body="{ data }">
                            <div class="row">
                                <div 
                                    v-if="data.customer.child_allergies != undefined && data.customer.child_allergies != ''" class="rounded-2 rounded-end-0 border border-end-0 col-md-1 px-0 d-flex justify-content-center align-items-center">
                                    <a class="link-danger attention" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-exclamation-triangle"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li class="dropdown-item">
                                            <strong>Allergie: </strong><span v-text="`${data.customer.child_allergies}`" />
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-10 border rounded-2 px-0" :class="{'rounded-start-0' : data.customer.child_allergies != undefined, 'rounded-end-0' : data.notes != undefined, 'col-md-12' : data.customer.child_allergies == undefined && data.notes == undefined, 'col-md-11' : data.customer.child_allergies == undefined || data.notes == undefined}" v-if="data.menu">
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
                                </div>

                                <div class="col-md-10 border rounded-2 px-0" :class="{'rounded-start-0' : data.customer.child_allergies != undefined, 'rounded-end-0' : data.notes != undefined, 'col-md-12' : data.customer.child_allergies == undefined && data.notes == undefined, 'col-md-11' : data.customer.child_allergies == undefined || data.notes == undefined}" v-else-if="data.special_menu">
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
                                            <span class="fs-14" style="font-weight: 500;" v-text="data.first_dish?.name" />
                                            <br>
                                            <em class="fs-12">Bibita</em>
                                        </div>
                                    </div>
                                </div>

                                <div 
                                    v-if="data.notes != undefined && data.notes != ''" class="rounded-2 rounded-start-0 border border-start-0 col-md-1 px-0 d-flex justify-content-center align-items-center">
                                    <a class="link-primary attention" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-question"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li class="dropdown-item">
                                            <strong>Note: </strong><span v-text="`${data.notes}`" />
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </template>
                    </Column>
                    <Column style="width: 10%" header="Totale">
                        <template #body="{ data }">
                            € {{ data.total_amount }}
                        </template>
                    </Column>
                    <Column style="width: 12.5%" header="Creato il" field="created_at" :showFilterMenu="false">
                        <template #body="{ data }">
                            {{ moment(data.created_at).format('DD/MM/YYYY') }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <DatePicker 
                                inputClass="w-100"
                                class="w-100"
                                placeholder="Cerca per data"
                                date-format="dd/mm/yy"
                                v-model="filterModel.value"
                                @value-change="filterCallback()"
                                showButtonBar
                            />
                        </template>
                    </Column>
                    <Column style="width: 12.5%" header="Per giorno" field="order_date" :showFilterMenu="false">
                        <template #body="{ data }">
                            {{ moment(data.order_date).format('DD/MM/YYYY') }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <DatePicker 
                                inputClass="w-100"
                                class="w-100"
                                placeholder="Cerca per data"
                                date-format="dd/mm/yy"
                                v-model="filterModel.value"
                                @value-change="filterCallback()"
                                showButtonBar
                                
                            />
                        </template>
                    </Column>
                    <Column style="width: 5%" header="Stato" field="status_info.value" :showFilterMenu="false">
                        <template #body="{ data }">
                            <div class="text-center">
                                <span :class="`badge text-bg-${data.status_info.color}`" v-text="data.status_info.label" />
                            </div>
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
                            <Select 
                                class="ms-2 w-100" 
                                :options="order_statuses" 
                                v-model="filterModel.value"
                                optionLabel="label" 
                                optionValue="value" 
                                placeholder="Stato" 
                                showClear
                                @change="filterCallback()"
                            />
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

.clickable {
    cursor: pointer;
}

.fs-12 {
    font-size: 12px;
}

.fs-14 {
    font-size: 14px;
}

@keyframes blink {
    0% { opacity: 1; }
    50% { opacity: 0.2; }
    100% { opacity: 1; }
}
</style>
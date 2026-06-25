<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import BaseBlock from "@/Components/BaseBlock.vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Select from 'primevue/select';
import InputText from 'primevue/inputtext';
import DatePicker from 'primevue/datepicker';
import Toast from 'primevue/toast'
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
                            <div class="d-flex align-items-center">
                                {{ data.child_name }}
                                <a class="link-info ms-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-info"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="dropdown-item">
                                        <strong>Genitore: </strong><em v-text="`${data.customer.name} ${data.customer.surname}`" />
                                    </li>
                                </ul>
                            </div>
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
                    <Column style="width: 15%" header="Nome menù" field="menu.name" />
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

                                <div class="col-md-10 px-0">
                                    <table class="table table-bordered rounded-2 h-100 align-middle">
                                        <tbody>
                                            <tr v-if="data.first_dish">
                                                <td class="p-2 align-middle">
                                                    <div class="d-flex justify-content-between">
                                                        <span>
                                                            <strong>Primo:</strong> <span v-text="data.first_dish.name" />
                                                        </span>
                                                        <button class="btn btn-link" v-if="data.first_dish.image" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fa fa-camera"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li class="dropdown-item">
                                                                <div style="width: 15rem; height: 10rem">
                                                                    <img :src="`/storage/app/public/${data.first_dish.image}`" :alt="data.first_dish.name" class="img-fluid">
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="data.second_dish">
                                                <td class="p-2 align-middle">
                                                    <div class="d-flex justify-content-between">
                                                        <span>
                                                            <strong>Secondo:</strong> <span v-text="data.second_dish.name" />
                                                        </span>
                                                        <button class="btn btn-link" v-if="data.second_dish.image" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fa fa-camera"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li class="dropdown-item">
                                                                <div style="width: 15rem; height: 10rem">
                                                                    <img :src="`/storage/app/public/${data.second_dish.image}`" :alt="data.second_dish.name" class="img-fluid">
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="data.side_dish">
                                                <td class="p-2 align-middle">
                                                    <div class="d-flex justify-content-between">
                                                        <span>
                                                            <strong>Contorno:</strong> <span v-text="data.side_dish.name" />
                                                        </span>
                                                        <button class="btn btn-link" v-if="data.side_dish.image" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fa fa-camera"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li class="dropdown-item">
                                                                <div style="width: 15rem; height: 10rem">
                                                                    <img :src="`/storage/app/public/${data.side_dish.image}`" :alt="data.side_dish.name" class="img-fluid">
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
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
                                @date-select="filterCallback()"
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
                                @date-select="filterCallback()"
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
                                placeholder="Seleziona stato" 
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

@keyframes blink {
    0% { opacity: 1; }
    50% { opacity: 0.2; }
    100% { opacity: 1; }
}
</style>
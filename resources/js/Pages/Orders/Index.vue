<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import DatePicker from 'primevue/datepicker';

import { computed, reactive, ref } from 'vue';

import moment from 'moment';

const props = defineProps({
    orders: Array,
    auth: Object,
    order_statuses: Array,
});

const ordersFilters = ref({

});

const filters = reactive({
    child_name : null,
    status : 1,
    order_date : null,
});

const filteredOrders = computed(() => {
    return props.orders.filter(order => {
        if (filters.status == undefined) return true;
        return (order.status_info.value === filters.status);
    }).filter(order => {
        if (filters.child_name == undefined) return true;
        const name = order.child_name.toLowerCase();
        const searchTerm = filters.child_name.toLowerCase();
        return name.includes(searchTerm) || (order.customer && (order.customer.name.toLowerCase().includes(searchTerm) || order.customer.surname.toLowerCase().includes(searchTerm)));
    }).filter(order => {
        if (filters.order_date == undefined || filters.order_date == null) return true;
        return moment(order.order_date).isSame(moment(filters.order_date), 'day');
    });
})

const updateOrderStatus = (event) => {
    const form = useForm({
        status: event.newData.status,
    });

    form.put(route('orders.update-status', { order : event.newData.id }), {
        preserveScroll: true,
    });
}

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
                    :value="filteredOrders"
                    paginator :rows="20" 
                    :rowsPerPageOptions="[5, 10, 20, 50]"
                    editMode="cell" 
                    @cell-edit-complete="updateOrderStatus"
                    tableStyle="min-width: 60rem"
                    v-model:filters="ordersFilters"
                    filterDisplay="row"
                >
                    <template #empty>
                        <div class="p-4 text-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun ordine inserito</p>
                        </div>
                    </template>

                    <Column style="width: 5%" class="text-center" v-if="auth.user.user_group_id == 1">
                        <template #body="{ data }">
                            <Link
                                :href="route('orders.edit', data.id)"
                                class="btn btn-alt-warning btn-sm"
                            >
                                <i class="fa fa-pencil-alt"></i>
                            </Link>
                        </template>
                    </Column>
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
                        <template #filter>
                            <InputText 
                                v-model="filters.child_name" 
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
                                                        <button class="btn-link" v-if="data.first_dish.image" data-bs-toggle="dropdown" aria-expanded="false">
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
                                                        <button class="btn-link" v-if="data.second_dish.image" data-bs-toggle="dropdown" aria-expanded="false">
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
                                                        <button class="btn-link" v-if="data.side_dish.image" data-bs-toggle="dropdown" aria-expanded="false">
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
                    <Column style="width: 10%" header="Creato il" field="created_at" :showFilterMenu="false">
                        <template #body="{ data }">
                            {{ moment(data.created_at).format('DD/MM/YYYY') }}
                        </template>
                    </Column>
                    <Column style="width: 10%" header="Per giorno" field="order_date" :showFilterMenu="false">
                        <template #body="{ data }">
                            {{ moment(data.order_date).format('DD/MM/YYYY') }}
                        </template>
                        <template #filter>
                            <DatePicker 
                                inputClass="w-100"
                                class="w-100"
                                placeholder="Cerca per data"
                                date-format="dd/mm/yy"
                                v-model="filters.order_date"
                                showButtonBar
                            />
                        </template>
                    </Column>
                    <Column style="width: 5%" header="Stato" field="status_info.value" :showFilterMenu="false">
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
                        <template #filter>
                            <Dropdown 
                                class="ms-2 w-100" 
                                :options="order_statuses" 
                                v-model="filters.status"
                                optionLabel="label" 
                                optionValue="value" 
                                placeholder="Seleziona stato" 
                                showClear
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
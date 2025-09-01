<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dropdown from 'primevue/dropdown';

import { computed, ref } from 'vue';

import moment from 'moment';

const props = defineProps({
    orders: Array,
    auth: Object,
    order_statuses: Array,
});

const selectedStatus = ref(1);

const filteredOrders = computed(() => {
    return props.orders.filter(order => {
        if (!selectedStatus.value) return true;
        return order.status_info.value === selectedStatus.value;
    })
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
                >

                    <template #header>
                        <div class="d-flex align-items-center justify-content-end">
                            Filtra per stato: 
                            <Dropdown 
                                class="ms-2" 
                                :options="order_statuses" 
                                v-model="selectedStatus"
                                optionLabel="label" 
                                optionValue="value" 
                                placeholder="Seleziona stato" 
                                showClear
                                style="width: 200px"
                            />
                        </div>
                    </template>
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
                            {{ data.customer.child }} {{ data.customer.child.split(' ').length == 1 ? data.customer.surname : '' }}
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
                            <div class="row">
                                <div 
                                    v-if="data.child_allergies != undefined && data.child_allergies != ''" class="rounded-2 rounded-end-0 border border-end-0 col-md-2 px-0 d-flex justify-content-center align-items-center">
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
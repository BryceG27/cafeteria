<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

import Swal from 'sweetalert2';

import moment from 'moment';

const props = defineProps({
    orders: Array,
    auth: Object,
});

const destroy = (id) => {
    const form = useForm({});

    Swal.fire({
        title: 'Sei sicuro?',
        text: "Non potrai tornare indietro!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, cancella!',
        cancelButtonText: 'Annulla'
    }).then((result) => {
        if (result.isConfirmed)
            form.delete(route('orders.destroy', { order : id}));
    })
}

</script>

<template>
    <Head title="Ordini" />    

    <AuthenticatedLayout>
        <div class="content">
            <SuccessMessage />
            <ErrorMessage />

            <BaseBlock title="Ordini" contentClass="pb-3">
                <template #options>
                    <Link :href="route('orders.create')" class="btn btn-alt-primary btn-sm">
                        <i class="fa fa-plus me-1"></i> Nuovo ordine
                    </Link>
                </template>
                <DataTable
                    striped-rows
                    :value="orders"
                >
                    <template #empty>
                        <div class="p-4 text-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun ordine creato</p>
                        </div>
                    </template>

                    <Column class="text-center">
                        <template #body="{ data }">
                            <Link
                                :href="route('orders.edit', data.id)"
                                class="btn btn-alt-warning btn-sm"
                            >
                                <i class="fa fa-pencil-alt"></i>
                            </Link>

                            <button class="btn btn-alt-secondary btn-sm ms-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" v-if="data.status != 3 && (moment(data.created_at).format('YYYY-MM-DD') <= moment().format('YYYY-MM-DD'))">
                                ...
                            </button>
                            <ul class="dropdown-menu">
                                <li v-if="data.status == 0">
                                    <Link
                                        :href="route('orders.destroy', data.id)"
                                        class="dropdown-item d-flex gap-2 align-items-center" style="font-size: 13px"
                                        as="button"
                                        method="patch"
                                    >
                                        <button class="btn btn-alt-success btn-sm">
                                            <i class="fa fa-dollar-sign"></i>
                                        </button>
                                        Effettua pagamento
                                    </Link>
                                </li>
                                <li v-if="data.status == 0">
                                    <hr class="dropdown-divider"></hr>
                                </li>
                                <li v-if="data.status != 3 && (moment(data.created_at).format('YYYY-MM-DD') <= moment().format('YYYY-MM-DD'))">
                                    <div
                                        @click="destroy(data.id)"
                                        class="dropdown-item d-flex gap-2 align-items-center" style="font-size: 13px"
                                        as="button"
                                        method="delete"
                                    >
                                        <button class="btn btn-alt-danger btn-sm">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        Cancella
                                    </div>
                                </li>
                            </ul>
                        </template>
                    </Column>
                    <Column header="Prodotti">
                        <template #body="{ data }">
                            <table class="table table-bordered rounded-2 h-100 align-middle">
                                <tbody>
                                    <tr v-if="data.first_dish">
                                        <td class="p-2 align-middle">
                                            <strong>Primo:</strong> <span v-text="data.first_dish.name" />
                                        </td>
                                    </tr>
                                    <tr v-if="data.second_dish">
                                        <td class="p-2 align-middle">
                                            <strong>Secondo:</strong> <span v-text="data.second_dish.name" />
                                        </td>
                                    </tr>
                                    <tr v-if="data.side_dish">
                                        <td class="p-2 align-middle">
                                            <strong>Contorno:</strong> <span v-text="data.side_dish.name" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </template>
                    </Column>
                    <Column header="Totale">
                        <template #body="{ data }">
                            <span v-text="parseFloat(data.total_amount).toFixed(2)" /> &euro;
                        </template>
                    </Column>
                    <Column header="Valido il">
                        <template #body="{ data }">
                            <span v-text="moment(data.order_date).format('DD/MM/YYYY')" />
                        </template>
                    </Column>
                    <Column header="Stato" field="status">
                        <template #body="{ data }">
                            <span :class="`badge text-bg-${data.status_info.color}`" v-text="data.status_info.label" />
                        </template>
                    </Column>
                </DataTable>
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>
<style>
    
</style>
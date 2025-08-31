<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dropdown from 'primevue/dropdown';

import moment from "moment";

const props = defineProps({
    auth : Object,
    customer : Object,
    order_statuses : Array,
});

const submit = () => {
    /* form.put(route('customers.update', form.id), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    }); */
}

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
    <Head :title="`${customer.name} ${customer.surname}`" />

    <AuthenticatedLayout>
        <div class="content">
            <BaseBlock :title="`Dati Cliente: ${customer.name} ${customer.surname}`" content-class="pb-3">
                <template #options>
                    <Link 
                        class="btn btn-alt-info btn-sm"
                        :href="route('customers.index')"
                    >
                        <i class="fa fa-arrow-left me-1"></i>
                        Indietro
                    </Link>
                </template>

                <div class="container-fluid">
                    <div class="row pb-2">
                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-md-3">

                                    <strong>Nome:</strong>
                                </div>
                                <div class="col-md-9">
                                    {{ customer.name }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Cognome:</strong>
                                </div>
                                <div class="col-md-8">
                                    {{ customer.surname }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Email:</strong>
                                </div>
                                <div class="col-md-8">
                                    {{ customer.email }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row pb-2">
                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Figlio:</strong>
                                </div>
                                <div class="col-md-9">
                                    {{ customer.child }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-md-4 pe-0">
                                    <strong>
                                        Allergie figlio:
                                    </strong>
                                </div>
                                <div class="col-md-8">
                                    {{ customer.child_allergies ? customer.child_allergies : 'Nessuna' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </BaseBlock>
            <BaseBlock title="Ordini del cliente" content-class="pb-3">
                <div class="container-fluid pt-4">
                    <div class="row">
                        <div class="col-md-12">
                            <DataTable
                                stripedRows
                                :value="customer.orders"
                                style="max-height: 50rem; overflow-y: auto"
                                editMode="cell" 
                                @cell-edit-complete="updateOrderStatus"
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
                                            class="btn btn-alt-warning btn-sm"
                                        >
                                            <i class="fa fa-pencil-alt"></i>
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
                        </div>
                    </div>
                </div>
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>

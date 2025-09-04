<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';

import Swal from 'sweetalert2';

import moment from 'moment';
import { ref, computed, onMounted } from "vue"
import axios from "axios";

import "https://js.stripe.com/v3";

const props = defineProps({
    credits : Array,
    order : Object,
    orders: Array,
    auth: Object,
});

const selectedOrder = ref(null)
const showDialog = ref(false);
const credit_available = computed(() => {
    return props.credits.reduce((acc, credit) => acc + parseFloat(credit.amount_available) , 0).toFixed(2);
});

onMounted(() => {
    if (props.order) {
        selectedOrder.value = props.order;
        showDialog.value = true;
    }
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

const payWithStripe = () => {
    axios.post(route('payments.checkout', { order : selectedOrder.value.id, payment_method: 3 }))
        .then(response => {
            const stripe = Stripe(props.variables.stripe_key);
            stripe.redirectToCheckout({ sessionId: response.data.id });
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

const payWithCreditAvailable = () => {
    const form = useForm({});

    form.post(route('payments.checkout', { order : selectedOrder.value.id, payment_method : 1 }), {
        onSuccess: () => {
            showDialog.value = false;
        }
    });
}

const payWithPayPal = () => {
    form.post(route('payments.checkout', { order : selectedOrder.value.id, payment_method : 2 }), {
        onSuccess: () => {
            showDialog.value = false;
        }
    });
}
</script>

<template>
    <Head title="Ordini" />    

    <AuthenticatedLayout>
        <div class="content">
            <SuccessMessage />
            <ErrorMessage />

            <Dialog
                v-model:visible="showDialog"
                modal
                :header="`Pagamento menù: ${selectedOrder?.menu?.name} - ${moment(selectedOrder?.date).format('DD/MM')}`"
                :style="{ width: '50vw', height: '46vh' }"
                :closable="false"
            >
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th style="width: 40%">Menù</th>
                                        <td style="width: 60%" v-text="selectedOrder?.menu?.name" />
                                    </tr>
                                    <tr>
                                        <th style="width: 40%">Data</th>
                                        <td style="width: 60%" v-text="moment(selectedOrder?.order_date).format('DD/MM/YYYY')" />
                                    </tr>
                                    <tr>
                                        <th style="width: 40%">Totale</th>
                                        <td style="width: 60%" v-text="parseFloat(selectedOrder?.total_amount).toFixed(2) + ' €'" />
                                    </tr>
                                    <tr>
                                        <th style="width: 40%">Da pagare</th>
                                        <td style="width: 60%" v-text="parseFloat(selectedOrder?.to_be_paid).toFixed(2) + ' €'" />
                                    </tr>
                                    <tr v-if="credit_available > 0">
                                        <th style="width: 40%">Credito disponibile</th>
                                        <td style="width: 60%" v-text="credit_available + ' €'" />
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <template #footer>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-4" :class="{ 'col-md-6' : credit_available <= 0, 'col-md-4' : credit_available > 0 }" @click.prevent="payWithStripe">
                                <button class="btn btn-card btn-lg w-100 gap-2">
                                    Paga con carta
                                </button>
                            </div>
                            <div class="col-md-4" :class="{ 'col-md-6' : credit_available <= 0, 'col-md-4' : credit_available > 0 }" @click.prevent="payWithCreditAvailable">
                                <button class="btn btn-paypal btn-lg w-100 text-black d-flex align-items-center justify-content-center gap-2">
                                    Paga con <img src="/assets/media/various/paypal.png" style="height: 1.5rem" />
                                </button>
                            </div>
                            <div class="col-md-4" v-if="credit_available > 0" @click.prevent="payWithPayPal">
                                <button class="btn btn-cash btn-lg w-100">
                                    Paga con credito residuo
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </Dialog>

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
                                class="btn btn-alt-warning btn-sm d-none d-xl-inline"
                            >
                                <i class="fa fa-pencil-alt"></i>
                            </Link>

                            <button class="btn btn-alt-secondary btn-sm ms-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" v-if="data.status != 3 && (moment(data.created_at).format('YYYY-MM-DD') <= moment().format('YYYY-MM-DD'))">
                                ...
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <Link
                                        :href="route('orders.edit', data.id)"
                                        class="d-xl-none dropdown-item d-flex gap-2 align-items-center clickable" 
                                        style="font-size: 13px"
                                    >
                                        <button class="btn btn-alt-warning btn-sm">
                                            <i class="fa fa-pencil-alt"></i>
                                        </button>
                                        Modifica
                                    </Link>
                                </li>
                                <li v-if="data.status == 0">
                                    <div
                                        class="dropdown-item d-flex gap-2 align-items-center clickable" 
                                        style="font-size: 13px"
                                        @click="selectedOrder = data, showDialog = true"
                                    >
                                        <button class="btn btn-alt-success btn-sm" type="button">
                                            <i class="fa fa-dollar-sign"></i>
                                        </button>
                                        Effettua pagamento
                                    </div>
                                </li>
                                <li v-if="data.status == 0">
                                    <hr class="dropdown-divider"></hr>
                                </li>
                                <li v-if="data.status != 3 && (moment(data.created_at).format('YYYY-MM-DD') <= moment().format('YYYY-MM-DD'))">
                                    <div
                                        @click="destroy(data.id)"
                                        class="dropdown-item d-flex gap-2 align-items-center clickable" style="font-size: 13px"
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
                    <Column header="Giorno menù" field="menu.name">
                        <template #body="{data}">
                            <span v-text="`${data.menu.name} - ${moment(data.order_date).format('DD/MM')}`" />
                        </template>
                    </Column>
                    <Column header="Prodotti">
                        <template #body="{ data }">
                            <table class="table table-bordered rounded-2 h-100 align-middle">
                                <tbody>
                                    <tr v-if="data.first_dish">
                                        <td class="p-2 align-middle">
                                            <div class="d-flex justify-content-between">
                                                <span>
                                                    <strong>Primp:</strong> <span v-text="data.first_dish.name" />
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
                        </template>
                    </Column>
                    <Column header="Totale">
                        <template #body="{ data }">
                            <span v-text="parseFloat(data.total_amount).toFixed(2)" /> &euro;
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
<style scoped>
    .clickable {
        cursor: pointer;
    }

    .btn-paypal {
        background-color: #ffc439;
        color: #003087;
    }

    .btn-paypal:hover {
        background-color: #ffb700;
        color: #002663;
    }

    .btn-card {
        background-color: #6772e5;
        color: #ffffff;
    }

    .btn-card:hover {
        background-color: #5469d4;
        color: #ffffff;
    }

    .btn-cash {
        background-color: #50b83c;
        color: #ffffff;
    }

    .btn-cash:hover {
        background-color: #3d9b2c;
        color: #ffffff;
    }
</style>
<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';

import Swal from 'sweetalert2';

import moment from 'moment';
import { ref, computed, onMounted, onUpdated } from "vue"
import axios from "axios";

import "https://js.stripe.com/v3";

const props = defineProps({
    variables : Object,
    credits : Array,
    orders_to_be_paid : Object,
    orders: Array,
    auth: Object,
});


const connectionSecure = window.location.protocol === 'https:';
const selectedOrder = ref(null)
const selectedOrders = ref([]);
const showDialog = ref(false);
const disablePayButton = ref(false);
const credit_available = computed(() => {
    return props.credits.reduce((acc, credit) => acc + parseFloat(credit.amount_available) , 0).toFixed(2);
});

onUpdated(() => {
    if (props.orders_to_be_paid) {
        selectedOrders.value = props.orders_to_be_paid;
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
    axios.post(route('payments.checkout-multiple', { payment_method: 3 }), { orders : selectedOrders.value.map(o => o.id) })
        .then(response => {
            const stripe = Stripe(props.variables.stripe_key);
            stripe.redirectToCheckout({ sessionId: response.data.id });
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

const payWithCreditAvailable = () => {
    const form = useForm({
        orders : selectedOrders.value.map(o => o.id)
    });

    disablePayButton.value = true;

    form.post(route('payments.checkout-multiple', { payment_method : 1 }), {
        onSuccess: () => {
            showDialog.value = false;
            disablePayButton.value = false;
        },
        onError: () => {
            if(props.order)
                selectedOrder.value = props.order;
            disablePayButton.value = false;
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

const checkSelectedOrders = () => {
    selectedOrders.value = selectedOrders.value.filter(order => order.status == 0)
}

const get_checkout_title = computed(() => {
    if(selectedOrders.value.length == 1) {
        return `Pagamento menù: ${selectedOrders.value[0]?.menu?.name} - ${moment(selectedOrders.value[0]?.date).format('DD/MM')}`;
    } else if (selectedOrders.value.length > 1) {
        return `Pagamento di ${selectedOrders.value.length} ordini`;
    } else {
        return 'Nessun ordine selezionato';
    }
})
</script>

<template>
    <Head title="Ordini" />    

    <AuthenticatedLayout>
        <div class="content">
            <SuccessMessage />
            <ErrorMessage v-if="!showDialog" />

            <div class="alert alert-danger" v-if="!connectionSecure">
                <i class="fa fa-exclamation-triangle fa-2x"></i>
                <span class="ms-2">
                    Attenzione: il pagamento con metodo elettronico funziona solo quando il sito è in modalità sicura!<br>
                    Clicca <Link href="https://www.mensaranchibile.it" class="link-primary">qui</Link> per andare sul sito in versione sicura (HTTPS).
                </span>
            </div>

            <Dialog
                v-model:visible="showDialog"
                modal
                :header="get_checkout_title"
                :style="{ width: '50vw', minHeight: '46vh', maxHeight: '52vh' }"
            >
                <div class="container">
                    <div class="row py-1" v-if="Object.keys($page.props.errors).length">
                        <p class="text-warning" v-for="error in $page.props.errors" v-text="error" />
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th style="width: 40%">Menù</th>
                                        <td style="width: 60%" v-text="selectedOrders[0]?.menu?.name" v-if="selectedOrders.length <= 1" />
                                        <td style="width: 60%" v-else>
                                            <ul class="list-group">
                                                <li class="list-group-item" v-for="order in selectedOrders" :key="order.id">
                                                    <p class="p-0 m-0 d-flex justify-content-between align-items-center">
                                                        <span>
                                                            - {{ order.menu.name }} | {{ moment(order.order_date).format('DD/MM') }}
                                                        </span>
                                                        <button class="btn-link link-danger me-3" @click="selectedOrders = selectedOrders.filter(o => o.id !== order.id)">
                                                            <i class="fa fa-x"></i>
                                                        </button>
                                                    </p>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr v-if="selectedOrders.length == 1">
                                        <th style="width: 40%">Data</th>
                                        <td style="width: 60%" v-text="moment(selectedOrder?.order_date).format('DD/MM/YYYY')" />
                                    </tr>
                                    <tr>
                                        <th style="width: 40%">Totale</th>
                                        <td style="width: 60%" v-text="parseFloat(selectedOrders.reduce((acc, order) => acc + parseFloat(order.total_amount), 0)).toFixed(2) + ' €'" />
                                    </tr>
                                    <tr>
                                        <th style="width: 40%">Da pagare</th>
                                        <td style="width: 60%" v-text="parseFloat(selectedOrders.reduce((acc, order) => acc + parseFloat(order.to_be_paid), 0)).toFixed(2) + ' €'" />
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
                        <div class="row g-2" :class="{'justify-content-center' : credit_available == 0}">
                            <div class="col-md-6" @click.prevent="payWithStripe">
                                <button class="btn btn-card btn-lg w-100 gap-2">
                                    <i class="fa fa-credit-card me-1"></i>
                                    Paga con metodo elettronico
                                </button>
                            </div>
                            <div class="col-md-6" v-if="credit_available > 0" @click.prevent="payWithCreditAvailable" :disabled="disablePayButton">
                                <button class="btn btn-cash btn-lg w-100">
                                    <i class="fa fa-euro-sign me-1"></i>
                                    Paga con credito residuo
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </Dialog>
            
            <div class="w-100 d-md-none">
                <Link 
                    :href="route('orders.create')" 
                    class="btn btn-alt-primary w-100 py-2 mb-3"
                >
                    <i class="fa fa-plus me-1"></i>
                    Nuovo ordine
                </Link>
            </div>

            <BaseBlock contentClass="pb-3">
                <DataTable
                    striped-rows
                    :value="orders"
                    table-style="min-width: 50rem"
                    v-model:selection="selectedOrders"
                    selectionMode="multiple"
                    @update:selection="checkSelectedOrders"
                >
                    <template #empty>
                        <div class="p-4 text-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun ordine creato</p>
                        </div>
                    </template>

                    <template #header>
                        <div class="row">
                            <div class="col-md-4 offset-md-4">
                                <div class="fw-bold text-center">
                                    <h3>Ordini</h3>
                                </div>
                            </div>
                            <div class="col-md-4 text-end d-none d-md-block">
                                <button 
                                    class="btn btn-alt-success btn-sm me-2" 
                                    :disabled="selectedOrders.length == 0"
                                    @click="showDialog = true"
                                >
                                    <i class="fa fa-euro-sign me-1"></i>
                                    Paga ordini
                                </button>
                                <Link :href="route('orders.create')" class="btn btn-alt-primary btn-sm">
                                    <i class="fa fa-plus me-1"></i> Nuovo ordine
                                </Link>
                            </div>
                        </div>
                    </template>

                    <Column style="width: 10%" class="text-center">
                        <template #body="{ data }">
                            <Link
                                :href="route('orders.edit', data.id)"
                                class="btn btn-alt-info btn-sm d-none d-xl-inline"
                            >
                                <i class="fa fa-eye"></i>
                            </Link>

                            <button class="btn btn-alt-secondary btn-sm ms-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" v-if="data.status != 3 && (moment(data.created_at).format('YYYY-MM-DD') <= moment().format('YYYY-MM-DD'))">
                                ...
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <Link
                                        :href="route('orders.edit', data.id)"
                                        class="d-xl-none dropdown-item d-flex gap-2 align-items-center clickable" 
                                        style="font-size: 13px"
                                    >
                                        <button class="btn btn-alt-info btn-sm">
                                            <i class="fa fa-eye"></i>
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
                    <Column style="width: 20%" header="Giorno menù" field="menu.name">
                        <template #body="{data}">
                            <span v-text="`${data.menu.name} - ${moment(data.order_date).format('DD/MM')}`" />
                        </template>
                    </Column>
                    <Column style="width: 30%" header="Prodotti">
                        <template #body="{ data }">
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
                        </template>
                    </Column>
                    <Column style="width: 15%" header="Totale">
                        <template #body="{ data }">
                            <span v-text="parseFloat(data.total_amount).toFixed(2)" /> &euro;
                        </template>
                    </Column>
                    <Column style="width: 15%" header="Da pagare">
                        <template #body="{ data }">
                            <span v-text="parseFloat(data.to_be_paid).toFixed(2)" /> &euro;
                        </template>
                    </Column>
                    <Column style="width: 10%" header="Stato" field="status">
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
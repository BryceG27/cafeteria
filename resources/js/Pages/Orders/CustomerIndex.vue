<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import BaseBlock from "@/Components/BaseBlock.vue";

import { ref, computed, onUpdated, onMounted, watchEffect } from "vue"
import { Head, Link, useForm } from "@inertiajs/vue3";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Popover from 'primevue/popover'
import Divider from 'primevue/divider'
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

import Swal from 'sweetalert2';
import moment from 'moment';
import axios from "axios";

import "https://js.stripe.com/v3";

const toast = useToast();

const props = defineProps({
    auth: Object,
    variables : Object,
    credits : Array,
    orders_to_be_paid : Object,
    orders: Array,
    errors : Object,
    flash : Object
});

const connectionSecure = window.location.protocol === 'https:';
const op = ref(null);
const selectedOrders = ref([]);
const showDialog = ref(false);
const disablePayButton = ref(false);
const credit_available = computed(() => {
    return props.credits.reduce((acc, credit) => acc + parseFloat(credit.amount_available) , 0).toFixed(2);
});

const new_order = (event) => {
    op.value.toggle(event)
}

onUpdated(() => {
    if(props.orders_to_be_paid.length) {
        selectedOrders.value = props.orders_to_be_paid.filter(order => order.status == 0).filter(order => moment(order.order_date).isSameOrAfter(moment(), 'day'));
        showDialog.value = true;
    }
});

onMounted(() => {
    if(props.orders_to_be_paid.length) {
        selectedOrders.value = props.orders_to_be_paid.filter(order => order.status == 0).filter(order => moment(order.order_date).isSameOrAfter(moment(), 'day'));
        showDialog.value = true;
    } else {
        selectedOrders.value = props.orders
        checkSelectedOrders();
    }
})

watchEffect(() => {
    if(props.flash?.message)
        toast.add({ severity: 'success', summary: 'Dati aggiornati', detail: props.flash.message, life: 3000 });

    if(Object.keys(props.errors).length > 0) {
        Object.values(props.errors).forEach(error => {
            toast.add({ severity: 'error', summary: 'Riscontrato errore', detail: error, life: 3000 });
        })
    }
})

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
    });
}

const checkSelectedOrders = () => {
    const originalLength = selectedOrders.value.length;
    selectedOrders.value = selectedOrders.value.filter(order => order.status == 0).filter(order => moment(order.order_date).isSameOrAfter(moment(), 'day'));

    if(moment().format('HH:mm') >= '10:00') {
        selectedOrders.value = selectedOrders.value.filter(order => {
            return moment(order.order_date).isAfter(moment(), 'day')
        });

        if(originalLength != selectedOrders.value.length)
            toast.add({severity:'error', summary: 'Attenzione', detail:'Data e ora utili per il pagamento superati.', life: 7000});
    }
}

const get_checkout_title = computed(() => {
    if(selectedOrders.value.length == 1 && selectedOrders.value[0]?.menu_id != undefined)
        return `Pagamento menù: ${selectedOrders.value[0]?.menu?.name} - ${moment(selectedOrders.value[0]?.order_date).format('DD/MM')}`;
    else if (selectedOrders.value.length == 1 && selectedOrders.value[0]?.special_menu_id != undefined)
        return `Pagamento menù extra: ${selectedOrders.value[0]?.special_menu?.name} - ${moment(selectedOrders.value[0]?.order_date).format('DD/MM')}`;
    else if (selectedOrders.value.length > 1)
        return `Pagamento di ${selectedOrders.value.length} ordini`;
    else
        return 'Nessun ordine selezionato';
})

const payOrders = () => {
    if(selectedOrders.value.length == 0) {
        Swal.fire({
            icon: 'error',
            title: 'Errore',
            text: 'Seleziona almeno un ordine da pagare',
        });
        return;
    }

    showDialog.value = true;
}

const total_orders = computed(() => {
    return parseFloat(selectedOrders.value.reduce((acc, order) => acc + parseFloat(order.total_amount), 0)).toFixed(2)
})

const total_to_be_paid = computed(() => {
    return parseFloat(selectedOrders.value.reduce((acc, order) => acc + parseFloat(order.to_be_paid), 0)).toFixed(2)
})

</script>

<template>
    <Head title="Ordini" />

    
    <AuthenticatedLayout>
        <Toast />
        <div class="content">
            <ErrorMessage v-if="!showDialog" />

            <Popover ref="op">
                <Link
                    :href="route('orders.create')"
                    class="link-dark"
                >
                    Menù settimanale
                </Link>
                <hr>
                <Link
                    :href="route('orders.create.special-menu')"
                    class="link-dark"
                >
                    Menù extra
                </Link>
            </Popover>

            <div class="alert alert-danger" v-if="!connectionSecure">
                <i class="fa fa-exclamation-triangle fa-2x"></i>
                <span class="ms-2">
                    Attenzione: il pagamento con metodo elettronico funziona solo quando il sito è in modalità sicura!<br>
                    Clicca <a href="https://www.mensaranchibile.it" class="link-primary">qui</a> per andare sul sito in versione sicura (HTTPS).
                </span>
            </div>

            <Dialog
                v-model:visible="showDialog"
                modal
                :header="get_checkout_title"
                :style="{ width: '50rem', minHeight: '46vh', maxHeight: '60vh' }"
            >
                <div class="container">
                    <div class="row py-1" v-if="Object.keys($page.props.errors).length">
                        <p class="text-warning" v-for="error in $page.props.errors" v-text="error" />
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table">
                                <tbody>
                                    <template v-if="selectedOrders.length <= 1">
                                        <tr>
                                            <th style="width: 40%">Menù</th>
                                            <td style="width: 60%" v-text="selectedOrders[0]?.menu?.name" v-if="selectedOrders.length <= 1 && selectedOrders[0].menu" />
                                            <td style="width: 60%" v-text="selectedOrders[0]?.special_menu?.name" v-else-if="selectedOrders.length <= 1 && selectedOrders[0].special_menu" />
                                        </tr>
                                        <tr v-if="selectedOrders.length == 1">
                                            <th style="width: 40%">Data</th>
                                            <td style="width: 60%" v-text="moment(selectedOrders[0]?.order_date).format('DD/MM/YYYY')" />
                                        </tr>
                                    </template>
                                    <tr v-else>
                                        <td class="w-100" colspan="2">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center" style="width: 10%"></th>
                                                        <th style="width: 60%">Nome menù</th>
                                                        <th class="text-center" style="width: 30%">Giorno</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="order in selectedOrders" :key="order.id">
                                                        <td class="text-center">
                                                            <button class="btn btn-link link-danger me-3" @click="selectedOrders = selectedOrders.filter(o => o.id !== order.id)">
                                                                <i class="fa fa-minus"></i>
                                                            </button>
                                                        </td>
                                                        <td v-if="order.menu" v-text="order.menu.name" />
                                                        <td v-else-if="order.special_menu" v-text="order.special_menu.name" />
                                                        <td class="text-center" v-text="moment(order.order_date).format('DD/MM/YYYY')" />
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 40%">Totale</th>
                                        <td style="width: 60%" v-text="total_orders + ' €'" />
                                    </tr>
                                    <tr>
                                        <th style="width: 40%">Da pagare</th>
                                        <td style="width: 60%" v-text="total_to_be_paid + ' €'" />
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
                                <button class="btn btn-card btn-lg w-100 gap-2" :disabled="total_orders == 0">
                                    <i class="fa fa-credit-card me-1"></i>
                                    Paga con metodo elettronico
                                </button>
                            </div>
                            <div class="col-md-6" v-if="credit_available > 0" @click.prevent="payWithCreditAvailable" :disabled="disablePayButton">
                                <button class="btn btn-cash btn-lg w-100" :disabled="total_to_be_paid == 0">
                                    <i class="fa fa-euro-sign me-1"></i>
                                    Paga con credito residuo
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </Dialog>
            
            <div class="w-100 d-md-none">
                <button 
                    class="btn btn-alt-primary w-100 py-2 mb-3"
                    type="button"
                    @click="new_order($event)"
                >
                    <i class="fa fa-plus me-1"></i>
                    Nuovo ordine
                </button>
                <button 
                    class="btn btn-alt-success btn-sm w-100 py-2 mb-3" 
                    @click.prevent="payOrders"
                >
                    <i class="fa fa-euro-sign me-1"></i>
                    Paga ordini
                </button>
            </div>

            <BaseBlock contentClass="pb-3">
                <DataTable
                    striped-rows
                    :value="orders"
                    table-style="min-width: 50rem"
                    v-model:selection="selectedOrders"
                    selectionMode="multiple"
                    @update:selection="checkSelectedOrders"
                    dataKey="id"
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
                                    @click.prevent="payOrders"
                                >
                                    <i class="fa fa-euro-sign me-1"></i>
                                    Paga ordini
                                </button>
                                <!-- <Link :href="route('orders.create')" class="btn btn-alt-primary btn-sm">
                                    <i class="fa fa-plus me-1"></i> Nuovo ordine
                                </Link> -->
                                <button 
                                    class="btn btn-sm btn-alt-primary"
                                    type="button"
                                    @click="new_order($event)"
                                >
                                    <i class="fa fa-plus me-1"></i>
                                    Nuovo ordine
                                </button>
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
                                <li class="d-xl-none">
                                    <hr class="dropdown-divider"></hr>
                                </li>
                                <!-- <li v-if="data.status == 0">
                                    <div
                                        class="dropdown-item d-flex gap-2 align-items-center clickable" 
                                        style="font-size: 13px"
                                        @click="payOrder(data)"
                                    >
                                        <button class="btn btn-alt-success btn-sm" type="button">
                                            <i class="fa fa-dollar-sign"></i>
                                        </button>
                                        Effettua pagamento
                                    </div>
                                </li> -->
                               
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
                    <Column style="width: 15%" header="Nome menù" field="menu.name">
                        <template #body="{data}">
                            <span v-if="data.menu" v-text="`${data.menu?.name}`"/>
                            <span v-else-if="data.special_menu" v-text="`${data.special_menu?.name}`"/>
                        </template>
                    </Column>
                    <Column style="width: 10%" header="Validità del menù" field="data.order_date">
                        <template #body="{ data }">
                            {{ moment(data.order_date).format('DD/MM/YYYY') }}
                        </template>
                    </Column>
                    <Column style="width: 25%" header="Prodotti">
                        <template #body="{ data }">
                            <div class="row">
                                <div class="col-md-10 border rounded-2 px-0" v-if="data.menu">
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

                                    <Divider class="my-1" v-if="data.beverage" />

                                    <div class="row py-1" v-if="data.beverage">
                                        <div class="col-md-10 offset-md-1">
                                            <span class="fs-14" style="font-weight: 500;" v-text="data.beverage?.name" />
                                            <br>
                                            <em class="fs-12">Bibita</em>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-10 border rounded-2 px-0" v-else-if="data.special_menu">
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
.p-datatable .p-datatable-tbody > tr.p-highlight {
    background: #cff4fc !important;   /* bg-info-subtle di Bootstrap 5 */
    color: #055160 !important;        /* text-info */
    border-left: 4px solid #31d2f2 !important;
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
<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";

import OrdersTable from "./Components/OrdersTable.vue";
import PaymentsTable from "./Components/PaymentsTable.vue";
import CreditsTable from "./Components/CreditsTable.vue";

import { ref } from "vue";

import InputNumber from 'primevue/inputnumber';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';

const props = defineProps({
    auth : Object,
    customer : Object,
    order_statuses : Array,
});


const paymentModal = ref(false);

const paymentForm = useForm({
    amount: 0.00,
    payment_method_id : 1,
    notes: 'Pagamento in contanti',
    user_id: props.customer.id,
    status : 1
});

const updateOrderStatus = (event) => {
    const form = useForm({
        status: event.newData.status,
    });

    form.put(route('orders.update-status', { order : event.newData.id }), {
        preserveScroll: true,
    });
}

const submitPayment = () => {
    paymentForm.post(route('payments.store-by-admin'), {
        onSuccess: () => {
            paymentModal.value = false;
            paymentForm.reset();
        },
        preserveScroll: true,
    });
}

const denyPayment = () => {
    paymentModal.value = false;
    paymentForm.reset();
}


</script>
<template>
    <Head :title="`${customer.name} ${customer.surname}`" />

    <AuthenticatedLayout>
        
        <Dialog 
            v-model:visible="paymentModal" 
            :header="`Aggiungi pagamento per ${customer.name} ${customer.surname}`" 
            modal
            :style="{ width: '50rem' }"
        >
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <p class="text-muted">
                            Generazione credito dopo pagamento in contanti
                        </p>
                    </div>
                </div>
                <div class="row pb-3">
                    <div class="col-md-12">
                        <label for="" class="form-label">Importo</label><br>
                        <InputNumber 
                            v-model="paymentForm.amount" 
                            input-id="amount" 
                            placeholder="Importo"
                            mode="currency"
                            currency="EUR"
                            locale="it-IT"
                            class="w-100"
                            inputClass="w-100"
                        />
                    </div>
                </div>
                <div class="row pb-3">
                    <div class="col-md-12">
                        <label for="s" class="form-label">Note aggiuntive</label><br>
                        <Textarea 
                            v-model="paymentForm.notes" 
                            input-id="notes"
                            rows="3" 
                            placeholder="Note aggiuntive"
                            class="w-100"
                            inputClass="w-100"
                        />
                    </div>
                </div>
            </div>    
            <template #footer>
                <div class="text-end">
                    <button 
                        class="btn btn-sm btn-alt-success me-1"
                        @click="submitPayment"
                    >
                        <i class="fa fa-save me-1"></i>
                        Salva
                    </button>
                    <button 
                        class="btn btn-sm btn-alt-danger"
                        @click="denyPayment"
                    >
                        <i class="fa fa-x me-1"></i>
                        Chiudi
                    </button>
                </div>
            </template>
        </Dialog>

        <SuccessMessage />
        <ErrorMessage />

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
                <OrdersTable 
                    :customer="customer" 
                    :order_statuses="order_statuses" 
                    :auth="auth"
                    @on-status-change="updateOrderStatus"
                />
            </BaseBlock>
            <BaseBlock title="Pagamenti del cliente" content-class="pb-3">
                <template #options v-if="auth.user.user_group_id == 1">
                    <button 
                        class="btn btn-alt-primary btn-sm"
                        @click="paymentModal = true"    
                    >
                        <i class="fa fa-plus"></i>
                        Registra pagamento
                    </button>
                </template>
                <PaymentsTable 
                    :customer="customer" 
                    :auth="auth"
                />
            </BaseBlock>
            <BaseBlock title="Credito del cliente" content-class="pb-3">
                <CreditsTable
                    :customer="customer" 
                    :auth="auth"
                />
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>

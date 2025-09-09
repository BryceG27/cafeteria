<script setup>
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputNumber from 'primevue/inputnumber';
import FloatLabel from 'primevue/floatlabel';

import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

import moment from 'moment';

const props = defineProps({
    customer: Object,
});

const total_paid = computed(() => {
    return props.customer.payments.reduce((sum, payment) => sum + (payment.status == 1 && payment.order?.status != 2 ? parseFloat(payment.amount) : 0), 0);
});

const total_due = computed(() => {
    return props.customer.orders.filter(order => order.status != 2).reduce((sum, order) => sum + parseFloat(order.to_be_paid), 0);
});

const total_credit = computed(() => {
    return props.customer.credits.reduce((total, credit) => {
        return total + parseFloat(credit.amount_available);
    }, 0);
});

</script>
<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <DataTable
                    :value="customer.payments"
                    stripedRows
                    style="max-height: 50rem; overflow-y: auto"
                    paginator
                    :rows="15"
                    :rowsPerPageOptions="[15,25,50]"
                >
                    <template #empty>
                        <div class="p-4 text-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun pagamento effettuato</p>
                        </div>
                    </template>
                    <template #header>
                        <div class="row">
                            <div class="col-md-4 d-flex align-items-center justify-content-center">
                                <FloatLabel variant="on">
                                    <InputNumber
                                        v-model="total_paid" 
                                        inputId="total_paid"
                                        mode="currency"
                                        currency="EUR"
                                        locale="it-IT"
                                        readonly
                                    />
                                    <label for="total_paid">Totale pagato</label>
                                </FloatLabel>
                            </div>
                            <div class="col-md-4 d-flex align-items-center justify-content-center">
                                <FloatLabel variant="on">
                                    <InputNumber
                                        v-model="total_due" 
                                        inputId="total_due"
                                        mode="currency"
                                        currency="EUR"
                                        locale="it-IT"
                                        readonly
                                    />
                                    <label for="total_due">Totale da pagare</label>
                                </FloatLabel>
                            </div>
                            <div class="col-md-4 d-flex align-items-center justify-content-center">
                                <FloatLabel variant="on">
                                    <InputNumber
                                        v-model="total_credit" 
                                        inputId="total_credit"
                                        mode="currency"
                                        currency="EUR"
                                        locale="it-IT"
                                        readonly
                                    />
                                    <label for="total_credit">Credito</label>
                                </FloatLabel>
                            </div>
                        </div>
                    </template>

                    <Column field="order_id" style="width: 15%" header="Ordine #">
                        <template #body="{ data }">
                            <Link
                                :href="route('orders.edit', data.id)"
                                class="link-info"
                            >
                                {{ data.order_id }}
                            </Link>
                        </template>
                    </Column>
                    <Column field="amount" style="width: 15%" header="Totale">
                        <template #body="{ data }">
                            {{ parseFloat(data.amount).toFixed(2) }} &euro;
                        </template>
                    </Column>
                    <Column field="method.name" style="width: 30%" header="Metodo di pagamento" />
                    <Column field="created_at" style="width: 20%" header="Data">
                        <template #body="{ data }">
                            {{ moment(data.created_at).format('DD/MM/YYYY') }}
                        </template>
                    </Column>
                    <Column field="status" style="width: 20%" header="Stato">
                        <template #body="{ data }">
                            <span :class="`badge text-bg-${data.status_info.color}`" v-text="data.status_info.label" />
                            <span class="badge text-bg-danger ms-2" v-if="data.order && data.order.status == 2">
                                Ordine annullato
                            </span>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </div>
</template>

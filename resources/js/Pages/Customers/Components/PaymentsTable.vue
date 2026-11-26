<script setup>
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputNumber from 'primevue/inputnumber';
import FloatLabel from 'primevue/floatlabel';
import MultiSelect from 'primevue/multiselect';
import DatePicker from 'primevue/datepicker';

import { computed, ref } from "vue";
import moment from 'moment';
import { FilterMatchMode } from '@primevue/core/api';

const props = defineProps({
    customer: Object,
});

const filters = ref({
    status: { value: null, matchMode: FilterMatchMode.IN },
    "method.id": { value: null, matchMode: FilterMatchMode.IN },
    created_at: { value: null, matchMode: FilterMatchMode.DATE_IS }
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

const statuses = computed(() => {
    return props.customer.payments.map(payment => payment.status);
})

const paymentMethods = computed(() => {
    return props.customer.payments.map(payment => payment.method);
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

                    <Column field="orders" style="width: 15%" header="Ordine/i #">
                        <template #body="{ data }">
                            {{ data.orders.map(order => order.id).join(', ') }}
                            <!-- <Link
                                :href="route('orders.edit', data.id)"
                                class="link-info"
                            >
                                {{ data.order_id }}
                            </Link> -->
                        </template>
                    </Column>
                    <Column field="amount" style="width: 15%" header="Totale">
                        <template #body="{ data }">
                            {{ parseFloat(data.amount).toFixed(2) }} &euro;
                        </template>
                    </Column>
                    <Column filterField="method.id" field="method.name" style="width: 30%" header="Metodo di pagamento" :showFilterMatchModes="false" :showApplyButton="false">
                        <template #filter="{ filterModel, filterCallback }">
                            <MultiSelect
                                v-model="filterModel.value"
                                :options="paymentMethods"
                                optionLabel="name"
                                optionValue="id"
                                append-to="self"
                                class="w-100"
                                @update:modelValue="filterCallback"
                            />
                        </template>
                    </Column>
                    <Column field="created_at" style="width: 20%" header="Data" :showFilterMatchModes="false" :showApplyButton="false">
                        <template #body="{ data }">
                            {{ moment(data.created_at).format('DD/MM/YYYY') }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <DatePicker
                                v-model="filterModel.value"
                                append-to="self"
                                class="w-100"
                                @update:modelValue="filterCallback"
                            />
                        </template>
                    </Column>
                    <Column field="status" filterField="status.id" style="width: 20%" header="Stato" :showFilterMatchModes="false" :showApplyButton="false">
                        <template #body="{ data }">
                            <span :class="`badge text-bg-${data.status_info.color}`" v-text="data.status_info.label" />
                            <span class="badge text-bg-danger ms-2" v-if="data.order && data.order.status == 2">
                                Ordine annullato
                            </span>
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <MultiSelect
                                v-model="filterModel.value"
                                :options="paymentStatuses"
                                optionLabel="label"
                                optionValue="id"
                                append-to="self"
                                class="w-100"
                                @update:modelValue="filterCallback"
                            />
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </div>
</template>

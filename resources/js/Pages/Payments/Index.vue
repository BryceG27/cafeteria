<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import FloatLabel from 'primevue/floatlabel'
import InputNumber from 'primevue/inputnumber'

import moment from 'moment'

const props = defineProps({
    payments: Array,
    auth : Object,
})

</script>
<template>
    <Head title="Pagamenti" />

    <AuthenticatedLayout>
        <div class="content">
            <BaseBlock title="Pagamenti" content-class="pb-3">
                <DataTable
                    :value="payments"
                    stripedRows
                    style="max-height: 50rem; overflow-y: auto"
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
                            {{ data.order_id }}
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
                        </template>
                    </Column>
                </DataTable>
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>

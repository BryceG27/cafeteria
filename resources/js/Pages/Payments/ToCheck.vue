<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DataTable, Column } from 'primevue';
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    payments : Object,
})

const selectedPayments = ref([])

const submit = () => {
    const form = useForm({});

    form.delete(route('payments.to-delete', {
        payments : selectedPayments.value.map(({id}) => id)
    }))
}

</script>
<template>
    <AuthenticatedLayout>
        <DataTable
            :value="payments.data"
            striped-rows
            v-model:selection="selectedPayments" 
            selectionMode="multiple"
            :key="id"
            scrollable
            scroll-height="50rem"
        >
            <template #header>
                <div class="row justify-content-end">
                    <div class="col-md-2 text-end">
                        <button class="btn btn-sm btn-alt-danger" @click="submit">
                            <i class="fa fa-trash me-1"></i>
                            Elimina
                        </button>
                    </div>
                </div>
            </template>
            <Column header="ID" field="id" />
            <Column header="User">
                <template #body="{ data }">
                    <span>{{ data.customer.name }} {{ data.customer.surname }}</span>
                </template>
            </Column>
            <Column header="Orders ID">
                <template #body="{ data }">
                    <span v-for="order in data.orders" :key="order.id" v-text="`${order.id} `" />
                </template>
            </Column>
            <Column header="Payment Amount">
                <template #body="{ data }">
                    {{ data.amount }}
                </template>
            </Column>
            <Column header="Order Amount">
                <template #body="{ data }">
                    <span v-text="data.orders.reduce((acc, order) => acc + parseFloat(order.pivot.amount) ,0)" />
                </template>
            </Column>
        </DataTable>
    </AuthenticatedLayout>
</template>
<style scoped>
    
</style>
<!-- 
SELECT
	p.id,
	p.amount,
	p.user_id,
	op.order_id,
	op.payment_id,
	op.amount
FROM
	payments p
	LEFT JOIN order_payment op ON op.payment_id = p.id
WHERE
	p.id <= 7341
	AND op.id IS NOT NULL
ORDER BY
	p.id DESC
     -->
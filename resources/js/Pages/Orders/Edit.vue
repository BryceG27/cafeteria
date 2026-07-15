<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";
import OrderForm from './Components/OrderForm.vue';

const props = defineProps({
    menus : Array,
    order : Object,
    beverage : Array,
    auth : Object,
    errors : Object,
    statuses : Array,
})

const submit = () => {
    form.put(route('orders.update', props.order.id), {
        preserveScroll: true,
    });
}

const form = useForm({
    id : props.order.id,
    menu_id: props.order.menu_id,
    customer_id : props.order.customer_id,
    notes : props.order.notes,
    status : props.order.status,
    order_date : props.order.order_date,
    discount : props.order.discount,
    subtotal_amount : props.order.subtotal_amount,
    total_amount : props.order.total_amount,
    first_dish_id : props.order.first_dish_id,
    second_dish_id : props.order.second_dish_id,
    side_dish_id : props.order.side_dish_id,
    beverage_id : props.order.beverage_id
})

</script>
<template>
    <Head title="Modifica ordine" />

    <AuthenticatedLayout>
        <div class="content">
            <BaseBlock :title="`Modifica ordine ${auth.user.user_group_id != 3 ? ' | ' + order.customer.child : ''}`" class="mb-4">
                <OrderForm 
                    :form="form" 
                    :menus="menus" 
                    :beverage="beverage"
                    :order="order"
                    :auth="auth"
                    :errors="errors"
                    :statuses="statuses"
                    @submit="submit"
                />
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>

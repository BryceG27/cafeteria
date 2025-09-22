<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";
import { onMounted, watch, reactive, computed } from "vue";
import OrderForm from './Components/OrderForm.vue'

import Accordion from 'primevue/accordion';
import AccordionPanel from 'primevue/accordionpanel';
import AccordionHeader from 'primevue/accordionheader';
import AccordionContent from 'primevue/accordioncontent';

const props = defineProps({
    menus : Array,
    order : Object,
    auth : Object,
    errors : Object,
    statuses : Array,
})

const submit = () => {
    form.post(route('orders.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

const form = useForm({
    orders: [
        ...(props.order?.orders || [])
    ]
})

const orderDetail = reactive({
    menu_id: null,
    customer_id : props.auth.user.id,
    description : '',
    notes : '',
    status : 0,
    order_date : null,
    discount : 0,
    subtotal_amount : 0,
    total_amount : 0,
    first_dish_id : null,
    second_dish_id : null,
    side_dish_id : null,
})

watch(() => orderDetail.menu_id, (newMenuId) => {
    const selectedMenu = props.menus.find(menu => menu.id === newMenuId);

    if (selectedMenu) {
        orderDetail.subtotal_amount = selectedMenu.price;
        orderDetail.total_amount = selectedMenu.price - (selectedMenu.price * (orderDetail.discount / 100));
        orderDetail.order_date = selectedMenu.validity_date;
        orderDetail.description = selectedMenu.name;
    } else {
        orderDetail.subtotal_amount = 0;
        orderDetail.total_amount = 0;
        orderDetail.order_date = null;
        orderDetail.description = selectedMenu.name;
    }
});

onMounted(() => {
    if(props.menus.length == 1) {
        orderDetail.menu_id = props.menus[0].id;
        orderDetail.subtotal = props.menus[0].price;
        orderDetail.total = props.menus[0].price;
        orderDetail.order_date = props.menus[0].validity_date;
        orderDetail.description = props.menus[0].name;
    }
})

const add_to_order = () => {
    form.orders.push({...orderDetail});

    orderDetail.menu_id = null;
    orderDetail.subtotal_amount = 0;
    orderDetail.total_amount = 0;
    orderDetail.order_date = null;
    orderDetail.first_dish_id = null;
    orderDetail.second_dish_id = null;
    orderDetail.side_dish_id = null;
    orderDetail.notes = '';
    orderDetail.discount = 0;
    orderDetail.description = '';
}

</script>
<template>
    <Head title="Nuovo ordine" />

    <AuthenticatedLayout>
        <div class="content">
            <BaseBlock title="Inserisci nuovo ordine" class="mb-4">
                <OrderForm 
                    :form="form" 
                    :menus="menus" 
                    :order="order"
                    :auth="auth"
                    :errors="errors"
                    @submit="submit"
                    @add_to_order="add_to_order"
                    :statuses="statuses"
                    :orderDetail="orderDetail"
                />
            </BaseBlock>

            <BaseBlock title="Menù aggiunti" class="mb-4">
                {{ form.orders }}
                <Accordion v-if="form.orders.length > 0">
                    <AccordionPanel v-for="(order, i) in form.orders" :key="order.menu_id" :value="i">
                        <AccordionHeader v-text="order.description" />
                        <AccordionContent>
                            Prova
                        </AccordionContent>
                    </AccordionPanel>
                </Accordion>
                <div v-else class="text-center p-4">
                    Nessun menù aggiunto
                </div>
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>
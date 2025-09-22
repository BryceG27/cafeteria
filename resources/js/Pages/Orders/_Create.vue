<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";
import OrderForm from './Components/OrderForm.vue';
import { onMounted, watch } from "vue";

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
    menu_id: null,
    customer_id : props.auth.user.id,
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

watch(() => form.menu_id, (newMenuId) => {
    const selectedMenu = props.menus.find(menu => menu.id === newMenuId);

    if (selectedMenu) {
        form.subtotal_amount = selectedMenu.price;
        form.total_amount = selectedMenu.price - (selectedMenu.price * (form.discount / 100));
        form.order_date = selectedMenu.validity_date;
    } else {
        form.subtotal_amount = 0;
        form.total_amount = 0;
        form.order_date = null;
    }
});

onMounted(() => {
    if(props.menus.length == 1) {
        form.menu_id = props.menus[0].id;
        form.subtotal = props.menus[0].price;
        form.total = props.menus[0].price;
        form.order_date = props.menus[0].validity_date;
    }
})

</script>
<template>
    <Head title="Nuovo ordine" />

    <AuthenticatedLayout>
        <div class="content">
            <BaseBlock title="Inserisci nuovo ordine" class="mb-4">
                <!-- <template #options>
                    <button
                        class="btn btn-alt-success btn-sm"
                        @click="submit"
                    >
                        <i class="fa fa-save me-1"></i>
                        Salva
                    </button>
                    <Link
                        class="btn btn-alt-danger btn-sm"
                        :href="route('orders.index')"
                    >
                        <i class="fa fa-x me-1"></i>
                        Annulla
                    </Link>
                </template> -->
                <OrderForm 
                    :form="form" 
                    :menus="menus" 
                    :order="order"
                    :auth="auth"
                    :errors="errors"
                    @submit="submit"
                    :statuses="statuses"
                />

            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>
<style>
    
</style>
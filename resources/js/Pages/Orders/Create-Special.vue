<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { watch } from 'vue'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SpecialOrderForm from './Components/SpecialOrderForm.vue';

const props = defineProps({
    auth : Object,
    menus : Object,
    order : Object,
    statuses : Array,
    beverage : Array,
    auth : Object,
    errors : Object,
    statuses : Array
})

const form = useForm({
    special_menu_id: null,
    customer_id : props.auth.user.id,
    notes : '',
    status : 0,
    order_date : null,
    subtotal_amount : 0,
    total_amount : 0,
    first_dish_id : null
})

const submit = () => {
    form.post(route('orders.store.special-menu'), {
        preserveScroll : true
    })
}

watch(form, (value) => {
    if(form.special_menu_id != null) {
        let selected_menu = props.menus.find(({id}) => form.special_menu_id === id);
        form.subtotal_amount = selected_menu.price;
        form.total_amount = form.subtotal_amount;
    }
})

</script>
<template>
    <Head title="Crea un menù extra" />

    <AuthenticatedLayout>
        <div class="content">
            <BaseBlock title="Inserisci nuovo ordine" class="mb-4 sticky-top">
                <form @submit.prevent="submit">
                    <SpecialOrderForm
                        :auth="auth"
                        :form="form" 
                        :menus="menus"
                        :statuses="statuses"
                        :beverage="beverage"
                        :errors="errors"
                    />
                </form>
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>
<style scoped>
    
</style>
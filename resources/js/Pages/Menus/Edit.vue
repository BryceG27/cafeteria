<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";
import MenuFrom from './Components/MenuForm.vue';
import { onMounted, watch } from "vue";

const props = defineProps({
    auth : Object,
    menu : Object,
    categories : Array,
    products : Array,
    errors : Object,
})

const submit = () => {
    form.patch(route('menus.update', { menu : props.menu.id }), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

const form = useForm({
    id : props.menu.id || null,
    name : props.menu.name || '',
    start_date : props.menu.start_date || '',
    end_date : props.menu.end_date || '',
    validity_date : props.menu.validity_date || '',
    price : parseFloat(props.menu.price || 0),
    second_price : parseFloat(props.menu.second_price || 0),
    is_active : props.menu.is_active || 1, // Default to active
    description : props.menu.description || '',
    products : props.menu.products,
})

watch(() => form.validity_date, (newVal) => {
    console.log(newVal);
    console.log(form.validity_date)
    
}, { deep: true })

onMounted(() => {
    form.products.map(p => p.quantity = p.pivot.quantity)
})

</script>
<template>
    <Head title="Inserimento menu" />

    <AuthenticatedLayout>
        <div class="content">
            <BaseBlock title="Crea un menu" contentClass="pb-3">
                <template #options>
                    <button
                        class="btn btn-alt-success btn-sm"
                        @click="submit"
                    >
                        <i class="fa fa-save me-1">
                        </i>
                        Salva
                    </button>
                    <Link
                        class="btn btn-alt-danger btn-sm"
                        :href="route('menus.index')"
                    >
                        <i class="fa fa-x me-1"></i>
                        Annulla
                    </Link>
                </template>

                <MenuFrom 
                    :form="form"
                    :categories="categories"
                    :products="products"
                    :auth="auth"
                    :errors="errors"
                    @submit="submit"
                />
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>
<style>
    
</style>
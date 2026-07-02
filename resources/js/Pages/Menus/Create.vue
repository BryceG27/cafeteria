<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

import BaseBlock from "@/Components/BaseBlock.vue";
import MenuFrom from './Components/MenuForm.vue';

const props = defineProps({
    auth : Object,
    menu : Object,
    menus : Array,
    categories : Array,
    products : Array,
    product_types : Array,
    errors : Object,
});

const submit = () => {
    form.post(route('menus.store'), {
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
    price : props.menu.price || 0,
    second_price : props.menu.second_price || 6.00,
    is_active : props.menu.is_active || 1, // Default to active
    description : props.menu.description || 'Ogni menù include acqua naturale e la frutta di stagione.',
    products : props.menu.products ? props.menu.products.map(p => p.id) : [],
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
                        <i class="fa fa-times me-1"></i>
                        Annulla
                    </Link>
                </template>

                <MenuFrom 
                    :form="form"
                    :categories="categories"
                    :products="products"
                    :product_types="product_types"
                    :auth="auth"
                    :errors="errors"
                    :menus="menus"
                    @submit="submit"
                />
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>
<style>
    
</style>
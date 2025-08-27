<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";
import ProductForm from './Components/ProductForm.vue'

import Swal from "sweetalert2";

const props = defineProps({
    auth : Object,
    product : Object,
    categories : Array,
    types : Array,
    errors : Object,
});

const submit = () => {
    if (form.price == 0 || form.price === null) {
        Swal.fire({
            title: 'Il prezzo è zero',
            text: "Sei sicuro di voler continuare?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sì, continua',
            cancelButtonText: 'Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                form.post(route('products.store'), {
                    preserveScroll: true,
                    onSuccess: () => form.reset(),
                });
            }
        });
        return;
    } else {
        form.post(route('products.store'), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    }
}

const form = useForm({
    id : props.product.id || null,
    name : props.product.name || '',
    description : props.product.description || '',
    category_id : null, // Default to first category if available
    is_active : props.product.is_active || 1, // Default to active
    product_type_id : null,
    image : null,
});

</script>
<template>
    <Head title="Inserimento prodotto" />

    <AuthenticatedLayout>
        <div class="content">
            <BaseBlock title="Inserimento prodotto" contentClass="pb-3">
                <template #options>
                    <button 
                        class="btn btn-alt-success btn-sm"
                        @click="submit"
                    >
                        <i class="fa fa-save me-1"></i>
                        Salva
                    </button>
                    <Link 
                        class="btn btn-alt-danger btn-sm"
                        :href="route('products.index')"
                    >
                        <i class="fa fa-x me-1"></i>
                        Annulla
                    </Link>
                </template>

                <ProductForm 
                    :form="form"
                    :categories="categories"
                    :types="types"
                    :auth="auth"
                    :errors="errors"
                    @submit="submit"
                />
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>
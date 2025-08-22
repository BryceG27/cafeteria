<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";
import CategoryForm from './Components/CategoryForm.vue';

const props = defineProps({
    auth : Object,
    category : Object,
    errors : Object,
});

const form = useForm({
    id : props.category.id || null,
    name : props.category.name || '',
    description : props.category.description || '',
    is_active : props.category.is_active || 1, // Default to active
});

const submit = () => {
    form.put(route('categories.update', { category : props.category.id }), {
        preserveScroll: true,
    });
}

</script>
<template>
    <Head title="Modifica categoria" />

    <AuthenticatedLayout>
        <div class="content">
            <BaseBlock :title="`Modifica ${category.name}`" contentClass="pb-3">
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
                        :href="route('categories.index')"
                    >
                        <i class="fa fa-x me-1"></i>
                        Annulla
                    </Link>
                </template>

                <CategoryForm 
                    :form="form"
                    :auth="auth"
                    :errors="errors"
                    @submit="submit"
                />

                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="" class="form-label">Prodotti associati</label>
                            <div style="max-height: 200px; min-height: 200px; overflow-y: auto;" class="border p-3">
                                <ul class="list-group" v-if="category.products && category.products.length > 0">
                                    <li 
                                        v-for="product in category.products" 
                                        :key="`product-${product.id}`" 
                                        class="list-group-item d-flex justify-content-between align-items-center"
                                    >
                                        {{ product.name }}
                                    </li>
                                </ul>
                                <div v-else class="d-flex justify-content-center h-100 text-muted py-5">
                                    Nessun prodotto associato a questa categoria.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>
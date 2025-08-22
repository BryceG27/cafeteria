<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";
import CategoryForm from './Components/CategoryForm.vue';

const props = defineProps({
    auth : Object,
    category : Array,
    errors : Object,
});

const submit = () => {
    form.post(route('categories.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

const form = useForm({
    id : props.category.id || null,
    name : props.category.name || '',
    description : props.category.description || '',
    is_active : props.category.is_active || 1, // Default to active
});

</script>
<template>
    <Head title="Inserimento categoria" />

    <AuthenticatedLayout>
        <div class="content">
            <BaseBlock title="Inserimento categoria" contentClass="pb-3">
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
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>
<style>
    
</style>
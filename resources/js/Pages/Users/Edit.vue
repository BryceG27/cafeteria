<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";
import UserForm from './Components/UserForm.vue';

const props = defineProps({
    auth : Object,
    user : Object,
    user_groups : Array,
    errors : Object,
});

const submit = () => {
    form.put(route('users.update', form.id), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

const form = useForm({
    id : props.user.id,
    name : props.user.name || '',
    surname : props.user.surname || '',
    email : props.user.email || '',
    user_group_id : props.user.user_group_id || 2, // Default to admin group
    is_active : props.user.is_active || 1, // Default to active
    password : '',
    password_confirmation : '',
});

</script>
<template>
    <Head :title="`Modifica utente - ${user.name} ${user.surname}`" />

    <AuthenticatedLayout>
        <div class="content">
            <BaseBlock title="Inserimento utente" contentClass="pb-3">
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
                        :href="route('users.index')"
                    >
                        <i class="fa fa-x me-1"></i>
                        Annulla
                    </Link>
                </template>

                <UserForm 
                    :form="form"
                    :user-groups="user_groups"
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
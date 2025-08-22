<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

const props = defineProps({
    categories: Array,
    auth: Object,
});

</script>
<template>
    <Head title="Categorie Prodotti" />

    <AuthenticatedLayout>
        <div class="content">
            <SuccessMessage />
            <ErrorMessage />

            <BaseBlock title="Categorie Prodotti" contentClass="pb-3">
                <template #options>
                    <Link 
                        class="btn btn-alt-primary btn-sm"
                        :href="route('categories.create')"
                        v-show="auth.user.user_group_id == 1"
                    >
                        <i class="fa fa-plus me-1"></i>
                        Nuova
                    </Link>
                </template>

                <DataTable
                    stripedRows
                    :value="categories"
                >
                    <Column field="id" class="text-center" v-if="auth.user.user_group_id == 1">
                        <template #body="{ data }">
                            <Link 
                                :href="route('categories.toggle-active', { category : data.id})"
                                method="put"
                                as="button"
                                class="btn btn-sm"
                                :class="!data.is_active ? 'btn-alt-success' : 'btn-alt-warning'"
                            >
                                <i class="fa" :class="!data.is_active ? 'fa-play' : 'fa-pause'"></i>
                            </Link>
                            <Link 
                                :href="route('categories.edit', { category : data.id} )"
                                class="btn btn-sm btn-alt-info ms-2"
                            >
                                <i class="fa fa-pencil"></i>
                            </Link>
                        </template>
                    </Column>
                    <Column field="name" header="Nome" />
                    <Column field="description" header="Descrizione" />
                    <Column field="is_active" header="Stato">
                        <template #body="{ data }">
                            <span v-if="data.is_active" class="badge bg-success">Attivo</span>
                            <span v-else class="badge bg-danger">Inattivo</span>
                        </template>
                    </Column>
                </DataTable>
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>
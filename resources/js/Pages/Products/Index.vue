<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

const props = defineProps({
    products: Array,
    auth: Object,
});

</script>
<template>
    <Head title="Prodotti" />
    
    <AuthenticatedLayout>
        <div class="content">
            <SuccessMessage />
            <ErrorMessage />

            <BaseBlock title="Prodotti" contentClass="pb-3">
                <template #options>
                    <Link
                        class="btn btn-alt-primary btn-sm"
                        :href="route('products.create')"
                        v-show="auth.user.user_group_id == 1"
                    >
                        <i class="fa fa-plus me-1"></i>
                        Nuovo
                    </Link>
                </template>

                <DataTable
                    stripedRows
                    :value="products"
                >
                    <template #empty>
                        <div class="p-4 text-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun prodotto inserito</p>
                        </div>
                    </template>
                    <Column class="text-center">
                        <template #body="{ data }">
                            <Link 
                                class="btn btn-alt-danger btn-sm"
                                :href="route('products.toggle-active', { product : data.id })"
                                as="button"
                                method="put"
                                :class="!data.is_active ? 'btn-alt-success' : 'btn-alt-warning'"
                            >
                                <i class="fa" :class="!data.is_active ? 'fa-play' : 'fa-pause'"></i>
                            </Link>
                            <Link 
                                class="btn btn-alt-info btn-sm ms-2"
                                :href="route('products.edit', { product : data.id })"
                                v-if="auth.user.user_group_id == 1"
                            >
                                <i class="fa fa-pencil"></i>
                            </Link>
                        </template>
                    </Column>
                    <Column field="name" header="Nome" />
                    <Column field="description" header="Descrizione" />
                    <Column field="category.name" header="Categoria" />
                    <Column field="price" header="Prezzo">
                        <template #body="{ data }">
                            &euro; <span v-text="`${data.price.toFixed(2)}`" :class="{ 'text-danger' : data.price == 0 }" />  
                        </template>
                    </Column>
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
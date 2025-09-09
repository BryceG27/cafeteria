<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";
import { reactive, computed } from "vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';

const props = defineProps({
    products: Array,
    auth: Object,
});

const filters = reactive({
    name: '',
});

const filteredProducts = computed(() => {
    return props.products.filter(product => {
        return filters.name != '' ? product.name.toLowerCase().includes(filters.name.toLowerCase()) : true;
    });
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
                    :value="filteredProducts"
                    paginator
                    :rows="10"
                    :rowsPerPageOptions="[10,25,50]"
                >
                    <template #empty>
                        <div class="p-4 text-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun prodotto inserito</p>
                        </div>
                    </template>
                    <template #header>
                        <div class="d-flex align-items-center justify-content-end">
                            <label class="form-label">Filtra per nome:</label>
                            <InputText 
                                v-model="filters.name" 
                                placeholder="Cerca per nome" 
                                class="ms-2"
                            />
                        </div>
                    </template>
                    <Column class="text-center">
                        <template #body="{ data }">
                            <Link 
                                class="btn btn-sm"
                                :class="!data.is_active ? 'btn-alt-success' : 'btn-alt-warning'"
                                :href="route('products.toggle-active', { product : data.id })"
                                as="button"
                                method="put"
                            >
                                <i class="fa" :class="!data.is_active ? 'fa-play' : 'fa-pause'"></i>
                            </Link>
                            <!-- <Link 
                                class="btn btn-alt-info btn-sm ms-2"
                                :href="route('products.edit', { product : data.id })"
                                v-if="auth.user.user_group_id == 1"
                            >
                                <i class="fa fa-pencil"></i>
                            </Link> -->
                            <button class="btn btn-alt-secondary btn-sm ms-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                ...
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <Link
                                        class="dropdown-item d-flex gap-2 align-items-center" style="font-size: 13px"
                                        :href="route('products.edit', { product : data.id })"
                                        v-if="auth.user.user_group_id == 1"
                                    >
                                        <button class="btn btn-sm btn-alt-info" type="button">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        Modifica
                                    </Link>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <Link
                                        class="dropdown-item d-flex gap-2 align-items-center" style="font-size: 13px"
                                        :href="route('products.destroy', { product : data.id })"
                                        method="delete"
                                        as="button"
                                        v-if="auth.user.user_group_id == 1"
                                    >
                                        <button class="btn btn-sm btn-alt-danger" type="button">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        Cancella
                                    </Link>
                                </li>
                            </ul>
                        </template>
                    </Column>
                    <Column field="name" header="Nome" />
                    <Column field="description" header="Descrizione" />
                    <Column field="type.name" header="Tipo di pasto" />
                    <Column field="category.name" header="Categoria" />
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
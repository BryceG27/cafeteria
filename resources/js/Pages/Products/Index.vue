<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import BaseBlock from "@/Components/BaseBlock.vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import MultiSelect from "primevue/multiselect";

import { FilterMatchMode } from '@primevue/core/api'
import { Head, Link } from "@inertiajs/vue3";
import { ref, computed } from 'vue';

const props = defineProps({
    auth: Object,
    categories : Array,
    products: Array,
    types : Array,
});

const filters = ref({
    name: { value: null, matchMode: FilterMatchMode.CONTAINS},
    type: { value: null, matchMode: FilterMatchMode.IN},
    description: { value: null, matchMode: FilterMatchMode.CONTAINS},
    category: { value: null, matchMode: FilterMatchMode.IN},
    status: { value: null, matchMode: FilterMatchMode.EQUALS}
})

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
                    paginator
                    :rows="10"
                    v-model:filters="filters"
                    :rowsPerPageOptions="[10,25,50]"
                    filterDisplay="row"
                >
                    <template #empty>
                        <div class="p-4 text-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun prodotto inserito</p>
                        </div>
                    </template>
                    <Column style="width: 5%" class="text-center">
                        <template #body="{ data }">
                            <button class="btn btn-width btn-alt-secondary btn-sm ms-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                ...
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <Link
                                        class="dropdown-item d-flex gap-2 align-items-center" style="font-size: 13px"
                                        :href="route('products.edit', { product : data.id })"
                                        v-if="auth.user.user_group_id == 1"
                                    >
                                        <button class="btn btn-width btn-sm btn-alt-info" type="button">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        Modifica
                                    </Link>
                                </li>
                                <li>
                                    <Link 
                                        class="dropdown-item d-flex gap-2 align-items-center"
                                        :href="route('products.toggle-active', { product : data.id })"
                                        style="font-size: 13px"
                                        as="button"
                                        method="put"
                                    >
                                        <button class="btn btn-width btn-sm" :class="!data.is_active ? 'btn-alt-success' : 'btn-alt-warning'" type="button">
                                            <i class="fa" :class="!data.is_active ? 'fa-play' : 'fa-pause'"></i>
                                        </button>
                                        <span v-text="!data.is_active ? 'Attiva' : 'Disattiva'" />
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
                                        <button class="btn btn-width btn-sm btn-alt-danger" type="button">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        Cancella
                                    </Link>
                                </li>
                            </ul>
                        </template>
                    </Column>
                    <Column style="width: 25%" field="name" header="Nome" :showFilterMenu="false">
                        <template #filter="{ filterModel, filterCallback }">
                            <div class="d-flex align-items-center justify-content-around">
                                <InputText v-model="filterModel.value" @input="filterCallback()" class="w-75" />
                                <button class="btn btn-link link-danger" type="button" @click="filterModel.value = null, filterCallback()">
                                    <i class="fa fa-x"></i>
                                </button>
                            </div>
                        </template>
                    </Column>
                    <Column style="width: 35%" field="description" header="Descrizione" :showFilterMenu="false">
                        <template #filter="{ filterModel, filterCallback }">
                            <div class="d-flex align-items-center justify-content-around">
                                <InputText v-model="filterModel.value" @input="filterCallback()" class="w-75" />
                                <button class="btn btn-link link-danger" type="button" @click="filterModel.value = null, filterCallback()">
                                    <i class="fa fa-x"></i>
                                </button>
                            </div>
                        </template>
                    </Column>
                    <Column style="width: 15%" filterField="type" field="type.name" header="Tipo di pasto" :showFilterMenu="false">
                        <template #filter="{ filterModel, filterCallback }">
                            <div class="d-flex align-items-center justify-content-around">
                                <MultiSelect 
                                    :options="types"
                                    optionLabel="name"
                                    v-model="filterModel.value"
                                    @change="filterCallback()"
                                    class="w-75"
                                />
                                <button class="btn btn-link link-danger" type="button" @click="filterModel.value = null, filterCallback()">
                                    <i class="fa fa-x"></i>
                                </button>
                            </div>
                        </template>
                    </Column>
                    <Column style="width: 5%" field="is_active" header="Stato">
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

<style scoped>
.btn-width {
    width: 2rem
}
</style>
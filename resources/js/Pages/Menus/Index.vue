<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

import moment from 'moment';

const props = defineProps({
    menus: Array,
    auth: Object,
});
</script>
<template>
    <Head title="Menu" />

    <AuthenticatedLayout>
        <div class="content">
            <SuccessMessage />
            <ErrorMessage />

            <BaseBlock title="Menu" contentClass="pb-3">
                <template #options>
                    <Link
                        class="btn btn-alt-primary btn-sm"
                        :href="route('menus.create')"
                        v-show="auth.user.user_group_id == 1"
                    >
                        <i class="fa fa-plus me-1"></i>
                        Nuovo
                    </Link>
                </template>
                <DataTable
                    stripedRows
                    :value="menus"
                >
                    <template #empty>
                        <div class="p-4 text-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun menu inserito</p>
                        </div>
                    </template>

                    <Column>
                        <template #body="{ data }">
                            <Link 
                                :href="route('menus.toggle-active', data.id)"
                                method="put"
                                as="button"
                                class="btn btn-sm"
                                :class="!data.is_active ? 'btn-alt-success' : 'btn-alt-warning'"
                            >
                                <i class="fa" :class="!data.is_active ? 'fa-play' : 'fa-pause'"></i>
                            </Link>
                            <Link 
                                :href="route('menus.edit', data.id)"
                                class="btn btn-sm btn-alt-info ms-2"
                                v-if="data.user_group_id == 2"
                            >
                                <i class="fa fa-pencil"></i>
                            </Link>
                        </template>
                    </Column>
                    <Column field="description" header="Descrizione" />
                    <Column field="products" header="Prodotti">
                        <template #body="{ data }">
                                
                        </template>
                    </Column>
                    <Column field="price" header="Prezzo">
                        <template #body="{ data }">
                            {{ data.price.toFixed(2) }} €
                        </template>
                    </Column>
                    <Column field="start_date" header="Valido dal">
                        <template #body="{ data }">
                            {{ moment(data.start_date).format('DD/MM/YYYY') }}
                        </template>
                    </Column>
                    <Column field="end_date" header="Valido al">
                        <template #body="{ data }">
                            {{ moment(data.end_date).format('DD/MM/YYYY') }}
                        </template>
                    </Column>
                </DataTable>
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>
<style>
    
</style>
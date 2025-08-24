<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

import moment from 'moment';
import Swal from 'sweetalert2';

const props = defineProps({
    menus: Array,
    auth: Object,
});

const deleteMenu = (id) => {
    Swal.fire({
        title: 'Sei sicuro?',
        text: "Non potrai tornare indietro!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, cancella!',
        cancelButtonText: 'Annulla'
    }).then((result) => {
        if (result.isConfirmed) {
            Inertia.delete(route('menus.destroy', id), {
                onSuccess: () => {
                    Swal.fire(
                        'Cancellato!',
                        'Il menu è stato cancellato.',
                        'success'
                    )
                }
            });
        }
    })
}
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

                    <Column style="width: 10%" class="text-center">
                        <template #body="{ data }">
                            <div class="d-flex align-items-center justify-content-center">

                                <Link 
                                    :href="route('menus.toggle-active', data.id)"
                                    method="put"
                                    as="button"
                                    class="btn btn-sm me-2"
                                    :class="!data.is_active ? 'btn-alt-success' : 'btn-alt-warning'"
                                >
                                    <i class="fa" :class="!data.is_active ? 'fa-play' : 'fa-pause'"></i>
                                </Link>
                                <button class="btn btn-alt-secondary btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    ...
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <Link
                                            class="dropdown-item d-flex gap-2 align-items-center" style="font-size: 13px"
                                            :href="route('menus.edit', data.id)"
                                        >
                                            <button class="btn btn-sm btn-alt-info" type="button">
                                                <i class="nav-main-link-icon fa fa-pencil"></i>
                                            </button>
                                            Modifica menu
                                        </Link>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <div class="dropdown-item d-flex gap-2 align-items-center" style="font-size: 13px"
                                        >
                                            <button class="btn btn-alt-danger btn-sm" @click="deleteMenu(data.id)" type="button">
                                                <i class="fa fa-trash nav-main-link-icon"></i>
                                            </button>
                                            Cancella menu
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </template>
                    </Column>
                    <Column style="width: 10%" field="description" header="Descrizione" />
                    <Column style="width: 10%" field="products" header="Prodotti">
                        <template #body="{ data }">
                            <div style="max-height: 2.5rem" v-if="data.products">
                                <ul class="list-group">
                                    <li class="list-group-item" v-for="product in data.products" :key="product.id" v-text="product.name" />
                                </ul>
                            </div>
                        </template>
                    </Column>
                    <Column style="width: 10%" field="price" header="Prezzo">
                        <template #body="{ data }">
                            <!-- {{ data.price.toFixed(2) }} € -->
                        </template>
                    </Column>
                    <Column style="width: 10%" field="start_date" header="Valido dal">
                        <template #body="{ data }">
                            {{ moment(data.start_date).format('DD/MM/YYYY') }}
                        </template>
                    </Column>
                    <Column style="width: 10%" field="end_date" header="Valido al">
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
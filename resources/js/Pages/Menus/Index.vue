<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";
import { ref } from "vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

import moment from 'moment';
import Swal from 'sweetalert2';

const props = defineProps({
    menus: Array,
    auth: Object,
});

const expandedRows = ref(null);

const deleteMenu = (id) => {
    const form = useForm({});
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
            form.delete(route('menus.destroy', { menu : id}));
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
                    v-model:expandedRows="expandedRows"
                >
                    <template #empty>
                        <div class="p-4 text-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun menù inserito</p>
                        </div>
                    </template>
                    <template #expansion="{ data }">
                        <div class="p-4">
                            <h5>Prodotti nel menu</h5>
                            <DataTable
                                :value="data.products"
                                responsiveLayout="scroll"
                                stripedRows
                                style="max-height: 15rem; overflow-y: auto;"
                            >
                                <Column header="Nome" field="name" />
                                <Column header="Tipo" field="type.name" />
                                <Column header="Quantità" field="pivot.quantity"></Column>
                            </DataTable>
                        </div>
                    </template>
                    <Column expander style="width: 1%" />
                    <Column style="width: 5%" class="text-center">
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
                                            class="dropdown-item d-flex gap-2 align-items-center clickable" 
                                            style="font-size: 13px"
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
                                        <div 
                                            class="dropdown-item d-flex gap-2 align-items-center clickable" 
                                            style="font-size: 13px" 
                                            @click="deleteMenu(data.id)"
                                        >
                                            <button class="btn btn-alt-danger btn-sm" type="button">
                                                <i class="fa fa-trash nav-main-link-icon"></i>
                                            </button>
                                            Cancella menu
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </template>
                    </Column>
                    <Column style="width: 15%" field="name" header="Nome" />
                    <Column style="width: 14%" field="price" header="Prezzo">
                        <template #body="{ data }">
                            {{ parseFloat(data.price).toFixed(2) }} €
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
<style scoped>
    .clickable {
        cursor: pointer;
    }
</style>
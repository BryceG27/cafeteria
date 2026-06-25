<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { watchEffect } from 'vue';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Toast from 'primevue/toast';

import { useToast } from 'primevue/usetoast';

const toast = useToast();

const props = defineProps({
    menus : Array,
    flash : Object
})

watchEffect(() => {
    if(props.flash?.message)
        toast.add({ severity: 'success', summary: 'Menù Extra aggiornato', detail: props.flash.message, life: 3000 })
})

</script>
<template>
    <Head title="Menu Extra" />

    <Toast />

    <AuthenticatedLayout>
        <div class="content">
            <BaseBlock content-class="pb-3" title="Lista menù extra">
                <DataTable
                    :value="menus"
                >
                    <template #empty>
                        <div class="p-4 text-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun menù creato</p>
                        </div>
                    </template>
                    <Column>
                        <template #body="{ data }">
                            <Link 
                                class="btn btn-width btn-sm" 
                                :class="!data.active ? 'btn-alt-success' : 'btn-alt-warning'" 
                                :href="route('special-menus.toggle-active', { menu : data.id })"
                                style="font-size: 13px"
                                as="button"
                                method="put"
                            >
                                <i class="fa" :class="!data.active ? 'fa-play' : 'fa-pause'"></i>
                            </Link>
                        </template>
                    </Column>
                    <Column header="Nome" field="name" />
                    <Column header="Descrizione" field="description" />
                    <Column header="Prodotto" field="product.name" />
                    <Column header="Prezzo" field="price">
                        <template #body="{ data }">
                            {{ data.price.toFixed(2) }} &euro;
                        </template>
                    </Column>
                </DataTable>
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>
<style scoped>
    
</style>
<script setup>
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputNumber from 'primevue/inputnumber';

import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

import moment from 'moment';

const props = defineProps({
    customer: Object,
});

const destroy = (id) => {
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
            form.delete(route('credits.destroy', { credit : id}));
        }
    })
}

const updateCreditAvailableAmount = (event) => {
    const form = useForm({
        amount_available: event.newData.amount_available,
    });

    form.patch(route('credits.update', { credit : event.newData.id }), {
        preserveScroll: true,
        onSuccess : () => {
            Swal.fire({
                icon: 'success',
                title: 'Aggiornato con successo',
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}

</script>
<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <DataTable
                    :value="customer.credits"
                    stripedRows
                    style="max-height: 50rem; overflow-y: auto"
                    editMode="cell" 
                    @cell-edit-complete="updateCreditAvailableAmount"
                >
                    <template #empty>
                        <div class="p-4 text-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun registrato disponibile</p>
                        </div>
                    </template>
                    <Column>
                        <template #body="{ data }">
                            <button
                                @click.prevent="destroy(data.id)"
                                class="btn btn-sm btn-alt-danger"
                            >
                                <i class="fa fa-trash"></i>
                            </button>    
                        </template>
                    </Column>
                    <Column style="width: 55%" header="Descrizione">
                        <template #body="{ data }">
                            <span v-text="data.description" />
                        </template>
                    </Column>
                    <Column style="width: 15%" header="Totale creato">
                        <template #body="{ data }">
                            <span>{{ parseFloat(data.total).toFixed(2) }} &euro;</span>
                        </template>
                    </Column>
                    <Column style="width: 15%" header="Totale disponibile">
                        <template #body="{ data }">
                            <span>{{ parseFloat(data.amount_available).toFixed(2) }} &euro;</span>
                        </template>
                        <template #editor="{ data }">
                            <InputNumber 
                                v-model="data.amount_available" 
                                mode="currency" 
                                currency="EUR" 
                                locale="it-IT" 
                                class="w-100"
                                inputClass="w-100"
                            />
                        </template>
                    </Column>
                    <Column style="width: 10%" header="Data">
                        <template #body="{ data }">
                            <span v-text="moment(data.created_at).format('DD/MM/YYYY')" />
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
   </div>
</template>
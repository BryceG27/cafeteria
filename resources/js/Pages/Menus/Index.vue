<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";
import { ref, reactive, computed, onMounted } from "vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import { FilterMatchMode } from '@primevue/core/api';

import moment from 'moment';
import Swal from 'sweetalert2';

onMounted(() => {
    moment.locale('it');
    moment().isoWeekday(1);
})

const props = defineProps({
    menus: Array,
    auth: Object,
});

const filters = ref({
    name : { value: null, matchMode: FilterMatchMode.CONTAINS },
    start_date : { value: null, matchMode: FilterMatchMode.BETWEEN },
})

const expandedRows = ref(null);

const dateFilter = reactive({ 
    start: moment().isoWeekday(1).startOf('week').toDate(), 
    end: moment().isoWeekday(1).endOf('week').toDate(),
    validity_date : null,
    week : null,
});

const filteredMenus = computed(() => {
    return props.menus.filter(menu => {
        if(dateFilter.week) {
            const [year, week] = dateFilter.week.split('-W');
            
            const startOfWeek = moment().isoWeekday(1).year(year).week(week).startOf('week').format('YYYY-MM-DD');
            const endOfWeek = moment().year(year).week(week).endOf('week').add(1, 'days').format('YYYY-MM-DD');
            
            return startOfWeek <= menu.start_date && menu.end_date <= endOfWeek;
        }
        
       return dateFilter.validity_date == null ? menu.validity_date >= moment(dateFilter.start).format('YYYY-MM-DD') && menu.validity_date <= moment(dateFilter.end).format('YYYY-MM-DD') : menu.validity_date == moment(dateFilter.validity_date).format('YYYY-MM-DD');
    });
});


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
                    paginator
                    v-model:expandedRows="expandedRows"
                    :value="filteredMenus"
                    :rows="10"
                    v-model:filters="filters"
                    filterDisplay="row"
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
                    <Column style="width: 15%" field="name" header="Nome" :showFilterMenu="false">
                        <template #filter="{ filterModel, filterCallback }">
                            <InputGroup>
                                <InputText
                                    class="w-100"
                                    input-class="w-100"
                                    placeholder="Cerca per nome" 
                                    v-model="filterModel.value" 
                                    @input="filterCallback" 
                                />
                                <InputGroupAddon>
                                    <button class="btn-link link-danger" @click.prevent="filterModel.value = null; filterCallback()">
                                        <i class="fa fa-x"></i>
                                    </button>
                                </InputGroupAddon>
                            </InputGroup>
                        </template>
                    </Column>
                    <Column style="width: 10%" field="price" header="Prezzo">
                        <template #body="{ data }">
                            {{ parseFloat(data.price).toFixed(2) }} €
                        </template>
                    </Column>
                    <Column style="width: 10%" field="start_date" header="Settimana validità" :showFilterMenu="false">
                        <template #body="{ data }">
                            {{ moment(data.start_date).format('DD/MM') }} - {{ moment(data.end_date).format('DD/MM') }}
                        </template>
                        <template #filter>
                            <InputGroup>
                                <!-- <DatePicker 
                                    dateFormat="dd/mm/yy"
                                    v-model="dates" 
                                    class="w-100" 
                                    inputClass="w-100"
                                    selectionMode="range"
                                /> -->
                                <input type="week" class="form-control" v-model="dateFilter.week" />
                                <InputGroupAddon>
                                    <button class="btn-link link-danger" @click.prevent="dates = []">
                                        <i class="fa fa-x"></i>
                                    </button>
                                </InputGroupAddon>
                            </InputGroup>
                        </template>
                    </Column>
                    <Column style="width: 14%" field="validity_date" header="Valido il" :showFilterMenu="false">
                        <template #body="{ data }">
                            <span v-if="data.validity_date" v-text="moment(data.validity_date).format('DD/MM')" />
                            <span v-else>-</span>
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputGroup>
                                <!-- <DatePicker 
                                    dateFormat="dd/mm/yy"
                                    v-model="dateFilter.validity_date" 
                                    class="w-100" 
                                    inputClass="w-100"
                                /> -->
                                <input type="date" class="form-control" v-model="dateFilter.validity_date" />

                                <InputGroupAddon>
                                    <button class="btn-link link-danger" @click.prevent="dateFilter.validity_date = null; filterCallback()">
                                        <i class="fa fa-x"></i>
                                    </button>
                                </InputGroupAddon>
                            </InputGroup>
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
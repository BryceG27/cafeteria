<script setup>
import InputNumber from "primevue/inputnumber";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import DatePicker from 'primevue/datepicker'
import SelectButton from "primevue/selectbutton";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Image from "primevue/image";
import MultiSelect from "primevue/multiselect";
import Dialog from "primevue/dialog";

import InputError from "@/Components/InputError.vue";

import { computed, ref } from "vue";
import moment from "moment";
import { FilterMatchMode } from '@primevue/core/api'

const emit = defineEmits(['submit']);

const props = defineProps({
    form : Object,
    categories : Array,
    products : Array,
    product_types : Array,
    auth : Object,
    menus : Array,
    errors : Object,
});

const avilable_filters = ref({
    name: { value: null, matchMode: FilterMatchMode.CONTAINS},
    type: { value: null, matchMode: FilterMatchMode.IN}
})

const selected_filters = ref({
    name: { value: null, matchMode: FilterMatchMode.CONTAINS},
    type: { value: null, matchMode: FilterMatchMode.IN}
})

const past_filters = ref({
    name: { value: null, matchMode: FilterMatchMode.CONTAINS},
    type: { value: null, matchMode: FilterMatchMode.IN}
})

const expandedRowGroups = ref([]);
const selected_menu = ref(null);
const show_menu_modal = ref(false)

const add_product_to_menu = (product) => {
    props.form.products.push(product);
}

const remove_product_from_menu = (product) => {
    props.form.products = props.form.products.filter(p => p.id !== product.id)
}

const availableProducts = computed(() => {
    return props.products
        .filter(p => !props.form.products.some(mp => mp.id === p.id))
        .sort((a, b) => {
            if (!a.category || !b.category) return 0;
            return a.category.id - b.category.id;
        });
});

const show_menu = (menu) => {
    selected_menu.value     = menu;
    show_menu_modal.value   = true;
}

</script>
<template>
    <Dialog
        modal
        v-model:visible="show_menu_modal"
        :style="{
            width : '45rem'
        }"
        :header="`Menù - ${selected_menu?.name}`"
    >
        <DataTable
            :value="selected_menu?.products"
            striped-rows
            scrollable
            scroll-height="27.5rem"
            v-model:filters="past_filters"
        >
            <Column header="Prodotto" field="name" :showFilterMenu="false">
                <template #filter="{ filterModel, filterCallback }">
                    <div class="d-flex align-items-center gap-2">
                        <InputText 
                            v-model="filterModel.value"
                            type="text"
                            @input="filterCallback"
                            class="w-75"
                        />
                        <button class="btn btn-link link-danger" type="button" @click="filterModel.value = null, filterCallback()">
                            <i class="fa fa-x"></i>
                        </button>
                    </div>
                </template>
            </Column>
            <Column header="Tipo" field="type.name">
                <template #filter="{ filterModel, filterCallback }">
                    <div class="d-flex align-items-center gap-2">
                        <MultiSelect 
                            v-model="filterModel.value"
                            @change="filterCallback"
                            :options="product_types"
                            optionLabel="name"
                        />
                        <button class="btn btn-link link-danger" type="button" @click="filterModel.value = null, filterCallback()">
                            <i class="fa fa-x"></i>
                        </button>
                    </div>
                </template>
            </Column>
        </DataTable>
    </Dialog>

    <form @submit.prevent="emit('submit')" class="container-fluid">
        <div class="row align-items-center pb-3">
            <div class="col-lg-6 col-md-4 d-flex flex-column justify-content-center">
                <label for="is_active" class="form-label">Attivo</label>
                <SelectButton 
                    v-model="form.is_active" 
                    :options="[{ label: 'Sì', value: 1 }, { label: 'No', value: 0 }]"
                    inputId="is_active"
                    :class="{ 'is-invalid': errors.is_active }"
                    option-label="label"
                    option-value="value"
                />
            </div>
            <div class="col-lg-6 col-md-8" v-if="menus.length">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="text-center" :colspan="menus.length">Menù della settimana</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td v-for="menu in menus" :key="menu.id">
                                <button class="btn btn-link link-info" type="button" @click="show_menu(menu)">{{ menu.name }}</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row pb-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Nome menù</label>
                <InputText 
                    v-model="form.name"
                    inputId="name"
                    class="w-100"
                    inputClass="w-100"
                    placeholder="Nome menù"
                    :class="{ 'is-invalid': errors.name }"
                />
                <InputError class="mt-2" :message="errors.name" />
            </div>
            <div class="col-md-6">
                <label for="descritpion" class="form-label">Descrizione</label><br>
                <Textarea 
                    v-model="form.description"
                    inputId="description"
                    class="w-100"
                    inputClass="w-100"
                    placeholder="Descrizione" 
                    :class="{ 'is-invalid': errors.description }"
                    rows="1"
                />
                <InputError class="mt-2" :message="errors.description" />
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="price" class="form-label">Prezzo menù</label>
                <InputNumber 
                    v-model="form.price"
                    :min="0" 
                    class="w-100"
                    inputClass="w-100"
                    placeholder="Prezzo menù"
                    mode="currency"
                    currency="EUR"
                    locale="it-IT"
                    :class="{ 'is-invalid': errors.price }"
                />
                <InputError class="mt-2" :message="errors.price" />
            </div>
            <div class="col-md-6">
                <label for="second_price" class="form-label">Prezzo menù aggiuntivo</label>
                <InputNumber 
                    v-model="form.second_price"
                    :min="0" 
                    class="w-100"
                    inputClass="w-100"
                    placeholder="Prezzo menù aggiuntivo"
                    mode="currency"
                    currency="EUR"
                    locale="it-IT"
                    :class="{ 'is-invalid': errors.second_price }"
                />
                <InputError class="mt-2" :message="errors.second_price" />
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Valido il</label><br>
                <DatePicker 
                    v-model="form.validity_date"
                    inputId="end_date"
                    class="w-100"
                    inputClass="w-100"
                    :minDate="moment().toDate()"
                    dateFormat="dd/mm/yy"
                    placeholder="gg/mm/aaaa"
                />
                <InputError class="mt-2" :message="errors.validity_date" />
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-sm-12 mt-4 col-xxl-6">
                <label for="products" class="form-label">Prodotti disponibili</label>
                <DataTable
                    stripedRows
                    :value="availableProducts"
                    v-model:expandedRowGroups="expandedRowGroups"
                    scrollable
                    scrollHeight="25rem"
                    v-model:filters="avilable_filters"
                    filterDisplay="row"
                >
                    <template #empty>
                        <div class="text-center p-4">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun prodotto disponibile</p>
                        </div>
                    </template>
                    <template #groupheader="slotProps">
                        <span>
                            {{ slotProps.data.category.name }}
                        </span>
                    </template>
                    <Column style="width: 10%" class="text-center">
                        <template #body="{ data }">
                            <button class="btn btn-link link-success btn-sm" @click="add_product_to_menu(data)" type="button">
                                <i class="fa fa-plus"></i>
                            </button>
                        </template>
                    </Column>
                    <Column style="width: 40%" header="Nome" field="name" :showFilterMenu="false">
                        <template #filter="{ filterModel, filterCallback }">
                            <div class="d-flex align-items-center gap-2">
                                <InputText 
                                    v-model="filterModel.value"
                                    type="text"
                                    @input="filterCallback"
                                    class="w-75"
                                />
                                <button class="btn btn-link link-danger" type="button" @click="filterModel.value = null, filterCallback()">
                                    <i class="fa fa-x"></i>
                                </button>
                            </div>
                        </template>
                    </Column>
                    <Column style="width: 20%" header="Foto">
                        <template #body="{ data }">
                            <Image 
                                v-if="data.image" 
                                :src="`/storage/app/public/${data.image}`" 
                                alt="Immagine prodotto" 
                                width="100%"
                            />
                        </template>
                    </Column>
                    <Column field="type.name" filterField="type" style="width: 30%" header="Tipo" :showFilterMenu="false">
                        <template #filter="{ filterModel, filterCallback }">
                            <div class="d-flex align-items-center gap-2">
                                <MultiSelect 
                                    v-model="filterModel.value"
                                    @change="filterCallback"
                                    :options="product_types"
                                    optionLabel="name"
                                />
                                <button class="btn btn-link link-danger" type="button" @click="filterModel.value = null, filterCallback()">
                                    <i class="fa fa-x"></i>
                                </button>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
            <div class="col-sm-12 mt-4 col-xxl-6">
                <label for="products" class="form-label">Prodotti selezionati</label>
                <DataTable
                    stripedRows
                    :value="props.form.products"
                    scrollable
                    scrollHeight="25rem"
                    v-model:filters="selected_filters"
                    filterDisplay="row"
                >
                    <template #empty>
                        <div class="text-center p-4">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun prodotto inserito</p>
                        </div>
                    </template>
                    <Column style="width: 10%" class="text-center">
                        <template #body="{ data }">
                            <button class="btn btn-link link-danger btn-sm" @click="remove_product_from_menu(data)" type="button">
                                <i class="fa fa-minus"></i>
                            </button>
                        </template>
                    </Column>
                    <Column style="width: 40%" header="Nome" field="name" :showFilterMenu="false">
                        <template #filter="{ filterModel, filterCallback }">
                            <div class="d-flex align-items-center gap-2">
                                <InputText 
                                    v-model="filterModel.value"
                                    type="text"
                                    @input="filterCallback"
                                    class="w-75"
                                />
                                <button class="btn btn-link link-danger" type="button" @click="filterModel.value = null, filterCallback()">
                                    <i class="fa fa-x"></i>
                                </button>
                            </div>
                        </template>
                    </Column>
                    <Column style="width: 30%" header="Foto">
                        <template #body="{ data }">
                            <Image 
                                v-if="data.image" 
                                :src="`/storage/app/public/${data.image}`" 
                                alt="Immagine prodotto" 
                                width="100%"
                            />
                        </template>
                    </Column>
                    <Column field="type.name" filterField="type" style="width: 20%" header="Tipo" :showFilterMenu="false">
                        <template #filter="{ filterModel, filterCallback }">
                            <div class="d-flex align-items-center gap-2">
                                <MultiSelect 
                                    v-model="filterModel.value"
                                    @change="filterCallback"
                                    :options="product_types"
                                    optionLabel="name"
                                />
                                <button class="btn btn-link link-danger" type="button" @click="filterModel.value = null, filterCallback()">
                                    <i class="fa fa-x"></i>
                                </button>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </form>
</template>
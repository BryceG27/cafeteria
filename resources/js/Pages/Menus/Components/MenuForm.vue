<script setup>
import InputNumber from "primevue/inputnumber";
import Textarea from "primevue/textarea";
import Calendar from "primevue/calendar";
import SelectButton from "primevue/selectbutton";
import Datatable from "primevue/datatable";
import Column from "primevue/column";
import Image from "primevue/image";

import InputError from "@/Components/InputError.vue";

import { computed, watch, watchEffect } from "vue";
import moment from "moment";

const props = defineProps({
    form : Object,
    categories : Array,
    products : Array,
    auth : Object,
    errors : Object,
});

const emit = defineEmits(['submit']);

const add_product_to_menu = (product) => {
    props.form.products.push(product);
}

const remove_product_from_menu = (product) => {
    props.form.products = props.form.products.filter(p => p.id !== product.id)
}

const availableProducts = computed(() => {
    return props.products.filter(p => !props.form.products.some(mp => mp.id === p.id));
});

</script>
<template>
    <form @submit.prevent="emit('submit')" class="container">
        <div class="row pb-3">
            <div class="col-md-6">
                <label for="is_active" class="form-label">Attivo</label> <br>
                <SelectButton 
                    v-model="form.is_active" 
                    :options="[{ label: 'Sì', value: 1 }, { label: 'No', value: 0 }]"
                    inputId="is_active"
                    :class="{ 'is-invalid': errors.is_active }"
                    option-label="label"
                    option-value="value"
                />
            </div>
        </div>
        <div class="row pb-3">
            <div class="col-md-12">
                <label for="descritpion" class="form-label">Descrizione</label><br>
                <Textarea 
                    v-model="form.description"
                    inputId="description"
                    class="w-100"
                    inputClass="w-100"
                    placeholder="Descrizione" 
                    :class="{ 'is-invalid': errors.description }"
                />
                <InputError class="mt-2" :message="errors.description" />
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="price" class="form-label">Prezzo menù</label>
                <InputNumber 
                    v-form="form.price"
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
                <label for="second_menu_price" class="form-label">Prezzo menù aggiuntivo</label>
                <InputNumber 
                    v-form="form.second_menu_price"
                    :min="0" 
                    class="w-100"
                    inputClass="w-100"
                    placeholder="Prezzo menù aggiuntivo"
                    mode="currency"
                    currency="EUR"
                    locale="it-IT"
                    :class="{ 'is-invalid': errors.second_menu_price }"
                />
                <InputError class="mt-2" :message="errors.second_menu_price" />
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Data inizio</label><br>
                <Calendar 
                    v-model="form.start_date"
                    inputId="start_date"
                    class="w-100"
                    inputClass="w-100"
                    :minDate="moment().toDate()"
                    dateFormat="dd/mm/yy"
                    placeholder="gg/mm/aaaa"
                />
                <InputError class="mt-2" :message="errors.start_date" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Data fine</label><br>
                <Calendar 
                    v-model="form.end_date"
                    inputId="end_date"
                    class="w-100"
                    inputClass="w-100"
                    :minDate="moment(form.start_date).add(1, 'day').toDate()"
                    :maxDate="moment(form.start_date).add(1, 'week').toDate()"
                    dateFormat="dd/mm/yy"
                    placeholder="gg/mm/aaaa"
                />
                <InputError class="mt-2" :message="errors.end_date" />
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="products" class="form-label">Prodotti disponibili</label>
                <Datatable
                    stripedRows
                    :value="availableProducts"
                >
                    <template #empty>
                        <div class="text-center p-4">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun prodotto disponibile</p>
                        </div>
                    </template>
                    <Column style="" class="text-center">
                        <template #body="{ data }">
                            <button class="btn btn-alt-success btn-sm" @click="add_product_to_menu(data)" type="button">
                                <i class="fa fa-plus"></i>
                            </button>
                        </template>
                    </Column>
                    <Column style="" header="Nome" field="name" />
                    <Column style="" header="Foto">
                        <template #body="{ data }">
                            <Image 
                                v-if="data.image" 
                                :src="`/storage/${data.image}`" 
                                alt="Immagine prodotto" 
                                width="100%"
                            />
                        </template>
                    </Column>
                    <Column style="" header="Qta">
                        <template #body="{ data }">
                            <InputNumber
                                v-model="data.quantity"
                                :min="1"
                                :max="100" 
                                class="w-100"
                                inputClass="w-100 text-center"
                            />
                        </template>
                    </Column>
                </Datatable>
            </div>
            <div class="col-md-6">
                <label for="products" class="form-label">Prodotti selezionati</label>
                <Datatable
                    stripedRows
                    :value="form.products"
                >
                    <template #empty>
                        <div class="text-center p-4">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun prodotto inserito</p>
                        </div>
                    </template>
                    <Column style="" class="text-center">
                        <template #body="{ data }">
                            <button class="btn btn-alt-danger btn-sm" @click="remove_product_from_menu(data)" type="button">
                                <i class="fa fa-minus"></i>
                            </button>
                        </template>
                    </Column>
                    <Column style="" header="Nome" field="name" />
                    <Column style="" header="Foto">
                        <template #body="{ data }">
                            <Image 
                                v-if="data.image" 
                                :src="`/storage/${data.image}`" 
                                alt="Immagine prodotto" 
                                width="100%"
                            />
                        </template>
                    </Column>
                    <Column style="" header="Qta">
                        <template #body="{ data }">
                            <InputNumber
                                v-model="data.quantity"
                                :min="1" 
                                :max="100" 
                                class="w-100"
                                inputClass="w-100 text-center"
                            />
                        </template>
                    </Column>
                </Datatable>
            </div>
        </div>
    </form>
</template>
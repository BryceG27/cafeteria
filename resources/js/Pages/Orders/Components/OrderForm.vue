<script setup>
import Dropdown from "primevue/dropdown";
import InputNumber from "primevue/inputnumber";
import Textarea from "primevue/textarea";
import InputError from "@/Components/InputError.vue";
import Calendar from "primevue/calendar";
import moment from "moment";

import Listbox from "primevue/listbox";

import { computed, ref, watch } from "vue";

const props = defineProps({
    form : Object,
    menus : Array,
    order : Object,
    auth : Object,
    errors : Object,
    statuses : Array,
});

const emit = defineEmits(['submit']);

const add_second_menu = ref(props.form.first_dish_id != null && props.form.second_dish_id != null);

const selectedMenu = computed(() => {
    return props.menus.find(m => m.id === props.form.menu_id) || null;
});

const order_total = computed(() => {

    if(!selectedMenu.value) return 0;

    let subtotal = parseFloat(selectedMenu.value.price);
    let second_menu_price = add_second_menu.value ? parseFloat(selectedMenu.value.second_price) : 0;
    return subtotal + second_menu_price - parseFloat(props.form.discount);
});

const first_dishes = computed(() => {
    if(!selectedMenu.value) return [];
    return selectedMenu.value.products.filter(p => p.product_type_id == 2 && p.pivot.quantity > 0);
});

const second_dishes = computed(() => {
    if(!selectedMenu.value) return [];
    return selectedMenu.value.products.filter(p => p.product_type_id == 3 && p.pivot.quantity > 0);
});

const side_dishes = computed(() => {
    if(!selectedMenu.value) return [];
    return selectedMenu.value.products.filter(p => p.product_type_id == 4 && p.pivot.quantity > 0);
});

watch(() => props.form, (newVal) => {
    if(newVal.first_dish_id != null && !add_second_menu.value) {
        props.form.second_dish_id = null;
        props.form.side_dish_id = null;
    }

    if(newVal.second_dish_id != null) {
        if(!add_second_menu.value)
            props.form.first_dish_id = null;
    }
}, { deep: true });

watch(add_second_menu, (newVal) => {
    if(!newVal) {
        props.form.first_dish_id = null;
        props.form.second_dish_id = null;
        props.form.side_dish_id = null;
    }
});

</script>
<template>
    <form @submit.prevent="emit('submit')" class="container-fluid">
        <div class="row pb-3" v-if="menus.length > 0">
            <div class="col-md-12">
                <label for="menu_id" class="form-label">Menù</label>
                <Dropdown 
                    inputId="menu_id"
                    optionValue="id"
                    optionLabel="description"
                    :options="props.menus"
                    class="w-100"
                    inputClass="w-100"
                    v-model="form.menu_id"
                    placeholder="Seleziona un menù"
                >
                    <template #option="slotProps">
                        {{ slotProps.option.name }} - {{ slotProps.option.description }}
                    </template>
                </Dropdown>
                <InputError class="mt-2" :message="errors.menu_id" />
            </div>
        </div>
        <template v-if="form.menu_id != null">
            <div class="row pb-3">
                <div class="col-md-12">
                    <em class="text-muted" v-text="selectedMenu.description" />
                </div>
            </div>
            <div class="row pb-3">
                <div class="col-md-4" v-if="auth.user.user_group_id == 1">
                    <label for="total" class="form-label">Subtotale ordine</label>
                    <InputNumber 
                        inputId="total"
                        class="w-100"
                        mode="currency" 
                        currency="EUR" 
                        locale="it-IT" 
                        v-model="form.subtotal_amount"
                        readonly
                    />
                </div>
                <div class="col-md-4" v-if="auth.user.user_group_id == 1">
                    <label for="total" class="form-label">Sconto</label>
                    <InputNumber 
                        inputId="total"
                        :class="{ 'is-invalid': errors.discount }"
                        class="w-100"
                        mode="currency" 
                        currency="EUR" 
                        locale="it-IT" 
                        v-model="form.discount"
                        :min="0"
                    />
                </div>
                <div :class="{ 'col-md-6' : auth.user.user_group_id == 3, 'col-md-4' : auth.user.user_group_id == 1 }">
                    <label for="total" class="form-label">Totale ordine</label>
                    <InputNumber 
                        inputId="total"
                        class="w-100"
                        mode="currency" 
                        currency="EUR" 
                        locale="it-IT" 
                        :value="order_total"
                        v-model="order_total"
                        readonly
                    />
                </div>
                <div class="col-md-6 pt-3" v-if="auth.user.user_group_id == 1">
                    <label for="status" class="form-label">Stato</label>
                    <Dropdown 
                        inputId="status"
                        optionValue="value"
                        optionLabel="label"
                        class="w-100"
                        inputClass="w-100"
                        :options="statuses"
                        v-model="form.status"
                    />
                </div>
                <div class="col-md-6" :class="{ 'pt-3' : auth.user.user_group_id == 1 }">
                    <label for="order_date" class="form-label">Data validità ordine:</label>
                    <Calendar 
                        v-model="form.order_date" 
                        inputId="order_date"
                        :class="{ 'is-invalid': errors.order_date }"
                        class="w-100"
                        inputClass="w-100"
                        dateFormat="dd/mm/yy"
                        placeholder="gg/mm/aaaa"
                        :minDate="moment().toDate()"
                        :maxDate="selectedMenu ? moment(selectedMenu.end_date).toDate() : null"

                    />
                    <InputError class="mt-2" :message="errors.order_date" />
                </div>
            </div>
            <div class="row pb-3">
                <div class="col-md-12">
                    <label for="notes" class="form-label">Note</label>
                    <Textarea 
                        v-model="form.notes"
                        inputId="notes"
                        :class="{ 'is-invalid': errors.notes }"
                        class="w-100"
                        rows="3"
                        placeholder="Note aggiuntive..."
                    />
                    <InputError class="mt-2" :message="errors.notes" />
                </div>
            </div>
            <div class="row pb-3">
                <div class="col-md-12">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" v-model="add_second_menu" id="add_second_menu" />
                        <label for="add_second_menu" class="form-checl-label">Aggiungi un secondo menù (+{{ parseFloat(selectedMenu.second_price).toFixed(2) }} &euro;)</label> <br>
                        <em class="text-muted">N.B. Prendendo un secondo, il contorno è sempre incluso</em>
                    </div>
                </div>
            </div>
            <div class="row pb-3">
                <div class="col-md-12">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 33%">Primo</th>
                                <th style="width: 33%">Secondo</th>
                                <th style="width: 33%">Contorno</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <InputError class="mt-2" :message="errors.first_dish_id" />
                                    <Listbox 
                                        v-model="form.first_dish_id" 
                                        :options="first_dishes" 
                                        optionLabel="name" 
                                        optionValue="id"
                                        class="w-100" 
                                        listStyle="max-height: 12rem"
                                        :disabled="form.second_dish_id != null && !add_second_menu"
                                    >
                                        <template #optiongroup="slotProps">
                                            <img :alt="slotProps.option.name" :src="slotProps.option.image" style="width: 18px" />
                                           {{ slotProps.option.name }}
                                        </template>
                                    </Listbox>
                                </td>
                                <td>
                                    <InputError class="mt-2" :message="errors.second_dish_id" />
                                    <Listbox 
                                        v-model="form.second_dish_id" 
                                        :options="second_dishes" 
                                        optionLabel="name" 
                                        optionValue="id"
                                        class="w-100" 
                                        listStyle="max-height: 12rem"
                                        :disabled="form.first_dish_id != null && !add_second_menu"
                                    >
                                        <template #optiongroup="slotProps">
                                            <img :alt="slotProps.option.name" :src="slotProps.option.image" style="width: 18px" />
                                           {{ slotProps.option.name }}
                                        </template>
                                    </Listbox>
                                </td>
                                <td>
                                    <Listbox 
                                        v-model="form.side_dish_id" 
                                        :options="side_dishes" 
                                        optionLabel="name" 
                                        optionValue="id"
                                        class="w-100" 
                                        listStyle="max-height: 12rem"
                                        :disabled="(form.first_dish_id != null && !add_second_menu) || form.second_dish_id == null"
                                    >
                                        <template #optiongroup="slotProps">
                                            <img :alt="slotProps.option.name" :src="slotProps.option.image" style="width: 18px" />
                                           {{ slotProps.option.name }}
                                        </template>
                                    </Listbox>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
        <div class="p-4 text-center" v-else-if="menus.length == 0">
            <i class="fa fa-exclamation-triangle fa-2x"></i>
            <p class="mt-2">Nessun menù disponibile</p>
        </div>
        <div class="p-4 text-center" v-else>
            <i class="fa fa-exclamation-triangle fa-2x"></i>
            <p class="mt-2">Nessun menù selezionato</p>
        </div>
    </form>
</template>
<style>
    
</style>
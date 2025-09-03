<script setup>
import Dropdown from "primevue/dropdown";
import InputNumber from "primevue/inputnumber";
import Textarea from "primevue/textarea";
import Calendar from "primevue/calendar";
import Listbox from "primevue/listbox";

import InputError from "@/Components/InputError.vue";
import moment from "moment";

import { computed, ref, watch } from "vue";
import { Link } from "@inertiajs/vue3";

import Swal from "sweetalert2";

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

const goBack = () => {
    Swal.fire({
        title: 'Sei sicuro?',
        text: "Le modifiche non salvate andranno perse!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, annulla!',
        cancelButtonText: 'Rimani'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = route('orders.index');
        }
    })
}

</script>
<template>
    <form @submit.prevent="emit('submit')" class="container-fluid">
        <div class="row pb-3" v-if="menus.length > 1">
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
                        {{ slotProps.option.name }}
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
                    <table class="table d-none d-md-table">
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
                                        listStyle="max-height: 17rem;"
                                        :disabled="form.second_dish_id != null && !add_second_menu"
                                    >
                                        <template #option="slotProps">
                                            <!-- <img :alt="slotProps.option.name"  :src="slotProps.option.image" style="width: 18px" /> -->
                                            <div>
                                                {{ slotProps.option.name }} <br>
                                                <span class="text-muted" style="font-size: 12px" v-text="slotProps.option.description" />
                                            </div>
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
                                        listStyle="max-height: 17rem"
                                        :disabled="form.first_dish_id != null && !add_second_menu"
                                    >
                                        <template #option="slotProps">
                                            <!-- <img :alt="slotProps.option.name"  :src="slotProps.option.image" style="width: 18px" /> -->
                                           <div>
                                                {{ slotProps.option.name }} <br>
                                                <span class="text-muted" style="font-size: 12px" v-text="slotProps.option.description" />
                                            </div>
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
                                        listStyle="max-height: 17rem"
                                        :disabled="(form.first_dish_id != null && !add_second_menu) || form.second_dish_id == null"
                                    >
                                        <template #option="slotProps">
                                            <!-- <img :alt="slotProps.option.name"  :src="slotProps.option.image" style="width: 18px" /> -->
                                           <div>
                                                {{ slotProps.option.name }} <br>
                                                <span class="text-muted" style="font-size: 12px" v-text="slotProps.option.description" />
                                            </div>
                                        </template>
                                    </Listbox>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="table d-md-none">
                        <tbody>
                            <tr>
                                <td>
                                    <p class="fw-bold border-bottom pb-2">Primo</p>
                                    <InputError class="mt-2" :message="errors.first_dish_id" />
                                    <Listbox 
                                        v-model="form.first_dish_id" 
                                        :options="first_dishes" 
                                        optionLabel="name" 
                                        optionValue="id"
                                        class="w-100" 
                                        listStyle="max-height: 17rem;"
                                        :disabled="form.second_dish_id != null && !add_second_menu"
                                    >
                                        <template #option="slotProps">
                                            <!-- <img :alt="slotProps.option.name"  :src="slotProps.option.image" style="width: 18px" /> -->
                                            <div>
                                                {{ slotProps.option.name }} <br>
                                                <span class="text-muted" style="font-size: 12px" v-text="slotProps.option.description" />
                                            </div>
                                        </template>
                                    </Listbox>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p class="fw-bold border-bottom pb-2">Secondo</p>
                                    <InputError class="mt-2" :message="errors.second_dish_id" />
                                    <Listbox 
                                        v-model="form.second_dish_id" 
                                        :options="second_dishes" 
                                        optionLabel="name" 
                                        optionValue="id"
                                        class="w-100" 
                                        listStyle="max-height: 17rem"
                                        :disabled="form.first_dish_id != null && !add_second_menu"
                                    >
                                        <template #option="slotProps">
                                            <!-- <img :alt="slotProps.option.name"  :src="slotProps.option.image" style="width: 18px" /> -->
                                           <div>
                                                {{ slotProps.option.name }} <br>
                                                <span class="text-muted" style="font-size: 12px" v-text="slotProps.option.description" />
                                            </div>
                                        </template>
                                    </Listbox>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p class="fw-bold border-bottom pb-2">Contorno</p>
                                    <Listbox 
                                        v-model="form.side_dish_id" 
                                        :options="side_dishes" 
                                        optionLabel="name" 
                                        optionValue="id"
                                        class="w-100" 
                                        listStyle="max-height: 17rem"
                                        :disabled="(form.first_dish_id != null && !add_second_menu) || form.second_dish_id == null"
                                    >
                                        <template #option="slotProps">
                                            <!-- <img :alt="slotProps.option.name"  :src="slotProps.option.image" style="width: 18px" /> -->
                                           <div>
                                                {{ slotProps.option.name }} <br>
                                                <span class="text-muted" style="font-size: 12px" v-text="slotProps.option.description" />
                                            </div>
                                        </template>
                                    </Listbox>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row pb-3">
                <div class="col-md-4" v-if="auth.user.user_group_id == 1">
                    <label for="total" class="form-label">Riepilogo Subtotale</label>
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
                    <label for="total" class="form-label">Riepilogo Totale</label>
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
                <div class="col-md-6 pt-2 pt-md-0" :class="{ 'pt-3' : auth.user.user_group_id == 1 }">
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
        </template>
        <div class="p-4 text-center" v-else-if="menus.length == 0">
            <i class="fa fa-exclamation-triangle fa-2x"></i>
            <p class="mt-2">Nessun menù disponibile</p>
        </div>
        <div class="p-4 text-center" v-else>
            <i class="fa fa-exclamation-triangle fa-2x"></i>
            <p class="mt-2">Nessun menù selezionato</p>
        </div>

        <div class="row pb-3 justify-content-center">
            <div class="col-md-4 col-sm-12 text-md-end text-center pb-3 pb-md-0">
                <button
                    class="btn btn-alt-primary btn-sm w-75"
                    type="submit"
                >
                    <i class="fa fa-save me-1"></i>
                    Conferma
                </button>
            </div>
            <div class="col-md-4 col-sm-12 text-center pb-3 pb-md-0">
                <button 
                    class="btn btn-alt-info btn-sm w-75"
                    type="button"
                >
                    <i class="fa fa-dollar-sign me-1"></i>
                    Conferma e paga
                </button>
            </div>
            <div class="col-md-4 col-sm-12 text-md-start text-center pb-3 pb-md-0">
                <button
                    class="btn btn-alt-danger btn-sm w-75"
                    type="button"
                    @click="goBack"
                >
                    <i class="fa fa-x me-1"></i>
                    Annulla
                </button>
            </div>
        </div>
    </form>
</template>
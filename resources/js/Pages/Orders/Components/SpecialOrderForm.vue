<script setup>
import Listbox from 'primevue/listbox';
import Textarea from 'primevue/textarea';
import DatePicker from 'primevue/datepicker';
import InputNumber from 'primevue/inputnumber';
import InputError from '@/Components/InputError.vue';

import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import moment from 'moment';

const props = defineProps({
    auth : Object,
    form : Object,
    statuses : Array,
    beverage : Array,
    menus : Array,
    errors : Object
})

const selected_menu = computed(() => {
    return props.form.special_menu_id ? props.menus.find(({id}) => id === props.form.special_menu_id) : null
})

const select_menu = (menu) => {
    props.form.special_menu_id = menu.id === props.form.special_menu_id ? null : menu.id
}

const minDate = moment().hours() >= 10 ? moment().add(1, 'day').toDate() : moment().toDate();

</script>
<template>
    <div style="min-height: 80vh">
        <div class="row">
            <div class="col-md-4 py-2" v-for="menu in menus" :key="menu.id">
                <div class="card h-100 menu-card" @click="select_menu(menu)" :class="{ 'text-bg-prime-success' : menu.id === form.special_menu_id }">
                    <div class="d-flex h-100 align-items-center">
                        <div class="col-4 d-flex align-items-center justify-content-center">
                            <img :src="menu.img_url" class="img-fluid rounded-start" style="object-fit: contain;" />
                        </div>

                        <div class="grow d-flex flex-column p-3">
                            <h5 class="card-title">{{ menu.name }}</h5>

                            <p class="card-text mb-0">
                                {{ menu.product.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3 border-top">
            <div class="col-12 mt-3">
                <h4>Completa il tuo ordine</h4>
            </div>
        </div>
        
        <div class="row pb-4">
            <div class="col-md-4 h-100" v-show="false">
                <h6 class="my-2">
                    Hai scelto
                </h6>
                <div class="card w-100" :class="{ 'text-bg-prime-success' : selected_menu }" style="min-height: 17rem">
                    <template v-if="selected_menu">
                        <img :src="`/storage/app/public/${selected_menu?.product?.image}`" class="card-img-top" :alt="selected_menu?.product?.name">
                        <div class="card-body">
                            <h5 class="card-title" v-text="selected_menu.product.name" />
                            <p class="card-text" v-text="selected_menu?.product?.description" />
                        </div>
                    </template>
                    <template v-else>
                        <div class="card-body h-100 d-flex align-items-center">
                            <small class="text-muted fs-italic">
                                Vedrai i dettagli del menù non appena effettuerai una scelta
                            </small>
                        </div>
                    </template>
                </div>
                <InputError class="mt-2 text-danger" :message="errors.special_menu_id" />
            </div>
            <div class="col-md-6">
                <h6 class="my-2">
                    Scegli una bibita
                </h6>
                <Listbox 
                    v-model="form.beverage_id" 
                    :options="beverage" 
                    optionLabel="name" 
                    optionValue="id"
                    class="w-100" 
                    listStyle="max-height: 17rem;"
                    :disabled="!selected_menu"
                >
                    <template #option="slotProps">
                        <div class="w-100">
                            <div class="d-flex align-items-center justify-content-between">
                                {{ slotProps.option.name }}
                            </div>
                            <span class="text-muted" style="font-size: 12px" v-text="slotProps.option.description" />
                        </div>
                    </template>
                </Listbox>
                <InputError class="mt-2 text-danger" :message="errors.beverage_id" />
            </div>
            <div class="col-md-6">
                <h6 class="my-2">
                    Hai delle richieste?
                </h6>
                <Textarea 
                    v-model="form.notes"
                    inputId="notes"
                    :class="{ 'is-invalid': errors.notes }"
                    class="w-100"
                    style="height: 90%"
                    inputStyle="height: 100%"
                    placeholder="Inserisci qui le tue note..."
                />
            </div>
        </div>

        <div class="row mt-3 pt-3 g-2 align-items-center border-top">
            <div class="col-md-6">
                <label for="total" class="form-label">Riepilogo Totale</label>
                <InputNumber 
                    inputId="total"
                    class="w-100"
                    mode="currency" 
                    currency="EUR" 
                    locale="it-IT" 
                    v-model="form.total_amount"
                    readonly
                />
            </div>
            <div class="col-md-6 pt-2 pt-md-0" :class="{ 'pt-3' : auth.user.user_group_id == 1 }">
                <label for="order_date" class="form-label">Ordine valido il:</label>
                <DatePicker 
                    v-model="form.order_date" 
                    inputId="order_date"
                    :class="{ 'is-invalid': errors.order_date }"
                    class="w-100"
                    inputClass="w-100"
                    dateFormat="dd/mm/yy"
                    placeholder="gg/mm/aaaa"
                    :minDate="minDate"
                    inline
                />
                <InputError class="mt-2 text-danger" :message="errors.order_date" />
            </div>
        </div>

        <div class="row py-3 justify-content-center">
            <div class="col-md-4 col-sm-12 text-center pb-3 pb-md-0" v-if="form.id == undefined">
                <button 
                    class="btn btn-alt-info btn-sm w-75"
                    type="submit"
                >
                    <i class="fa fa-dollar-sign me-1"></i>
                    Conferma e paga
                </button>
            </div>
            <div class="col-md-4 col-sm-12 text-center pb-3 pb-md-0">
                <Link 
                    class="btn btn-alt-warning btn-sm w-75" 
                    type="button" 
                    v-if="form.id != undefined" 
                    :href="route('orders.index')"
                >
                    <i class="fa fa-arrow-left me-1"></i>
                    Indietro
                </Link>
                <button
                    class="btn btn-alt-danger btn-sm w-75"
                    type="button"
                    @click="goBack"
                    v-else
                >
                    <i class="fa fa-times me-1"></i>
                    Annulla
                </button>
            </div>
        </div>
    </div>
</template>
<style scoped>
    .menu-card {
        transition: transform .2s ease, box-shadow .2s ease, background-color .2s ease;
        cursor: pointer;
    }

    .menu-card:hover {
        transform: scale(1.02);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
    }

    .menu-card.selected {
        background-color: var(--bs-light);
        transform: scale(1.03);
        box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.2);
    }

    .text-bg-prime-success {
        background: #ecfdf5;
        color : #047857;
    }
</style>
<script setup>

import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import Dropdown from "primevue/dropdown";
import SelectButton from "primevue/selectbutton";
import Image from "primevue/image";

import InputError from "@/Components/InputError.vue";

const props = defineProps({
    form: Object,
    categories: Array,
    types: Array,
    auth: Object,
    errors: Object,
});

const emit = defineEmits(['submit']);

const loadImage = (event) => {
    props.form.image = event.target.files[0]
}

</script>
<template>
    <form @submit.prevent="emit('submit')" class="container">
        <div class="row pb-3">
            <div class="col-md-6">
                <label for="is_active" class="form-label">Attivo</label><br>
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
            <div class="col-md-6">
                <label for="name" class="form-label">Nome</label> <br></br>
                <InputText 
                    v-model="form.name" 
                    inputId="name"
                    class="w-100"
                    inputClass="w-100"
                    placeholder="Nome" 
                    :class="{ 'is-invalid': errors.name }"
                />
                <InputError class="mt-2" :message="errors.name" />
            </div>
            <div class="col-md-6">
                <label for="description" class="form-label">Descrizione</label> <br></br>
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
        <div class="row pb-3">
            <div class="col-md-6">
                <label for="category_id" class="form-label">Categoria</label> <br></br>
                <Dropdown 
                    v-model="form.category_id" 
                    :options="categories" 
                    optionLabel="name" 
                    optionValue="id" 
                    placeholder="Seleziona una categoria"
                    inputId="category_id"
                    class="w-100"
                    :class="{ 'is-invalid': errors.category_id }"
                />
                <InputError class="mt-2" :message="errors.category_id" />
            </div>
            <div class="col-md-6">
                <label for="product_type_id" class="form-label">Tipo</label> <br></br>
                <Dropdown 
                    v-model="form.product_type_id" 
                    :options="types" 
                    optionLabel="name" 
                    optionValue="id" 
                    placeholder="Seleziona il tipo di prodotto"
                    inputId="product_type_id"
                    class="w-100"
                    :class="{ 'is-invalid': errors.product_type_id }"
                />
                <InputError class="mt-2" :message="errors.product_type_id" />
            </div>
        </div>

        <div class="row pb-3">
            <label for="" class="form-label">Immagine</label>
            <div class="col-md-6">
                <InputText 
                    type="file"
                    class="form-control"
                    inputClass="form-control"
                    :class="{ 'is-invalid': errors.image }"
                    @input="loadImage($event)"
                    accept="image/*"
                />
                <InputError class="mt-2" :message="errors.image" />
            </div>
            <div class="col-md-6">
                <Image 
                    v-if="form.image && typeof(form.image) === 'string'" 
                    :src="typeof form.image === 'object' ? URL.createObjectURL(form.image) : `/storage/${form.image}`" 
                    alt="Immagine prodotto" 
                    width="100%"
                />
                <div v-else class="text-muted">Nessuna immagine caricata</div>
            </div>
        </div>
    </form>
</template>
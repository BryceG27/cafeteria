<script setup>

import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import SelectButton from "primevue/selectbutton";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    form: Object,
    auth: Object,
    errors: Object,
});

const emit = defineEmits(['submit']);

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
    </form>
</template>
<style>
    
</style>
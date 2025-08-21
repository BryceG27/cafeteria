<script setup>

import InputText from "primevue/inputtext";
import Dropdown from "primevue/dropdown";
import SelectButton from "primevue/selectbutton";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    form: Object,
    userGroups: Array,
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
                <label for="surname" class="form-label">Cognome</label> <br></br>
                <InputText 
                    v-model="form.surname" 
                    inputId="surname"
                    class="w-100"
                    inputClass="w-100"
                    placeholder="Cognome" 
                    :class="{ 'is-invalid': errors.surname }"
                />
                <InputError class="mt-2" :message="errors.surname" />
            </div>
        </div>

        <div class="row pb-3">
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <InputText 
                    v-model="form.email"
                    type="email"
                    inputId="email"
                    class="w-100"
                    inputClass="w-100"
                    placeholder="Email"
                    :class="{ 'is-invalid': errors.email }"
                />
                <InputError class="mt-2" :message="errors.email" />
            </div>
            <div class="col-md-6">
                <label for="userGroup" class="form-label">Ruolo</label> <br>
                <Dropdown 
                    v-model="form.user_group_id" 
                    :options="userGroups" 
                    class="w-100"
                    inputClass="w-100"
                    optionLabel="name" 
                    optionValue="id"
                    inputId="userGroup"
                    placeholder="Seleziona un ruolo"
                    :class="{ 'is-invalid': errors.user_group_id }"
                />
                <InputError class="mt-2" :message="errors.user_group_id" />
            </div>
        </div>

        <div class="row pb-3">
            <div class="col-md-6">
                <label for="password" class="form-label">Password</label>
                <InputText 
                    type="password"
                    v-model="form.password"
                    inputId="password"
                    class="w-100"
                    inputClass="w-100"
                    placeholder="Password"
                    :class="{ 'is-invalid': errors.password }"
                />
                <InputError class="mt-2" :message="errors.password" />
            </div>
            <div class="col-md-6">
                <label for="password-confirmation" class="form-label">Conferma Password</label>
                <InputText 
                    type="password"
                    v-model="form.password_confirmation"
                    inputId="password-confirmation"
                    class="w-100"
                    inputClass="w-100"
                    placeholder="Conferma Password"
                    :class="{ 'is-invalid': errors.password_confirmation }"
                />
                <InputError class="mt-2" :message="errors.password_confirmation" />
            </div>
        </div>
    </form>
</template>
<style>
    
</style>
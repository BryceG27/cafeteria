<script setup>
import { reactive, computed, ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

import { required, minLength } from "@vuelidate/validators";

import FloatLabel from "primevue/floatlabel";
import InputText from "primevue/inputtext";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    errors: Object,
});

// Input state variables
const state = reactive({
    username: null,
    password: null,
});

// Validation rules
const rules = computed(() => {
    return {
        email: {
            required,
            minLength: minLength(3),
        },
        password: {
            required,
            minLength: minLength(5),
        },
    };
});

const register = ref(false);

const form = useForm({
    email : '',
    password : '' 
});

const registerForm = useForm({
    name : '',
    surname : '',
    email : '',
    child : '',
    password : '',
    password_confirmation : '',
    is_active : 1,
    user_group_id : 3,
})


// On form submission
async function onSubmit() {
    form.post(route('login'));
}

async function submitRegister() {
    registerForm.post(route('register'));
}

</script>

<template>
    <!-- Page Content -->
    <Head title="Login" />
    <BaseBackground image="/assets/media/photos/photo11@2x.jpg">
        <div class="row g-0 bg-primary-dark-op">
            <!-- Meta Info Section -->
            <div
                class="hero-static col-lg-4 d-none d-lg-flex flex-column justify-content-center"
            >
                <div class="p-4 p-xl-5 flex-grow-1 d-flex align-items-center">
                    <div class="w-100">
                        <Link
                            :href="route('dashboard')"
                            class="link-fx fw-semibold fs-2 text-white"
                            >
                            MILLE<span class="fw-normal">VOGLIE</span>
                        </Link>
                        <p class="text-white-75 me-xl-8 mt-2">
                            Benvenuto sul portale della Mensa Ranchibile. Effettua il login per accedere agli ordini e alla configurazione
                        </p>
                    </div>
                </div>
            <div
                class="p-4 p-xl-5 d-xl-flex justify-content-between align-items-center fs-sm"
            >
                <p class="fw-medium text-white-50 mb-0">
                <!-- <strong>{{ store.app.name + " " + store.app.version }}</strong>
                    &copy; {{ store.app.copyright }} -->
                </p>
                <ul class="list list-inline mb-0 py-2" v-if="false">
                    <li class="list-inline-item">
                        <a class="text-white-75 fw-medium" href="javascript:void(0)"
                        >Legal</a
                        >
                    </li>
                    <li class="list-inline-item">
                        <a class="text-white-75 fw-medium" href="javascript:void(0)"
                        >Contact</a
                        >
                    </li>
                    <li class="list-inline-item">
                        <a class="text-white-75 fw-medium" href="javascript:void(0)"
                        >Terms</a
                        >
                    </li>
                </ul>
            </div>
        </div>
        <!-- END Meta Info Section -->
    
        <!-- Main Section -->
        <div
            class="hero-static col-lg-8 d-flex flex-column align-items-center bg-body-extra-light"
        >
            <div class="p-3 w-100 d-lg-none text-center">
            <Link
                :href="route('dashboard')"
                class="link-fx fw-semibold fs-3 text-dark"
            >
                Mille<span class="fw-normal">Foglie</span>
            </Link>
        </div>
        <div class="p-4 w-100 flex-grow-1 d-flex align-items-center">
            <div class="w-100" v-if="!register">
                <!-- Header -->
                <div class="text-center mb-5">
                    <p class="mb-3">
                        <i class="fa fa-2x fa-circle-notch text-primary-light"></i>
                    </p>
                    <h1 class="fw-bold mb-2">Entra nel portale</h1>
                    <p class="fw-medium text-muted">
                        Benvenuto, effettua il login oppure
                        <button
                            type="button" 
                            class="btn-link link-primary"
                            @click="register = true"
                        >
                            registrati
                        </button>
                        per creare un nuovo account.
                    </p>
                </div>
                <!-- END Header -->
        
                <!-- Sign In Form -->
                <div class="row g-0 justify-content-center">
                    <div class="col-sm-8 col-xl-4">
                        <form @submit.prevent="onSubmit">
                            <div class="mb-4">
                                <FloatLabel variant="on">
                                    <InputText
                                        type="text"
                                        class="form-control form-control-lg form-control-alt py-3"
                                        id="login-username"
                                        name="login-username"
                                        :class="{
                                            'is-invalid': errors.email,
                                        }"
                                        v-model="form.email"
                                    />
                                    <label for="login-username">Email</label>
                                </FloatLabel>
                                <InputError class="mt-2" :message="errors.email" />
                            </div>
                            <div class="mb-4">
                                <FloatLabel variant="on">
                                    <InputText
                                        type="password"
                                        class="form-control form-control-lg form-control-alt py-3"
                                        id="login-password"
                                        name="login-password"
                                        :class="{
                                            'is-invalid': errors.password,
                                        }"
                                        v-model="form.password"
                                    />
                                    <label for="login-password">Password</label>
                                </FloatLabel>
                                <InputError class="mt-2" :message="errors.password" />
                            </div>
                            <div
                                class="d-flex justify-content-center align-items-center mb-4"
                            >
                                <!-- <div>
                                    <Link
                                        :href="{ name: 'auth-reminder3' }"
                                        class="text-muted fs-sm fw-medium d-block d-lg-inline-block mb-1"
                                        >
                                        Password dimenticata?
                                    </Link>
                                </div> -->
                                <div>
                                    <button type="submit" class="btn btn-lg btn-alt-primary">
                                        <i class="fa fa-fw fa-sign-in-alt me-1 opacity-50"></i>
                                        Entra
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- END Sign In Form -->
            </div>
            <div class="w-100" v-if="register">
                <div class="text-center mb-5">
                    <p class="mb-3">
                        <i class="fa fa-2x fa-circle-notch text-primary-light"></i>
                    </p>
                    <h1 class="fw-bold mb-2">Entra nel portale</h1>
                    <p class="fw-medium text-muted">
                        Benvenuto, effettua la registrazione per creare un nuovo account oppure
                        <button
                            type="button" 
                            class="btn-link link-primary"
                            @click="register = false"
                        >
                            effettua il login
                        </button>
                    </p>
                </div>

                <form @submit.prevent="submitRegister">
                    <div class="row g-0 justify-content-center">
                        <div class="col-xl-4 col-sm-8">
                            <!-- <input 
                                type="text" 
                                class="form-control form-control-lg form-control-alt py-3"
                                id="name"
                                name="name"
                                placeholder="Nome"
                                v-model="registerForm.name"
                            > -->
                            <FloatLabel variant="on">
                                <InputText 
                                    id="name" 
                                    class="form-control form-control-lg form-control-alt py-3"
                                    inputClass="py-3 w-100" 
                                    v-model="registerForm.name"
                                />
                                <label for="name">Nome</label>
                            </FloatLabel>
                            <InputError class="mt-2" :message="errors.name" />
                        </div>
                        <div class="col-xl-4 col-sm-8 offset-xl-1 offset-sm-0 mt-4 mt-sm-0">
                            <FloatLabel variant="on">
                                <InputText
                                    class="form-control form-control-lg form-control-alt py-3"
                                    inputClass="py-3 w-100"
                                    id="surname"
                                    name="surname"
                                    v-model="registerForm.surname"
                                />
                                <label for="surname">Cognome</label>
                            </FloatLabel>
                            <InputError class="mt-2" :message="errors.surname" />
                        </div>
                    </div>

                    <div class="row g-0 justify-content-center pt-4">
                        <div class="col-xl-4 col-sm-8">
                            <FloatLabel variant="on">
                                <InputText
                                    type="email"     
                                    class="form-control form-control-lg form-control-alt py-3"
                                    id="email"
                                    name="email"
                                    v-model="registerForm.email"
                                />
                                <label for="email">Email</label>
                            </FloatLabel>
                            <InputError class="mt-2" :message="errors.email" />
                        </div>
                        <div class="col-xl-4 col-sm-8 offset-xl-1 offset-sm-0 mt-4 mt-sm-0">
                            <FloatLabel variant="on">
                                <InputText
                                    type="text"     
                                    class="form-control form-control-lg form-control-alt py-3"
                                    id="child"
                                    name="child"
                                    v-model="registerForm.child"
                                />
                                <label for="child">Nome e Cognome figlio/a</label>
                            </FloatLabel>
                            <InputError class="mt-2" :message="errors.child" />
                        </div>
                    </div>

                    <div class="row g-0 justify-content-center pt-4">
                        <div class="col-xl-4 col-sm-8">
                            <FloatLabel variant="on">
                                <InputText 
                                    type="password"     
                                    class="form-control form-control-lg form-control-alt py-3"
                                    id="password"
                                    name="password"
                                    v-model="registerForm.password"
                                />
                                <label for="password">Password</label>
                            </FloatLabel>
                            <InputError class="mt-2" :message="errors.password" />
                        </div>
                        <div class="col-xl-4 col-sm-8 offset-xl-1 offset-sm-0 mt-4 mt-sm-0">
                            <FloatLabel variant="on">
                                <InputText 
                                    type="password"     
                                    class="form-control form-control-lg form-control-alt py-3"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    v-model="registerForm.password_confirmation"
                                />
                                <label for="password_confirmation">Conferma Password</label>
                            </FloatLabel>
                            <InputError class="mt-2" :message="errors.password_confirmation" />
                        </div>
                    </div>

                    <div class="row g-0 justify-content-center pt-4">
                        <div class="col-md-12 text-center">
                            <button class="btn btn-alt-warning btn-lg" type="submit">
                                <i class="fa fa-fw fa-check me-1 opacity-50"></i>
                                Registrati
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div
            class="px-4 py-3 w-100 d-lg-none d-flex flex-column flex-sm-row justify-content-between fs-sm text-center text-sm-start"
        >
            <p class="fw-medium text-black-50 py-2 mb-0">
            <!-- <strong>{{ store.app.name + " " + store.app.version }}</strong>
                &copy; {{ store.app.copyright }} -->
            </p>
            <ul class="list list-inline py-2 mb-0">
                <li class="list-inline-item">
                    <a class="text-muted fw-medium" href="javascript:void(0)">
                        Legal
                    </a>
                </li>
                <li class="list-inline-item">
                    <a class="text-muted fw-medium" href="javascript:void(0)">
                        Contact
                    </a>
                </li>
                <li class="list-inline-item">
                    <a class="text-muted fw-medium" href="javascript:void(0)">
                        Terms
                    </a>
                </li>
            </ul>
        </div>
    </div>
<!-- END Main Section -->
</div>
</BaseBackground>
<!-- END Page Content -->
</template>

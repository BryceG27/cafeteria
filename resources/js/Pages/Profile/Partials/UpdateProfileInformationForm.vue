<script setup>
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

import InputText from 'primevue/inputtext'
import TextArea from 'primevue/textarea'

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    id : user.id,
    name: user.name,
    surname : user.surname,
    email: user.email,
    child: user.child || '',
    child_allergies: user.child_allergies || '',
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Informazioni del profilo</h2>

            <p class="mt-1 text-sm text-gray-600">
                Aggiorna le informazioni del tuo account e l'indirizzo email.
            </p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="mt-6 space-y-6">
            <div>
                <label for="name" v-text="'Nome'" class="form-label" />

                <InputText
                    inputClass="w-100"
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <label for="surname" v-text="'Cognome'" class="form-label" />

                <InputText
                    inputClass="w-100"
                    id="surname"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.surname"
                    required
                    autofocus
                    autocomplete="surname"
                />

                <InputError class="mt-2" :message="form.errors.surname" />
            </div>

            <div>
                <label for="email" v-text="'Email'" class="form-label" />

                <InputText
                    inputClass="w-100"
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <label for="child" v-text="'Nome e cognome figlio'" class="form-label" />

                <InputText
                    inputClass="w-100"
                    id="child"
                    class="mt-1 block w-full"
                    v-model="form.child"
                    required
                />

                <InputError class="mt-2" :message="form.errors.child" />
            </div>

            <div>
                <label for="child" v-text="'Allergie figlio'" class="form-label" />

                <TextArea
                    inputClass="w-100"
                    id="child_allergies"
                    class="mt-1 block w-full"
                    v-model="form.child_allergies"
                    required
                />

                <InputError class="mt-2" :message="form.errors.child" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null || false">
                <p class="text-sm mt-2 text-gray-800">
                    Il tuo indirizzo email non è verificato.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Salva</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Salvato.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import BaseBlock from "@/Components/BaseBlock.vue";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

const props = defineProps({
    users: Array,
    userGroups: Array,
    auth: Object,
});

</script>
<template>
    <Head title="Utenti" />

    <AuthenticatedLayout>
        <div class="content">
            <SuccessMessage />
            <ErrorMessage />

            <BaseBlock title="Utenti" contentClass="pb-3">
                <template #options>
                    <Link 
                        class="btn btn-alt-primary btn-sm"
                        :href="route('users.create')"
                    >
                        <i class="fa fa-plus me-1"></i>
                        Nuovo
                    </Link>
                </template>

                <DataTable
                    stripedRows
                    :value="users"
                >
                    <template #empty>
                        <div class="p-4 text-center">
                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                            <p class="mt-2">Nessun utente inserito</p>
                        </div>
                    </template>
                    <Column field="id" class="text-center" v-if="auth.user.user_group_id == 1">
                        <template #body="{ data }">
                            <template v-if="data.user_group_id != 1">
                                <Link 
                                    :href="route('users.toggle-active', data.id)"
                                    method="put"
                                    as="button"
                                    class="btn btn-sm"
                                    :class="!data.is_active ? 'btn-alt-success' : 'btn-alt-warning'"
                                >
                                    <i class="fa" :class="!data.is_active ? 'fa-play' : 'fa-pause'"></i>
                                </Link>
                                <Link 
                                    :href="route('users.edit', data.id)"
                                    class="btn btn-sm btn-alt-info ms-2"
                                    v-if="data.user_group_id == 2"
                                >
                                    <i class="fa fa-pencil"></i>
                                </Link>
                            </template>
                        </template>
                    </Column>
                    <Column header="Utente">
                        <template #body="{ data }">
                            <span 
                                v-text="`${data.name} ${data.surname}`"
                            />
                        </template>
                    </Column>
                    <Column header="Ruolo" field="user_group.name" />
                    <Column field="is_active" header="Stato">
                        <template #body="{ data }">
                            <span v-if="data.is_active" class="badge bg-success">Attivo</span>
                            <span v-else class="badge bg-danger">Inattivo</span>
                        </template>
                    </Column>
                </DataTable>
            </BaseBlock>
        </div>
    </AuthenticatedLayout>
</template>
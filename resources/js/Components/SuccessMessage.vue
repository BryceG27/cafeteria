<template>
    <div v-if="$page.props.flash.message" class="alert alert-success alert-dismissible" role="alert">
        <h3 class="alert-heading h4 my-2">Successo</h3>
        <p class="mb-0"> {{ $page.props.flash.message }} </p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</template>
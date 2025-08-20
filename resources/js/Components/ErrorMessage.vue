<template>
    <div v-if="Object.keys($page.props.errors).length" class="alert alert-warning alert-dismissible" role="alert">
        <h3 class="alert-heading h4 my-2">Attenzione</h3>
        <p v-for="error in $page.props.errors" class="mb-0"> {{ error }} </p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</template>
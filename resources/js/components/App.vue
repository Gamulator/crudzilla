<template>
    <div>
        <b-container>
            <Menu />

            <b-card class="mt-2">
                <router-view />
            </b-card>
        </b-container>

        <vue-progress-bar />
    </div>
</template>

<script>
    import Menu from './Menu';

    export default {
        components: {
            Menu
        }
    }
</script>

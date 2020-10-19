<template>
    <div>
        <b-navbar toggleable="lg" type="dark" variant="dark">
            <b-navbar-brand href="#">CRUDzilla</b-navbar-brand>

            <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

            <b-collapse id="nav-collapse" is-nav>
                <b-navbar-nav>
                    <router-link
                        is="b-nav-item"
                        v-for="item in items"
                        :active="isActive(item.link)"
                        :key="item.title"
                        :content="item.title"
                        @click="select(item.title)"
                        :to="item.link"
                    ><b-icon :icon="item.icon" /> {{ item.title }}</router-link>
                </b-navbar-nav>
            </b-collapse>
        </b-navbar>
    </div>
</template>

<script>
    export default {
        name: 'UpperMenu',
        data() {
            return {
                active: 'Валюты',
                items: [{
                    icon: 'card-text',
                    title: 'Валюты',
                    link: '/'
                }, {
                    icon: 'info-circle',
                    title: 'О проекте',
                    link: '/about'
                }],
            };
        },
        methods: {
            isActive(link) {
                return link === this.$route.path;
            },
            select(name) {
                this.active = name;
            },
        },
    };
</script>

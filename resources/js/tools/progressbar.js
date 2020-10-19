import Vue from 'vue';
import VueProgressBar from 'vue-progressbar';

Vue.use(VueProgressBar, {
    color: 'rgb(9,171,210)',
    failedColor: 'red',
    height: '5px',
    transition: {
        speed: '0.4s',
        opacity: '0.6s',
        termination: 300
    },
});

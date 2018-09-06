Nova.booting((Vue, router) => {

    Vue.config.devtools = true

    Vue.component('nova-installer-navigation', require('./components/Navigation'));

    router.addRoutes([
        {
            name: 'nova-installer',
            path: '/nova-installer',
            component: require('./components/Tool'),
        },
    ])
})

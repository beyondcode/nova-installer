Nova.booting((Vue, router) => {
    Vue.component('nova-installer-navigation', require('./components/Navigation'));

    router.addRoutes([
        {
            name: 'nova-installer',
            path: '/nova-installer',
            component: require('./components/Tool'),
        },
    ])
})

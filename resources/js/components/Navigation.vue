<template>
    <div>
        <template v-for="tool in tools">
            <component v-bind:is="transformed(tool)" v-bind="$props"></component>
        </template>
    </div>
</template>

<script>
export default {
    data() {
        return {
            tools: [],
            scripts: [],
            styles: [],
        }
    },

    watch: {
        scripts(val) {
            for (let scriptName in val) {
                const script = document.createElement('script');
                script.src = `/nova-api/scripts/${scriptName}`;
                script.onload = () => {
                    window.Nova.boot();
                };
                document.head.appendChild(script);
            }
        }
    },

    methods: {
        transformed(html) {
            return {
                template: html,
                props: this.$options.props
            }
        },
    }
}
</script>

<style>
    /* Scoped Styles */
</style>

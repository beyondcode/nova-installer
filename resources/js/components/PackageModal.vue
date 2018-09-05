<template>
    <modal @modal-close="close">
        <div class="containerTty rounded shadow max-w-full max-h-screen overflow-y-scroll" style="width: calc(200vh - 16rem); height: calc(110vh - 16rem);">
                <div class="mb-1 absolute pin-t bg-white p-4 z-50 border-b w-full">
                    <h1>{{ selectedPackage.name }}</h1>
                    <p v-html="selectedPackage.abstract"></p>
                </div>

                <div class="mb-4 text-xs pt-auto description" v-if="!isInstalling">
                    <p v-html="selectedPackage.description_html"></p>
                    <p v-html="selectedPackage.instructions_html"></p>
                </div>

                <div class="relative overflow-y-scroll h-full console" v-if="isInstalling">
                    <div class="overflow-y-auto h-full" style="background-color: black;">
                        <pre
                            v-html="console"
                            class="p-4 font-mono text-white text-left leading-loose text-sm"
                        ></pre>
                    </div>
                </div>

            <div class="bg-white flex text-sm border-t px-6 py-4 items-center absolute pin-b w-full">
                <p class="flex-grow text-indigo font-bold no-underline uppercase text-xs hover:text-indigo-dark">{{ selectedPackage.author.name }}</p>

                <button v-if="!isInstalling"
                        @click="close"
                        class="btn btn-default btn-danger ">Close
                </button>

                <span v-if="installed"  class="text-success mt-1 ml-4 font-bold">Installed</span>
                <button
                        @click="requestInstallation(package)"
                        :class="{'btn-disabled': isInstalling}"
                        :disabled="isInstalling"
                        class="btn btn-default btn-primary justify-self-end" v-else>
                    <loader v-if="isInstalling && installingPackage === selectedPackage.composer_name" class="text-60" /> <span v-if="! isInstalling || installingPackage !== selectedPackage.composer_name ">Confirm Install</span>
                </button>
            </div>


        </div>
    </modal>
</template>

<script>
export default {
    props: ['selectedPackage', 'isInstalling', 'installingPackage', 'console', 'installedPackages'],

    data() {
        return {
            console: '',
            installed: this.isInstalled(),
        }
    },


    methods: {
        close() {
            this.$emit('close')
        },

        isInstalled() {

            return this.installedPackages.map(function(i) { return i.name; }).includes(this.selectedPackage.composer_name);

        },

        requestInstallation() {
            Nova.$emit('installation-requested', {requestedPackage: this.selectedPackage})
        }

    }
}
</script>

<style scoped>
    .console{
        margin-top: 6%;
        transition: height 0.5s linear;
    }

    .description{
        transition: height 0.5s linear;
        margin-top: 7%;
        margin-bottom: 7%;
        margin-left: 2%;
        margin-right: 2%;
    }

</style>

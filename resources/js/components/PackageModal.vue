<template>
    <modal
        class="modal"
        tabindex="-1"
        role="dialog"
        @modal-close="close"
    >
        <div class="containerTty rounded-lg shadow overflow-y-scroll" style="max-height: 80vh; width: 80vw;">
                <div class="mb-1 sticky items-center pin-t bg-white p-4 z-50 border-b w-full">
                    <h1 class="py-2">{{ selectedPackage.name }}</h1>
                    <p v-html="selectedPackage.abstract"></p>
                </div>

                <div class="mb-4 text-xs pt-auto p-4" v-if="showDescriptions">
                    <p v-html="selectedPackage.description_html"></p>
                    <p v-html="selectedPackage.instructions_html"></p>
                </div>

                <div class="relative overflow-y-scroll h-full w-full bg-90" v-if="showConsole" style="min-height: 200px;" id="console">
                    <div class="overflow-y-auto h-full w-full">
                        <pre
                            v-html="console"
                            class="p-4 w-full font-mono text-white text-left leading-loose text-sm"
                        ></pre>
                    </div>
                </div>

            <div class="bg-white flex text-sm border-t px-6 py-4 items-center sticky pin-b w-full">
                <p class="flex-grow text-indigo font-bold no-underline uppercase text-xs hover:text-indigo-dark">{{ selectedPackage.author.name }}</p>

                <button v-if="!isInstalling"
                        @click="close"
                        class="mr-2 btn btn-default btn-danger ">Close
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
    props: [
        'selectedPackage',
        'isInstalling',
        'installingPackage',
        'console',
        'installedPackages',
        'hasInstallationErrors',
    ],

    data() {
        return {
            console: '',
            hasHadErrors: false
        }
    },

    computed: {
        installed() {
            return this.installedPackages.map(function(i) { return i.name; }).includes(this.selectedPackage.composer_name);
        },

        showConsole() {
            return this.isInstalling || this.hasHadErrors
        },

        showDescriptions() {
            return ! this.showConsole
        }
    },

    watch: {
        console: function(newInput, oldInput){
            var console = document.getElementById("console");
            window.scrollTo(0, console.innerHeight);
        },

        hasInstallationErrors: function(newValue, oldValue){
            this.hasHadErrors = (!oldValue && newValue) ? true : false
        }
    },

    methods: {
        close() {
            this.$emit('close')
            Nova.$emit('installation-modal-closed')
        },

        requestInstallation() {
            Nova.$emit('installation-requested', {requestedPackage: this.selectedPackage})
        }

    }
}
</script>

<template>
    <div>
        <heading class="mb-6">Nova Package Installer</heading>

        <input
                type="text"
                @change="searchPackages"
                v-model="searchText"
                class="mb-4 w-full form-control form-input form-input-bordered"
                placeholder="Search packages"
        />

        <div class="flex items-center flex-no-shink flex-wrap -mx-3">
                <div class="w-1/4 px-3 flex flex-wrap justify-center sm:justify-start" v-for="package in availablePackages">
                    <div class="flex mb-4 shadow hover:shadow-md h-128 w-full" style="max-width: 380px;">
                        <div class="flex-1 bg-white text-sm border-solid border-t-4 rounded-sm border-indigo">
                            <div class="flex flex-row mt-4 px-4 pb-4" style="height: 14em;">
                                <div class="pb-2">
                                     <h2 class="text-xl text-grey-darkest mb-2">{{ package.name }}</h2>
                                    <div class="text-grey-darkest leading-normal mb-4 markdown leading-tight">{{ package.abstract }}</div>
                                </div>
                            </div> 
                            <div class="bg-grey-lighter flex text-sm border-t px-6 py-4 items-center">
                                <p class="flex-grow text-indigo font-bold no-underline uppercase text-xs hover:text-indigo-dark">{{ package.author.name }}</p>
                                <button
                                        @click="installPackage(package)"
                                        :class="{'btn-disabled': isInstalling}"
                                        :disabled="isInstalling"
                                        class="btn btn-default btn-primary justify-self-end">
                                    <loader v-if="isInstalling && installingPackage === package.composer_name" class="text-60" /> <span v-if="! isInstalling || installingPackage !== package.composer_name ">Install</span>
                                </button>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</template>

<script>

import axios from 'axios';

export default {
    data() {
        return {
            searchText: null,
            isInstalling: false,
            installingPackage: null,
            availablePackages: [],
        }
    },

    methods: {
        searchPackages() {
            axios.get(`/nova-vendor/beyondcode/nova-installer/packages/search?q=${this.searchText}`)
                .then(({data}) => {
                    this.availablePackages = data.data;
                });
        },

        async fetchRecent() {
            const data = await Nova.request().get('/nova-vendor/beyondcode/nova-installer/packages/recent');

            this.availablePackages = data.data.data;
        },

        installPackage(selectedPackage) {
            this.isInstalling = true;
            this.installingPackage = selectedPackage.composer_name;
            this.$toasted.show(`Triggered installation for "${selectedPackage.name}"`, { type: 'info', duration: 14000 });

            axios.post('/nova-vendor/beyondcode/nova-installer/install', {
                    package: selectedPackage.composer_name
                })
                .then(({data}) => {
                    this.$parent.$refs['nova-installer-navigation'].tools = data.tools;
                    this.$parent.$refs['nova-installer-navigation'].scripts = data.scripts;
                    this.$parent.$refs['nova-installer-navigation'].styles = data.styles;
                    this.isInstalling = false;
                    this.installingPackage = null;
                    this.$toasted.show(`Successfully installed ${selectedPackage.name}.`, { type: 'success' });
                })
                .catch(({data}) => {
                    this.isInstalling = false;
                    this.installingPackage = null;
                    this.$toasted.show(`There was an error when trying to install ${selectedPackage.name}. Please take a look at your log files.`, { type: 'error' });
                });
        }

    },

    mounted() {
        this.fetchRecent();
    },
}
</script>

<style>
    /* Scoped Styles */
</style>

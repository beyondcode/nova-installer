<template>
    <div>
        <heading class="mb-6">Nova Package Installer</heading>

        <input
                type="text"
                @input.stop="searchPackages"
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

                                <span v-if="isInstalled(package)"  class="text-success mt-3 mb-2 font-bold">Installed</span>
                                <button
                                        @click="show(package)"
                                        :class="{'btn-disabled': isInstalling}"
                                        :disabled="isInstalling"
                                        class="btn btn-default btn-primary justify-self-end" v-else>
                                    <loader v-if="isInstalling && installingPackage === package.composer_name" class="text-60" /> <span v-if="! isInstalling || installingPackage !== package.composer_name ">Install</span>
                                </button>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
        <InstalledPackages :installedPackages="installedPackages" />


        <portal to="modals">
            <transition name="fade">
                <PackageModal
                    v-if="showingPackage"
                    @close="showingPackage = null"
                    :selectedPackage="showingPackage"
                    :isInstalling="isInstalling"
                    :installingPackage="installingPackage"
                    :console="console"
                    :hasInstallationErrors="composerStatus.has_errors"
                    :installedPackages="installedPackages"
                />
            </transition>
        </portal>
    </div>
</template>

<script>

import InstalledPackages from './InstalledPackages';
import PackageModal from './PackageModal';

export default {

    components: {
        InstalledPackages,
        PackageModal
    },

    data() {
        return {
            searchText: null,
            isInstalling: false,
            installingPackage: null,
            showingPackage: null,
            availablePackages: [],
            installedPackages: [],
            composerStatus: [],
            console: '',
        }
    },

    methods: {
        searchPackages() {
            if (this.searchText.length < 3) {
                return;
            }
            this.debouncer(() => {
                Nova.request().get(`/nova-vendor/beyondcode/nova-installer/packages/search?q=${this.searchText}`)
                    .then(({data}) => {
                        this.availablePackages = data.data;
                    });
            })
        },

        async fetchRecent() {
            const data = await Nova.request().get('/nova-vendor/beyondcode/nova-installer/packages/recent');

            this.availablePackages = data.data.data;
        },

        async fetchInstalled() {
            const response = await Nova.request().get('/nova-vendor/beyondcode/nova-installer/packages/installed');

            this.installedPackages = Array.from(Object.keys(response.data), k=>response.data[k]);
        },

        installPackage(selectedPackage) {
            this.isInstalling = true;
            this.installingPackage = selectedPackage.composer_name;
            this.$toasted.show(`Installing "${selectedPackage.name}"`, { type: 'info', duration: 0 });

            Nova.request().post('/nova-vendor/beyondcode/nova-installer/install', {
                package: selectedPackage.composer_name,
                packageName: selectedPackage.name
            });

            this.startPolling();
        },

        debouncer: _.debounce(callback => callback(), 500),

        show(selectedPackage) {
            this.showingPackage = selectedPackage
        },

        startPolling() {
            this.poller = window.setInterval(() => {
                this.status();
            }, 1000);

            this.$once('hook:beforeDestroy', () => {
                this.stopPolling()
            });
        },

        stopPolling(){
            window.clearInterval(this.poller);
        },

        status(){

            Nova.request().get('/nova-vendor/beyondcode/nova-installer/composer-status')
            .then((response) => {

                this.composerStatus = response.data
                this.console = this.composerStatus.log

                if(this.composerStatus.finished_installation){


                    if(this.composerStatus.has_errors){

                        this.clearNotificationsNow();
                        this.$toasted.show(`There was an error when trying to install ${this.installingPackage}. Please take a look at your log files.`, { type: 'error', duration: 0 });


                    }else{
                        this.$parent.$refs['nova-installer-navigation'].tools = this.composerStatus.extras.tools;
                        this.$parent.$refs['nova-installer-navigation'].scripts = this.composerStatus.extras.scripts;
                        this.$parent.$refs['nova-installer-navigation'].styles = this.composerStatus.extras.styles;

                        this.clearNotificationsAfter(2000)
                        this.$toasted.show(`Successfully installed ${this.installingPackage}.`, { type: 'success' });


                        this.fetchInstalled()
                    }


                        this.isInstalling = false;
                        this.installingPackage = null;
                        this.stopPolling()

                        this.resetComposerStatus();

                }


            }).catch(({error}) => {
                this.isInstalling = false;
                this.installingPackage = null;
                this.$toasted.show(`There was an error when trying to install ${this.installingPackage}. Please take a look at your log files.`, { type: 'error' });
                this.stopPolling()

                this.clearNotificationsAfter(2000)
            });
        },

        clearNotificationsAfter(milliseconds){

            var _this = this

            setTimeout(function(){
                _this.$toasted.clear()
            }, 2000)
        },

        clearNotificationsNow(){
            this.$toasted.clear()
        },

        initialStatusCheck(){
            Nova.request().get('/nova-vendor/beyondcode/nova-installer/composer-status')
            .then((response) => {

                this.composerStatus = response.data

                if(this.composerStatus.is_running){

                    this.isInstalling = true
                    this.installingPackage = this.composerStatus.package

                    this.$toasted.show(`Installing "${this.composerStatus.packageName}"`, { type: 'info', duration: 0 });

                    this.startPolling()

                }
            })
        },

        resetComposerStatus(){
            Nova.request().get('/nova-vendor/beyondcode/nova-installer/composer-status-reset').then((response) => {
                // this.composerStatus = response.data
            });
        },

        isInstalled(currentPackage) {

            return this.installedPackages.map(function(i) { return i.name; }).includes(currentPackage.composer_name);

        },

    },

    mounted() {
        this.fetchRecent();
        this.fetchInstalled();
        this.initialStatusCheck();

        Nova.$on('installation-requested', payload => this.installPackage(payload.requestedPackage))
        Nova.$on('installation-modal-closed', () => this.console = '')
    },

    beforeDestroy() {
        Nova.$off('installation-requested');
    }
}
</script>

<style>
    /* Scoped Styles */
</style>

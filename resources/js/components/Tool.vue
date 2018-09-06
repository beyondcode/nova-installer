<template>
    <div>
        <heading class="mb-6">Nova Package Installer</heading>

        <tabs>
            <tab name="Install Packages">
                <div class="flex justify-between w-full">
                    <div class="relative h-9 flex items-center mb-6 w-full">
                        <icon type="search" class="absolute ml-3 text-70" />

                        <input
                            class="appearance-none form-control form-input w-search pl-search w-full"
                            placeholder="Search Packages"
                            type="search"
                            @input.stop="searchPackages"
                            v-model="searchText"
                        >
                    </div>
                </div>

                <div class="flex items-center flex-no-shink flex-wrap -mx-3">
                        <div class="w-1/4 px-3 flex flex-wrap justify-center sm:justify-start" v-for="package in availablePackages">
                            <div class="flex mb-4 shadow hover:shadow-md h-128 w-full" style="max-width: 380px;">
                                <div class="flex-1 bg-white text-sm border-solid border-t-4 rounded-sm border-indigo">
                                    <div class="flex flex-row mt-4 px-4 pb-4" style="height: 14em;">
                                        <div class="pb-2">
                                             <h2 class="text-xl text-grey-darkest mb-2">{{ package.name }}</h2>
                                            <div class="text-grey-darkest leading-normal mb-4 markdown leading-tight">{{ package.abstract }}</div>
                                            <button v-if="isInstalled(package)" @click="show(package)" class="text-info mt-3 mb-2 font-bold">View Package Details</button>
                                        </div>
                                    </div>
                                    <div class="bg-grey-lighter flex text-sm border-t px-6 py-4 items-center">
                                        <p class="flex-grow text-indigo font-bold no-underline uppercase text-xs hover:text-indigo-dark">{{ package.author.name }}</p>

                                        <button v-if="isInstalled(package)"
                                                @click="show(package)"
                                                :class="{'btn-disabled': isInstalling}"
                                                :disabled="isInstalling"
                                                class="btn btn-default btn-danger justify-self-end">
                                            <loader v-if="isInstalling && installingPackage === package.composer_name" class="text-60" /> <span v-if="! isInstalling || installingPackage !== package.composer_name ">Remove</span>
                                        </button>
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
            </tab>
            <tab name="Installed Packages">
                <InstalledPackages :installedPackages="installedPackages" />
            </tab>
        </tabs>


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
import axios from 'axios';
import InstalledPackages from './InstalledPackages';
import PackageModal from './PackageModal';
import {Tabs, Tab} from 'vue-tabs-component';

export default {

    components: {
        Tabs,
        Tab,
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
            currentAction: ''
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
            this.currentAction = 'install';
            this.installingPackage = selectedPackage.composer_name;
            this.$toasted.show(`Installing "${selectedPackage.name}"`, { type: 'info', duration: 0 });

            Nova.request().post('/nova-vendor/beyondcode/nova-installer/install', {
                package: selectedPackage.composer_name,
                packageName: selectedPackage.name
            });

            this.startPolling();
        },

        removePackage(selectedPackage) {
            this.isInstalling = true;
            this.currentAction = 'remove';
            this.installingPackage = selectedPackage.composer_name;
            this.$toasted.show(`Removing "${selectedPackage.name}"`, { type: 'info', duration: 0 });

            Nova.request().post('/nova-vendor/beyondcode/nova-installer/remove', {
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

            axios.get('/nova-vendor/beyondcode/nova-installer/composer-status')
            .then((response) => {

                this.composerStatus = response.data
                this.console = this.composerStatus.log

                if(this.composerStatus.finished_installation){


                    if(this.composerStatus.has_errors){

                        this.clearNotificationsNow();
                        this.$toasted.show(`There was an error when trying to ${this.currentAction} ${this.installingPackage}. Please take a look at your log files.`, { type: 'error', duration: 0 });


                    } else {
                        this.$parent.$refs['nova-installer-navigation'].tools = this.composerStatus.extras.tools;
                        this.$parent.$refs['nova-installer-navigation'].scripts = this.composerStatus.extras.scripts;
                        this.$parent.$refs['nova-installer-navigation'].styles = this.composerStatus.extras.styles;

                        this.clearNotificationsAfter(2000)
                        this.$toasted.show(`Successfully ${this.currentAction}ed ${this.installingPackage}.`, { type: 'success' });


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
                this.$toasted.show(`There was an error when trying to ${this.currentAction} ${this.installingPackage}. Please take a look at your log files.`, { type: 'error' });
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
        Nova.$on('removal-requested', payload => this.removePackage(payload.requestedPackage))
        Nova.$on('installation-modal-closed', () => this.console = '')
    },

    beforeDestroy() {
        Nova.$off('installation-requested');
    }
}
</script>

<style>
// Nova Tool CSS
.tabs-component {
  margin: 1em 1.5em;

}

.px-6 .tabs-component {
  margin-left: 0em;
  margin-right: 0em;
}

.tabs-component-tabs {
  border: solid 1px #ddd;
  border-radius: 6px;
  margin-bottom: 5px;
}

@media (min-width: 700px) {
  .tabs-component-tabs {
    border: 0;
    align-items: stretch;
    display: flex;
    justify-content: flex-start;
    margin-bottom: -1px;
  }
}

.tabs-component-tab {
  color: #999;
  font-size: 14px;
  font-weight: 600;
  margin-right: 0;
  list-style: none;
}

.tabs-component-tab:not(:last-child) {
  border-bottom: dotted 1px #ddd;
}

.tabs-component-tab:hover {
  color: #666;
}

.tabs-component-tab.is-active {
  color: #000;
}

.tabs-component-tab.is-disabled * {
  color: #cdcdcd;
  cursor: not-allowed !important;
}

@media (min-width: 700px) {
  .tabs-component-tab {
    background-color: var(--30);
    border: solid 1px #ddd;
    border-radius: 3px 3px 0 0;
    margin-right: .5em;
    transform: translateY(2px);
    transition: transform .3s ease;
  }

  .tabs-component-tab.is-active {
    border-bottom: solid 1px  var(--30);
    z-index: 2;
    transform: translateY(0);
  }
}

.tabs-component-tab-a {
  align-items: center;
  color: inherit;
  display: flex;
  padding: .75em 1em;
  text-decoration: none;
}

.tabs-component-panels {
  padding: 4em 0;
}

@media (min-width: 700px) {
  .tabs-component-panels {
    border-top-left-radius: 0;
    background-color: var(--30);
    border: solid 1px #ddd;
    border-radius: 0 6px 6px 6px;
    box-shadow: 0 0 10px rgba(0, 0, 0, .05);
    padding: 4em 2em;
  }
}
</style>

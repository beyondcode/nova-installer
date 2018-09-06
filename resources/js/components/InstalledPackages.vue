<template>
    <div>
            <div class="flex justify-between">

                <heading class="mb-6 mt-6">Installed Packages ({{ this.installedPackages.length }})</heading>

            </div>

            <div class="flex justify-between">
                <div class="relative h-9 flex items-center mb-6">
                    <icon type="search" class="absolute ml-3 text-70" />

                    <input
                        class="appearance-none form-control form-input w-search pl-search"
                        placeholder="Filter"
                        type="search"
                        v-model="search"
                    >
                </div>
            </div>

        <card>

            <div class="overflow-hidden overflow-x-auto relative">
                <table class="table w-full" cellpadding="0" cellspacing="0">
                    <thead>
                    <tr>
                        <th v-for="field in fields" class="text-left">
                                {{ field.label }}
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(package, index) in filteredInstalledPackages" :key="index">
                        <td v-for="field in fields">
                            <span class="whitespace-no-wrap text-left">
                                {{ package[field.attribute] }}
                            </span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </card>
    </div>
</template>

<script>
    export default {

        props: ['installedPackages'],

        data() {
            return {
                fields: [
                    {
                        label: 'Name',
                        attribute: 'name',
                    },
                    {
                        label: 'Description',
                        attribute: 'description',
                    },
                    {
                        label: 'Version',
                        attribute: 'version',
                    },
                    {
                        label: 'Author(s)',
                        attribute: 'authors',
                    }
                ],
                search: '',
            }
        },


        computed: {
            filteredInstalledPackages() {
                if (! this.search.length) {
                    return this.installedPackages;
                }
                const regex = this.searchRegex;
                // User input is not a valid regular expression, show no results
                if (! regex) {
                    return {};
                }
                return this.installedPackages.filter(novaPackage => {
                    let matchesSearch = false;
                    for (let key in novaPackage) {
                        if (Array.isArray(novaPackage[key])) {
                            novaPackage[key].forEach(property => {
                                if (regex.test(property)) {
                                    matchesSearch = true;
                                }
                            });
                        }
                        else if (regex.test(novaPackage[key])) {
                            matchesSearch = true;
                        }
                    }
                    return matchesSearch;
                });
            },
            searchRegex() {
                try {
                    return new RegExp('(' + this.search + ')','i');
                } catch (e) {
                    return false;
                }
            }
        }
    }
</script>

<style>
    /* Scoped Styles */
</style>

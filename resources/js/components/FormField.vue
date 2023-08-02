<template>
    <DefaultField :field="currentField" :full-width-content="currentField.fullWidth" :show-help-text="false">
        <template #field :class="{'border-danger border': hasErrors}">
            <div class="nova-attach-many" :class="{'border-danger border': hasErrors}">
                <div v-if="currentField.showToolbar" class="flex items-center border border-b-0 border-gray-100 dark:border-gray-700">
                    <div v-if="preview" class="flex justify-center p-3 w-full">
                        <div class="text-xl">{{ __('Selected Items') }} ({{ selected.length  }})</div>
                    </div>
                    <div v-else class="flex items-center w-full">
                        <div class="px-3">
                            <Checkbox @click="selectAll" :checked="selectingAll" class="cursor-pointer" />
                        </div>
                        <input
                            v-model="search"
                            type="search"
                            :placeholder="__('Search')"
                            class="w-full form-control form-input form-input-bordered" />
                    </div>
                </div>
                <div class="border border-gray-100 dark:border-gray-700 relative overflow-scroll" :style="{ height: currentField.height }" >
                    <div v-if="loading" class="flex justify-center" :style="{ height: currentField.height }">
                        <loader />
                    </div>
                    <CheckboxWithLabel
                        v-else
                        class="p-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                        v-for="resource in resources"
                        :key="resource.value"
                        :checked="selected.includes(resource.value)"
                        @input="toggle($event, resource.value)"
                    >
                        <div class="flex flex-col">
                            <div>{{ resource.display }}</div>
                            <div v-if="currentField.withSubtitles">
                                <span v-if="resource.subtitle">{{ resource.subtitle }}</span>
                                <span v-else>{{ __('No additional information...') }}</span>
                            </div>
                        </div>
                    </CheckboxWithLabel>
                </div>
            </div>

            <HelpText class="error-text mt-2 text-danger" v-if="hasErrors">
                {{ firstError }}
            </HelpText>

            <div class="help-text mt-3 w-full flex justify-between" :class="{ 'invisible': loading }">
                <span v-if="currentField.showCounts">
                    {{ selected.length  }} / {{ available.length }}
                </span>

                <span v-if="currentField.helpText">
                    <HelpText class="help-text"> {{ currentField.helpText }} </HelpText>
                </span>

                <span v-if="currentField.showPreview">
                    <CheckboxWithLabel @input="togglePreview($event)" :checked="preview" class="cursor-pointer">{{ __('Preview') }}</CheckboxWithLabel>
                </span>

                <span v-if="currentField.showRefresh" @click="refresh($event)" class="cursor-pointer">
                    <span>{{ __('Refresh') }}</span>
                </span>
            </div>

        </template>
    </DefaultField>
</template>

<script>
import {
    DependentFormField,
    HandlesValidationErrors
} from 'laravel-nova'

export default {
    mixins: [
        DependentFormField,
        HandlesValidationErrors,
    ],

    props: ['resourceName', 'resourceId', 'field'],

    data() {
        return {
            search: null,
            selected: [],
            selectingAll: false,
            available: [],
            preview: false,
            loading: true,
        }
    },

    methods: {
        setInitialValue() {
            this.retrieveData();
        },

        retrieveData(keepSelected=false) {
            let baseUrl = '/nova-vendor/nova-attach-many/';

            if(this.resourceId) {
                Nova.request(baseUrl + this.resourceName + '/' + this.resourceId + '/attachable/' + this.field.attribute)
                    .then((data) => {
                        if(keepSelected) {
                            this.selected = _.intersection(this.selected, _.map(data.data.available, 'value'));
                        } else {
                            this.selected = data.data.selected || [];
                        }
                        this.available = data.data.available || [];
                        this.loading = false;
                    });
            }
            else {
                Nova.request(baseUrl + this.resourceName + '/attachable/' + this.field.attribute)
                    .then((data) => {
                        this.available = data.data.available || [];
                        this.loading = false;
                    });
            }
        },

        fill(formData) {
            formData.append(this.field.attribute, this.value || [])
        },

        toggle(event, id){
            if(this.selected.includes(id)) {
                this.selected = this.selected.filter(selectedId => selectedId != id);
            }
            else {
                this.selected.push(id)
            }
        },

        refresh(event){
            this.loading = true;
            this.retrieveData(true);
        },

        selectAll() {
            var selected = this.selected;

            this.selectingAll = ! this.selectingAll;

            // search can return 0 results
            if(this.resources.length == 0) {
                return;
            }

            if(this.resources.length == 1 && this.selected == 1)
            {
                this.selected = [];
            }

            // add all resources
            if(! this.search && this.selectingAll) {
                selected = [];
                this.resources.forEach(resource => {
                    selected.push(resource.value)
                })
            }

            // remove all resources
            if(! this.search && ! this.selectingAll) {
                selected = [];
            }

            // append searched resources
            if(this.search && this.selectingAll) {
                this.resources.forEach(resource => {
                    selected.push(resource.value)
                })
            }

            // remove only searched items
            if(this.search && ! this.selectingAll) {

                let exludingIds = [];

                this.resources.forEach(resource => {
                    exludingIds.push(resource.value);
                })

                selected = selected.filter(id => exludingIds.includes(id) == false);
            }

            this.selected = selected;
        },
        clearSearch()
        {
            this.selectingAll = false;
            this.search = null;
        },
        checkIfSelectAllIsActive() {

            if(this.resources.length === 0 || this.preview) {
                this.selectingAll = false; return;
            }

            let visibleAndSelected = [];

            this.resources.forEach(resource => {
                if(this.selected.includes(resource.value)) {
                    visibleAndSelected.push(resource.value);
                }
            })

            this.selectingAll = visibleAndSelected.length == this.resources.length;
        },
        togglePreview(event){
            this.preview = ! this.preview;
        }
    },
    computed: {
        resources: function() {
            if(this.preview) {
                return this.available.filter((resource) => {
                    return this.selected.includes(resource.value)
                });
            }

            if(this.search == null) {
                return this.available;
            }

            return this.available.filter((resource) => {
                return resource.display.toLowerCase().includes(this.search.toLowerCase())
            });
        },
        hasErrors: function() {
            return this.errors.errors.hasOwnProperty(this.field.attribute);
        },
        firstError: function() {
            return this.errors.errors[this.field.attribute][0]
        }
    },
    watch: {
        'search': {
            handler: function(search) {
                this.checkIfSelectAllIsActive();
            }
        },
        'selected': {
            handler: function (selected) {
                this.value = JSON.stringify(selected);
                this.checkIfSelectAllIsActive();
            },
            deep: true
        }
    }
}
</script>

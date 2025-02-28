<template>
    <div class="flex flex-col flex-auto ns-select">
        <label :for="field.name" :class="hasError ? 'has-error' : 'is-pristine'" class="block leading-5 font-medium"><slot></slot></label>
        <div :class="hasError ? 'has-error' : 'is-pristine'" class="border-2 mt-1 relative rounded-md shadow-sm mb-1 flex overflow-hidden">
            <div @click="showResults = ! showResults" class="flex-auto h-10 sm:leading-5 py-2 px-4 flex items-center bg-input-background cursor-default">
                <span class="text-primary text-sm">{{  selectedOption }}</span>
            </div>
            <div v-if="field.component" @click="triggerDynamicComponent( field.component )" class="flex items-center justify-center w-10 hover:cursor-pointer hover:bg-input-button-hover border-l-2 border-input-edge">
                <i class="las la-plus"></i>
            </div>
        </div>
        <div class="relative" v-if="showResults">
            <div class="w-full overflow-hidden -top-[8px] border-r-2 border-l-2 border-t rounded-b-md border-b-2 border-input-edge bg-input-background shadow z-10 absolute">
                <div class="border-b border-input-edge border-dashed p-2">
                    <input @keypress.enter="selectFirstOption()" ref="searchInputField" v-model="searchField" type="text" :placeholder="__( 'Search result' )">
                </div>
                <div class="h-60 overflow-y-auto">
                    <ul>
                        <li @click="selectOption( option )" v-for="option of filtredOptions" class="py-1 px-2 hover:bg-info-primary cursor-pointer text-primary">{{ option.label }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <ns-field-description :field="field"></ns-field-description>
    </div>
</template>
<script lang="ts">
import { nsSnackBar } from '~/bootstrap';
import { __ } from '~/libraries/lang';
import { Popup } from '~/libraries/popup';

declare const nsExtraComponents: any;

export default {
    data: () => {
        return {
            selectedOption: __( 'Choose...' ),
            searchField: '',
            showResults: false,
        }
    },
    name: 'ns-search-select',
    props: [ 'name', 'placeholder', 'field', 'leading' ],
    computed: {
        filtredOptions() {
            if ( this.searchField.length > 0 ) {
                return this.field.options.filter( option => {
                    const expression    =   ( new RegExp( this.searchField, 'i' ) );
                    
                    return expression.test( option.label );
                }).splice(0,10);
            } else {
                return this.field.options;
            }
        },
        hasError() {
            if ( this.field.errors !== undefined && this.field.errors.length > 0 ) {
                return true;
            }
            return false;
        },
        disabledClass() {
            return this.field.disabled ? 'ns-disabled cursor-not-allowed' : '';
        },
        inputClass() {
            return this.disabledClass + ' ' + this.leadClass
        },
        leadClass() {
            return this.leading ? 'pl-8' : 'px-4';
        }
    },
    watch: {
        showResults() {
            if ( this.showResults === true ) {
                setTimeout( () => {
                    this.$refs.searchInputField.select();
                }, 50 );
            }
        }
    },
    mounted() {
        const options   =   this.field.options.filter( op => op.value === this.field.value );

        if ( options.length > 0 ) {
            this.selectOption( options[0] );
        }
    },
    methods: { 
        __,
        selectFirstOption() {
            if ( this.filtredOptions.length > 0 ) {
                this.selectOption( this.filtredOptions[0] );
            }
        },
        selectOption( option ) {
            this.selectedOption =   option.label || __( 'Choose...' )
            this.field.value    =   option.value;
            this.$emit( 'change', option.value );
            this.searchField    =   '';
            this.showResults    =   false;
        },
        async triggerDynamicComponent( componentName ) {
            try {
                const component =   nsExtraComponents[ componentName ];

                if ( component === undefined ) {
                    nsSnackBar.error( __( `The component ${componentName} cannot be loaded. Make sure it's injected on nsExtraComponents object.` ) ).subscribe();
                }

                const result = await new Promise( ( resolve, reject ) => {
                    const response  =   Popup.show( nsExtraComponents[ componentName ], { field: this.field, resolve, reject } );
                });
            } catch ( error ) {
                console.log( error );
            }
        },
    },
}
</script>
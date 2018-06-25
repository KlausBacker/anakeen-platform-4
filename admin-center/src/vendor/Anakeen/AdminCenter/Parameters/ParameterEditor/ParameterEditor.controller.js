export default {
    name: 'ank-parameter-editor',

    props: {
        editedItem: {
            type: Object,
            default: {},
        },
    },

    data() {
        //
    },

    methods: {
        openEditor() {
            if (this.editedItem) {
                this.$('#edition-window').kendoWindow({
                    modal: true,
                    draggable: false,
                    resizable: false,
                    width: '60%',
                    title: this.editedItem.name,
                    visible: false,
                    actions: ['Close'],

                    close: () => this.$emit('closeEditor'),
                }).data('kendoWindow').center().open();
            }
        },

        closeEditor() {
            this.$('#edition-window').data('kendoWindow').close();
            this.$emit('closeEditor');
        },

        modifyParameter() {
            let newValue = this.$('#parameter-new-value').val();
            console.log(newValue);
            Vue.ankApi.put('admin/parameters/' + this.editedItem.name,
                {
                    value: newValue,
                })
                .then(() => {
                    // TODO Display popup
                    // TODO On 'Ok' close editor
                })
                .catch(() => {
                    // TODO Display popup
                });
        },
    },

    computed: {
        // Pretty format to display parameter type
        formatedParameterType() {
            if (this.editedItem) {
                let type = this.editedItem.type;
                if (type.startsWith('enum')) {
                    return 'Enum';
                }

                return this.editedItem.type.charAt(0).toUpperCase() + this.editedItem.type.slice(1);
            }

            return '';
        },

        parameterInputType() {
            let parameterType = this.editedItem.type.toLowerCase();
            if (parameterType === 'text') {
                return 'text';
            } else if (parameterType === 'number' || parameterType === 'integer' || parameterType === 'double') {
                return 'number';
            } else if (parameterType.startsWith('enum')) {
                return 'enum';
            }
        },

        enumPossibleValues() {
            if (this.parameterInputType === 'enum') {
                let rawEnum = this.editedItem.type;
                rawEnum = rawEnum.slice(5);
                rawEnum = rawEnum.slice(0, -1);
                return rawEnum.split('|');
            }
        },
    },

    updated() {
        // Show modal
        this.openEditor();
    },
};

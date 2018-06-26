export default {
    name: 'ank-parameter-editor',

    props: {
        editedItem: {
            type: Object,
            default: {},
        },
    },

    data() {
        // No data
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
        },

        modifyParameter() {
            let newValue = this.$('#parameter-new-value').val();
            Vue.ankApi.put('admin/parameters/' + this.editedItem.domainName + '/' + this.editedItem.name + '/',
                {
                    value: newValue,
                })
                .then(() => {
                    this.$('#confirmation-window').kendoWindow({
                        modal: true,
                        draggable: false,
                        resizable: false,
                        title: 'Parameter modified',
                        width: '20%',
                        visible: false,
                        actions: [],
                    }).data('kendoWindow').center().open();
                })
                .catch(() => {
                    this.$('#error-window').kendoWindow({
                        modal: true,
                        draggable: false,
                        resizable: false,
                        title: 'Error',
                        width: '20%',
                        visible: false,
                        actions: [],
                    }).data('kendoWindow').center.open();
                });
        },

        closeConfirmationAndEditor() {
            this.$('#confirmation-window').data('kendoWindow').close();
            this.closeEditor();
        },

        closeErrorAndEditor() {
            this.$('#error-window').data('kendoWindow').close();
            this.closeEditor();
        },
    },

    computed: {
        parameterInputType() {
            let parameterType = this.editedItem.type.toLowerCase();
            if (parameterType === 'text') {
                return 'text';
            } else if (parameterType === 'password') {
                return 'password';
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

    mounted() {
        // When resizing the browser window, resize and center the edition window
        window.addEventListener('resize', () => {
            let editionWindow = this.$('#edition-window').data('kendoWindow');
            if (editionWindow) {
                editionWindow.setOptions({
                    width: '60%',
                });
                editionWindow.center();
            }
        });
    },
};

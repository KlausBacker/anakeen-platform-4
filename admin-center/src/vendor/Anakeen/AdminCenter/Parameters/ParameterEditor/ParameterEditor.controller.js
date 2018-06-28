import JSONEditor from 'jsoneditor';
import 'jsoneditor/dist/jsoneditor.min.css';

export default {
    name: 'admin-center-parameter-editor',

    props: {
        editedItem: {
            type: Object,
            default: {},
        },
    },

    data() {
        return {
            jsonEditor: {},
            jsonValue: {},
        };
    },

    methods: {
        openEditor() {
            if (this.editedItem) {
                this.$('#edition-window').kendoWindow({
                    modal: true,
                    autoFocus: false,
                    draggable: false,
                    resizable: false,
                    width: '60%',
                    title: this.editedItem.name,
                    visible: false,
                    actions: ['Close'],
                    activate: () => this.$('#parameter-new-value').focus(),
                    close: () => this.$emit('closeEditor'),
                }).data('kendoWindow').center().open();

                this.$('#parameter-new-value').css('border-color', '');

                if (this.parameterInputType === 'json' && this.isJson(this.editedItem.value)) {
                    this.jsonValue = JSON.parse(this.editedItem.value);
                    let divContainer = document.getElementById('json-parameter-new-value');
                    this.jsonEditor = new JSONEditor(divContainer, {
                        search: false,
                        navigationBar: false,
                        statusBar: false,
                        history: false,
                    }, this.jsonValue);
                }
            }
        },

        closeEditor() {
            if (this.parameterInputType === 'json' && this.isJson(this.editedItem.value)) {
                this.jsonEditor.destroy();
            }

            this.$('#edition-window').data('kendoWindow').close();
        },

        modifyParameter() {
            let newValue;
            if (this.parameterInputType === 'json' && this.isJson(this.editedItem.value)) {
                newValue = JSON.stringify(this.jsonEditor.get());
            } else if (this.parameterInputType === 'json' && !this.isJson(this.$('#parameter-new-value').val())) {
                this.$('#parameter-new-value').css('border-color', 'red');
                return;
            } else {
                this.$('#parameter-new-value').css('border-color', '');
                newValue = this.$('#parameter-new-value').val();
            }

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
                        width: '30%',
                        visible: false,
                        actions: [],
                    }).data('kendoWindow').center().open();
                });
        },

        closeConfirmationAndEditor() {
            this.$('#confirmation-window').data('kendoWindow').close();
            this.closeEditor();
        },

        isJson(stringValue) {
            try {
                JSON.parse(stringValue);
                return true;
            } catch (e) {
                return false;
            }
        },
    },

    computed: {
        parameterInputType() {
            let parameterType = this.editedItem.type.toLowerCase();
            if (parameterType === 'number' || parameterType === 'integer' || parameterType === 'double') {
                return 'number';
            } else if (parameterType.startsWith('enum')) {
                return 'enum';
            } else {
                return parameterType;
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

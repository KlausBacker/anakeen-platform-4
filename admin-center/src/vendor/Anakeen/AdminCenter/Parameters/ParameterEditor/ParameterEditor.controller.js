export default {
    name: 'ank-parameter-editor',

    props: {
        editedItem: {
            type: Object,
            default: {},
        },
    },

    data() {
        return {
            //
        };
    },

    methods: {
        modifyParameter(type) {
            // TODO Send request to modify the parameter
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

        enumPossibleValues() {

        }
    },
};

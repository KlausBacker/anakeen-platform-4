import { AnkMixin } from '../AnkVueComponentMixin';
// noinspection JSUnusedGlobalSymbols
export default {
    name: 'ank-authent-password',
    mixins: [AnkMixin],
    props: {
        label: {
            type: String,
            default: 'Password',
        },
        placeholder: {
            type: String,
            default: '',
        },

        validationMessage: {
            type: String,
            default: '',
        },
    },
    data() {
        return {
            value: '',
            pwdId: 'pwd' + this._uid,
        };
    },

    mounted() {
        let $ = this.$kendo.jQuery;
        let $input = $(this.$refs.authentPassword).find('input');
        let _this = this;

        $(this.$refs.authentReveal).on('click', function revealPass() {
            let $pwd = $(this).closest('.input-group').find('input');
            if ($pwd.attr('type') === 'password') {
                $pwd.attr('type', 'text');
                $(this).find('.fa').removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                if ($pwd.attr('type') === 'text') {
                    $pwd.attr('type', 'password');
                    $(this).find('.fa').addClass('fa-eye').removeClass('fa-eye-slash');
                }
            }
        });

        $input.on('input, invalid', function requireMessage() {
            if (this.value === '' && _this.validationMessage) {
                this.setCustomValidity(_this.validationMessage);
            } else {
                this.setCustomValidity('');
            }
        });

        $input.trigger('input');
    },

    methods: {
        $changePassword() {
            this.$emit('input', this.value);
        },

        setValue(value) {
            this.value = value;
        },
    },
};

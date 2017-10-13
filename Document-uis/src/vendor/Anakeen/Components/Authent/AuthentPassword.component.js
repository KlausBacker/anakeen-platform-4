
// noinspection JSUnusedGlobalSymbols
export default {
    name: 'a4-input-password',
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
        let validationMessage = this.validationMessage;

        $(this.$refs.authentReveal).on('click', function revealPass() {
            let $pwd = $(this).closest('.input-group-btn').find('input');
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

        $(this.$refs.authentPassword).find('input').on('change', function requireMessage() {
            if (this.value === '' && validationMessage) {
                this.setCustomValidity(validationMessage);
            } else {
                this.setCustomValidity('');
            }
        });
    },

    methods: {
        changePassword() {
            this.$emit('input', this.value);
        },
    },
};

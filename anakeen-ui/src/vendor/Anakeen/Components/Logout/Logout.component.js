export default {
    name: 'ank-logout',

    props: {
        title: {
            type: String,
            default: 'Logout'
        },
    },

    methods: {
        logout() {
            let event = new CustomEvent('beforeLogout', {cancelable: true});
            this.$el.parentNode.dispatchEvent(event);
            if (event.defaultPrevented) {
                this.$emit('logoutCanceled');
            } else {
                this.$http.delete('/components/logout/session')
                    .then(response => {
                        this.$emit('afterLogout', response.data);
                        document.location.assign(response.data.location || '/');
                    })
                    .catch(error => {
                        if (error.status === 401) {
                            this.$emit('afterLogout', error.data);
                            document.location.assign(error.data.location || '/');
                        }
                    });
            }
        }
    },

    computed: {
        translations() {
            return {
                title: this.$pgettext('Logout', 'Logout')
            }
        }
    }
}
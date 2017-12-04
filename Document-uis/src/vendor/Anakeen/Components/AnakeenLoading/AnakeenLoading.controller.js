export default {
    props: {
        color: {
            type: String,
            default: 'white',
            validator: (value) => {
                return value === 'black' || value === 'white';
            },
        },
        width: {
            type: String,
            default: 'auto',
        },
        height: {
            type: String,
            default: 'auto',
        },
    },
    computed: {
        viewBox() {
            return '0 0 400 120';

        },
    },
};

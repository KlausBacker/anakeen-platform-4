module.exports = {
    "env": {
        "browser": true
    },
    "extends": "eslint:recommended",
    "parserOptions": {
        "ecmaVersion": 5
    },
    "rules": {
        "prettier/prettier": "error",
        "linebreak-style": [
            "error",
            "unix"
        ],
        "semi": [
            "error",
            "always"
        ],
        "no-console": ["error", { allow: ["warn", "error", "timeEnd", "time"] }]
    },
    "globals": {
        i18n: false,
        jQuery: false,
        $: false,
        kendo: false,
        define: false,
        _: false,
        Mustache: false
    },
    "plugins": [
        "prettier"
    ]
};
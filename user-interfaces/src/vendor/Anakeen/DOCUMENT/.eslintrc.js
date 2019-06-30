module.exports = {
    "env": {
        "browser": true
    },
    "extends": "eslint:recommended",
    "parserOptions": {
        "ecmaVersion": 2015
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
        "no-console": ["error", { allow: ["warn", "error", "timeEnd", "time"] }],
        "no-redeclare": 0,
        "no-prototype-builtins": 0
    },
    "globals": {
        i18n: false,
        jQuery: false,
        $: false,
        kendo: false,
        _: false,
        Mustache: false,
        define: false
    },
    "plugins": [
        "prettier"
    ]
};
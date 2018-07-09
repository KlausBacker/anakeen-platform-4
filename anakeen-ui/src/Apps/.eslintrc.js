module.exports = {
    "env": {
        "browser": true
    },
    "extends": "eslint:recommended",
    "parserOptions": {
        "ecmaVersion": 5
    },
    "rules": {
        "indent": [
            "error",
            4
        ],
        "linebreak-style": [
            "error",
            "unix"
        ],
        "semi": [
            "error",
            "always"
        ],
        "console": {"allow": ["warn", "error"]}
    },
    "globals": {
        $: false,
        kendo: false,
        define: false,
        _: false,
        Mustache: false
    }
};
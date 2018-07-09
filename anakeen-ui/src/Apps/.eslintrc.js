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
        "no-console": ["error", { allow: ["warn", "error"] }]
    },
    "globals": {
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
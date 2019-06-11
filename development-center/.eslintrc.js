module.exports = {
  "env": {
    "browser": true,
    "node": true,
    "es6": true
  },
  "extends": "eslint:recommended",
  "parser": "babel-eslint",
  "parserOptions": {
    "ecmaVersion": 2018,
    "sourceType": "module"
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
    "no-console": ["error", {allow: ["warn", "error"]}]
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
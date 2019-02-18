module.exports = {
  env: {
    node: true,
    commonjs: true,
    es6: true
  },
  extends: "eslint:recommended",
  parserOptions: {
    ecmaVersion: 2018,
    sourceType: "module"
  },
  rules: {
    "prettier/prettier": "error",
    "linebreak-style": ["error", "unix"],
    semi: ["error", "always"],
    "no-console": "off"
  },
  globals: {
    i18n: false,
    $: false,
    kendo: false,
    define: false,
    _: false,
    Mustache: false
  },
  plugins: ["prettier"]
};

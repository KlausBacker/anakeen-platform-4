module.exports = {
  env: {
    node: true,
    es6: true,
    browser: true,
    commonjs: true,
    mocha: true
  },
  extends: "eslint:recommended",
  parserOptions: {
    ecmaVersion: 2018,
    sourceType: "module"
  },
  rules: {
    "prettier/prettier": "error",
    "linebreak-style": [
      "error",
      "unix"
    ],
    semi: [
     "error",
      "always"
    ],
    "no-console": ["error", {allow: ["warn", "error"]}]
  },
  globals: {
    $: false,
    kendo: false,
    Mustache: false
  },
  plugins: [
    "prettier"
  ]
};
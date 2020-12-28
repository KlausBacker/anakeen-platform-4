module.exports = {
  parser: "@typescript-eslint/parser",
  parserOptions: {
    ecmaVersion: 2018,
    sourceType: "module"
  },
  env: {
    node: true,browser: true, es6: true
  },
  extends: [
    "eslint:recommended",
    //"plugin:react/recommended"
  ],
  overrides: [
    {
      files: ["**/*.react.js"],
      extends: ["eslint:recommended",   "plugin:react/recommended","plugin:prettier/recommended"],
      plugins: ["prettier", "@typescript-eslint"]
    },


  ]
};

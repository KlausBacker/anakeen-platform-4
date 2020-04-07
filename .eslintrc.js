module.exports = {
  parser: "@typescript-eslint/parser",

  parserOptions: {
    ecmaVersion: 2018,
    sourceType: "module"
  },

  env: { browser: true, es6: true },

  ignorePatterns: ["constants/", "devtools", "dist/", "node_modules", "src/public", "stubs"],

  rules: {
    "prettier/prettier": "error",
    "linebreak-style": ["error", "unix"],
    semi: ["error", "always"],
    "no-console": ["error", { allow: ["warn", "error"] }]
  },

  overrides: [
    {
      files: ["**/*.js"],
      extends: ["eslint:recommended", "plugin:prettier/recommended"],
      plugins: ["prettier", "@typescript-eslint"]
    },
    {
      files: ["**/*.ts", "**/*.tsx"],
      rules: {
        "@typescript-eslint/interface-name-prefix": 0,
        "@typescript-eslint/camelcase": 0
      },
      extends: [
        "eslint:recommended",
        "plugin:@typescript-eslint/eslint-recommended",
        "plugin:@typescript-eslint/recommended",
        "plugin:prettier/recommended"
      ]
    },
    {
      files: ["**/*.vue"],
      extends: ["plugin:vue/recommended", "eslint:recommended", "prettier/vue", "plugin:prettier/recommended"]
    }
  ]
};

module.exports = {
  parser: "@typescript-eslint/parser",

  parserOptions: {
    ecmaVersion: 2018,
    sourceType: "module"
  },

  overrides: [
    {
      files: ["**/*.js"],
      env: { browser: true, es6: true },
      extends: ["eslint:recommended", "plugin:prettier/recommended"],
      rules: {
        "prettier/prettier": "error",
        "linebreak-style": ["error", "unix"],
        semi: ["error", "always"],
        "no-console": ["error", { allow: ["warn", "error"] }]
      },
      plugins: ["prettier", "@typescript-eslint"],
    },
    {
      files: ["**/*.ts", "**/*.tsx"],
      env: { browser: true, es6: true },
      extends: [
        "eslint:recommended",
        "plugin:@typescript-eslint/eslint-recommended",
        "plugin:prettier/recommended"
      ],
      parserOptions: {
        ecmaVersion: 2018,
        sourceType: "module",
        project: "./tsconfig.json"
      }
    },
    {
      files: ["**/*.vue"],
      env: { browser: true, es6: true },
      extends: [
        "plugin:vue/recommended"
      ]
    }
  ]
};

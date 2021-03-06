module.exports = {
  env: {
    node: true,
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
    "no-console": ["error", { allow: ["warn", "error"] }],
    "no-prototype-builtins": 0
  },
  plugins: ["prettier"]
};

const Configuration = {
  extends: ["@commitlint/config-conventional"],
  rules: {
    "scope-enum": [
      2,
      "always",
      ["control", "config", "internal", "route", "i18n", "script", "component"]
    ]
  }
};

module.exports = Configuration;

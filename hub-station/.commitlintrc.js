const Configuration = {
  extends: ["@commitlint/config-conventional"],
  rules: {
    "scope-enum": [
      2,
      "always",
      ["internal", "config", "route", "i18n", "script", "component"]
    ]
  }
};

module.exports = Configuration;

const Configuration = {
  extends: ["@commitlint/config-conventional"],
  rules: {
    "scope-enum": [2, "always", ["config", "route", "i18n", "script"]]
  }
};

module.exports = Configuration;

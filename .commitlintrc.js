const Configuration = {
  extends: ["@commitlint/config-conventional"],
  rules: {
    "scope-enum": [
      2,
      "always",
      [
        "admin",
        "cli",
        "business-app",
        "control",
        "dev-data",
        "dev-center",
        "hub",
        "internal",
        "validation",
        "security",
        "sde",
        "theme",
        "te",
        "ui",
        "workflow",
        "tools",
        "i18n",
        "test-tools",
        "migration-tools"
      ]
    ]
  }
};

module.exports = Configuration;

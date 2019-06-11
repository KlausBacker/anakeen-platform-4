const Configuration = {
  extends: ['@commitlint/config-conventional'],
  rules: {
    'scope-enum': [2, 'always', ['config', 'i18n', 'route', 'internal', 'script']]
  }
};

module.exports = Configuration;

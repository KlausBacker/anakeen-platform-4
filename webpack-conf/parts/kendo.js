/**
 * Add false kendo rules to remove parts not needed by the application
 *
 * @param additionalRules
 * @returns {{externals: Function[]}}
 */
exports.addFalseKendoGlobal = (additionalRules = []) => {
  const rules = additionalRules;
  return {
    externals: [
      (context, request, callback) => {
        if (
          rules.find(regexp => {
            return regexp.test(request);
          }) !== undefined
        ) {
          return callback(null, "root kendo");
        }
        return callback();
      }
    ]
  };
};

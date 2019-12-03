/**
 * Add false kendo rules to remove parts not needed by the application
 *
 * @param additionalRules
 * @returns {{externals: Function[]}}
 */

const kendoBasesRules = [
  /kendo\.autocomplete/,
  /kendo\.menu/,
  /kendo\.combobox/,
  /kendo\.list/,
  /kendo\.dropdownlist/,
  /kendo\.slider/,
  /kendo\.dateinput/,
  /kendo\.validator/,
  /kendo\.listview/,
  /kendo\.core/,
  /kendo\.mobile.scroller/,
  /kendo\.editable/,
  /kendo\.datetimepicker/,
  /kendo\.window/,
  /kendo\.tabstrip/,
  /kendo\.userevents/,
  /kendo\.numerictextbox/,
  /kendo\.timepicker/,
  /kendo\.calendar/,
  /kendo\.fx/,
  /kendo\.draganddrop/,
  /kendo\.color/,
  /kendo\.binder/,
  /kendo\.colorpicker/,
  /kendo\.data\.odata/,
  /kendo\.notification/,
  /kendo\.button/,
  /kendo\.popup/,
  /kendo\.multiselect/,
  /kendo\.selectable/,
  /kendo\.data$/,
  /kendo\.data\.xml/,
  /kendo\.data\.odata/,
  /kendo\.virtuallist/,
  /kendo\.datepicker/
];

exports.addKendoGlobal = (additionalRules = [], addKendoBaseRules = false) => {
  let rules = additionalRules;
  if (addKendoBaseRules) {
    rules = [...additionalRules, ...kendoBasesRules];
  }
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

exports.addJqueryGlobal = () => {
  return {
    externals: [{ jquery: "jQuery" }]
  };
};

exports.addVueGlobal = () => {
  return {
    externals: [{ vue: "vue" }]
  };
};

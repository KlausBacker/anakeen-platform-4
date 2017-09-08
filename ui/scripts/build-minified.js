//noinspection BadExpressionStatementJS
({
  baseUrl : "../node_modules/kendo-ui-core/js",
  paths :   {
    "kendo-culture-fr" :     "cultures/kendo.culture.fr-FR",
    "kendo-culture-en" :     "cultures/kendo.culture.en",
    "jquery" : "../../jquery"
  },
  map : {
    "kendo-culture-en" : {
      "../kendo.core" : "kendo.core"
    },
    "kendo-culture-fr" : {
      "../kendo.core" : "kendo.core"
    }
  },
  onBuildWrite : function (name, path, contents) {
    contents = contents.replace(/(['"])\.\.\/kendo\.(\w+['"])/g, "$1kendo.$2");
    return contents.replace(/(['"])kendo\.(\w+['"])/g, "$1kendo/kendo.$2");
  },
  wrap: {
    startFile: '../scripts/start.js.frag',
    endFile: '../scripts/end.js.frag'
  },
  generateSourceMaps : true,
  preserveLicenseComments : false,
  optimize : "uglify2",
  name :    "../../../scripts/main",
  out :     "../../Document-uis/src/public/lib/KendoUI/js/kendo-ddui-builded.min.js"
})
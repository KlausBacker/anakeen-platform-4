//noinspection BadExpressionStatementJS
({
  baseUrl : "../js",
  paths :   {
    "kendo-culture-fr" :     "cultures/kendo.culture.fr-FR",
    "kendo-culture-en" :     "cultures/kendo.culture.en",
    "jquery" : "../../jquery/jquery"
  },
  onBuildWrite : function (name, path, contents) {
    contents = contents.replace(/(['"])\.\.\/kendo\.(\w+['"])/g, "$1kendo.$2");
    return contents.replace(/(['"])kendo\.(\w+['"])/g, "$1kendo/kendo.$2");
  },
  wrap: {
    startFile: '../kendo-builder/start.js.frag',
    endFile: '../kendo-builder/end.js.frag'
  },
  generateSourceMaps : true,
  preserveLicenseComments : false,
  optimize : "uglify2",
  name :    "../kendo-builder/main",
  out :     "../js/kendo-ddui-builded.min.js"
})
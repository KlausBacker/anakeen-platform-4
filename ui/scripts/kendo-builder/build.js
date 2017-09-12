//noinspection BadExpressionStatementJS
({
  baseUrl : "../js",
  paths :   {
    "kendo-culture-fr" :     "cultures/kendo.culture.fr-FR",
    "kendo-culture-en" :     "cultures/kendo.culture.en",
    "jquery" : "../../jquery"
  },
  onBuildWrite : function (name, path, contents) {
    contents = contents.replace(/(['"])\.\.\/kendo\.(\w+['"])/g, "$1kendo.$2");
    return contents.replace(/(['"])kendo\.(\w+['"])/g, "$1kendo/js/kendo.$2");
  },
  wrap: {
    startFile: './start.js.frag',
    endFile: './end.js.frag'
  },
  optimize : "none",
  name :    "../kendo-builder/main",
  out :     "../js/kendo-ddui-builded.js"
})
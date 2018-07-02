const { build } = require('./tasks/build');
const { deploy } = require("./tasks/deploy");
const gulp = require('gulp');
const {stub} = require('./tasks/stub');
const {po} = require('./tasks/po');

build({ sourcePath: "/home/charles/git/ank-basic-showcase/ank-basic-showcase/", targetPath: "./" , autoRelease: true});
deploy({ controlUrl: "http://localhost/control/public",  "controlUsername": "admin", "controlPassword":"anakeen", "appPath": "./basic-showcase-1.0.0-11.app"})
stub("/Users/rlutter/Projects/A4/anakeen-cli/POtest", "./");
po("/Users/rlutter/Projects/A4/anakeen-cli/POtest", "./output/po");
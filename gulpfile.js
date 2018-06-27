const { build } = require('./tasks/build');
const { deploy } = require("./tasks/deploy");
const gulp = require('gulp');


build({ sourcePath: "/home/charles/git/ank-basic-showcase/ank-basic-showcase/", targetPath: "./" , autoRelease: true});
deploy({ controlUrl: "http://localhost/control/public",  "controlUsername": "admin", "controlPassword":"anakeen", "appPath": "./basic-showcase-1.0.0-11.app"})

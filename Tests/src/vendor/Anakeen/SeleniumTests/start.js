#!/usr/bin/env node

var Jasmine = require('jasmine');
var reporters = require('jasmine-reporters');
var expectReporter = require("./spec/lib/expectReporter.js");
var jasmine = new Jasmine();

var args = process.argv.slice(2);

var junitReporter = new reporters.JUnitXmlReporter({
    savePath: __dirname
});

var files = ["**/*[sS]pec.js"];



if (args.length > 0) {
    files = args;
}

jasmine.loadConfig({
    "spec_dir" : 'spec',
    "spec_files" : files,
    "stopSpecOnExpectationFailure": false,
    "random": false
});

jasmine.configureDefaultReporter({
    showColor:true
});

jasmine.addReporter(junitReporter);

/**
 * Register the reporter with jasmine
 */
jasmine.addReporter(expectReporter.reporter);
jasmine.execute();
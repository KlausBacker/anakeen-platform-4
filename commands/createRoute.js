const gulp = require("gulp");
const { getModuleInfo } = require("../utils/moduleInfo");
const signale = require("signale");
const fs = require("fs");
const inquirer = require("inquirer");
const { createRoute } = require("../tasks/createRoute");

let moduleData = {};
signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "Create a route";
const builder = {
  namespace: {
    description: "namespace of the route",
    alias: "N",
    type: "string",
    default: () => {
      if (moduleData.moduleInfo) {
        return moduleData.moduleInfo.vendor;
      } else {
        return undefined;
      }
    }
  },
  name: {
    description: "name of the route",
    alias: "n",
    type: "string",
    required: true
  },
  callable: {
    description: "path to php file",
    alias: "c",
    type: "string",
    default: arg => {
      if (moduleData.moduleInfo) {
        return `${moduleData.moduleInfo.vendor}\\${
          moduleData.moduleInfo.name
        }\\Routes\\${arg}`;
      } else {
        return undefined;
      }
    }
  },
  method: {
    description: "method used by the route",
    alias: "m",
    type: "array",
    required: true,
    conflicts: "overrides"
  },
  pattern: {
    description: "pattern which validate the route",
    type: "string",
    alias: "p",
    required: true,
    conflicts: "overrides"
  },
  description: {
    type: "string"
  },
  access: {
    description: "name of the access right",
    type: "string",
    implies: "accessNameSpace"
  },
  accessNameSpace: {
    description: "namespace of the access right",
    type: "string",
    implies: "access"
  },
  sourcePath: {
    description: "path to the module",
    alias: "s",
    default: ".",
    type: "string",
    coerce: arg => {
      if (!fs.statSync(arg).isDirectory()) {
        throw new Error("Unable to find the source directory " + arg);
      }
      return arg;
    }
  },
  middleware: {
    description: "route is a middleware",
    type: "boolean",
    default: false
  },
  overrides: {
    description: "route is route-override",
    type: "boolean",
    default: false
  }
};
exports.builder = builder;

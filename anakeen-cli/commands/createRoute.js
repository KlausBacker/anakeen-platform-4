const gulp = require("gulp");
const signale = require("signale");
const fs = require("fs");
const { createRoute } = require("../tasks/createRoute");

signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "Create a route";
const builder = {
  name: {
    description: "name of the route",
    alias: "n",
    type: "string",
    required: true
  },
  callable: {
    description: "path to php file (example: Anakeen/Module/Routes/Main)",
    alias: "c",
    type: "string",
    required: true
  },
  method: {
    description: "method used by the route",
    alias: "m",
    type: "array"
  },
  pattern: {
    description: "pattern which validate the route",
    type: "string",
    alias: "p"
  },
  description: {
    type: "string"
  },
  access: {
    description: "name of the access right",
    type: "string",
    implies: "accessNameSpace",
    required: true
  },
  accessNameSpace: {
    description: "namespace of the access right",
    type: "string",
    implies: "access",
    required: true
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
  routeConfigPath: {
    description: "path of the file where the route will be added",
    type: "string",
    required: true
  },
  type: {
    description: "type of the route [ routes || middleware || overrides ]",
    type: "string",
    default: "routes",
    required: true,
    choices: ["routes", "middleware", "overrides"]
  },
  priority: {
    description: "priority used by middlewares",
    type: "number",
    default: 1
  }
};
exports.builder = builder;

exports.handler = async argv => {
  try {
    signale.time("createRoute");
    createRoute(argv);
    const task = gulp.task("createRoute");
    task()
      .then(() => {
        signale.timeEnd("createRoute");
        signale.success("createRoute done");
      })
      .catch(e => {
        signale.timeEnd("createRoute");
        signale.error(e);
        process.exit(1);
      });
  } catch (e) {
    signale.timeEnd("createRoute");
    signale.error(e);
    process.exit(1);
  }
};

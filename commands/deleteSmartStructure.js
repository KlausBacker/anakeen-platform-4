const gulp = require("gulp");
const signale = require("signale");
const inquirer = require("inquirer");

const { deleteSmartStructure } = require("../tasks/deleteSmartStructure");

signale.config({
  displayTimestamp: true,
  displayDate: true
});

exports.desc = "Delete a smart structure";
exports.builder = {
  name: {
    description: "name of the smart structure",
    alias: "n",
    type: "string",
    required: true
  },
  username: {
    alias: "u",
    type: "string",
    required: true
  },
  password: {
    alias: "p",
    type: "string",
    required: true
  },
  contextUrl: {
    description: "url of the context",
    alias: "c",
    type: "string",
    required: true
  },
  byPass: {
    description: "bypass confirmation message",
    type: "boolean",
    default: false
  }
};

exports.handler = async function(argv) {
  try {
    if (argv.byPass) {
      deleteSmartStructure(argv);
      const task = gulp.task("deleteSmartStructure");
      return task().then(response => {
        signale.timeEnd("deleteSmartStructure");
        signale.info("\n" + response.messages.messages.join("\n"));
        signale.success("Smart structure has been deleted");
      });
    } else {
      inquirer
        .prompt({
          type: "confirm",
          name: "confirmDelete",
          message: `You are about to delete ${
            argv.name
          } smart structure. Are you sure you want to delete this?`
        })
        .then(confirmation => {
          if (confirmation.confirmDelete && !argv.byPass) {
            if (argv.name) {
              signale.info(`Deleting ${argv.name} smart structure...`);
            }
            deleteSmartStructure(argv);
            const task = gulp.task("deleteSmartStructure");
            return task().then(response => {
              signale.timeEnd("deleteSmartStructure");
              signale.info("\n" + response.messages.messages.join("\n"));
              signale.success("Smart structure has been deleted");
            });
          }
        });
    }
  } catch (e) {
    signale.timeEnd("deleteSmartStructure");
    signale.error(e);
  }
};

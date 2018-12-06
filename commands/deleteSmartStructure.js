const gulp = require("gulp");
const signale = require("signale");

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
  }
};

exports.handler = function(argv) {
  try {
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
  } catch (e) {
    signale.timeEnd("deleteSmartStructure");
    signale.error(e);
  }
};

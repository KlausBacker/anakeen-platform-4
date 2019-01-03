#!/usr/bin/env node

const {produceAndUpload} = require("@anakeen/anakeen-ci");
const {
  getModuleInfo
} = require("@anakeen/anakeen-cli/utils/moduleInfo");

return produceAndUpload({getModuleInfo})
  .then(() => {
    console.log("OK");
  })
  .catch(err => {
    throw new Error(err);
  });
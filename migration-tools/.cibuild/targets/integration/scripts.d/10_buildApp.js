#!/usr/bin/env node

const { produceApp } = require("@anakeen/anakeen-ci");
const { getModuleInfo } = require("@anakeen/anakeen-cli/utils/moduleInfo");

return produceApp({
  apps: [
    {
      app: {
        command: "make",
        args: ["app-autorelease"]
      },
      path: {
        infoXML: "./",
        src: "./src/"
      },
      src: true
    }
  ],
  getModuleInfo
})
  .then(() => {
    console.log("OK");
  })
  .catch(err => {
    console.error(err);
    process.exit(42);
  });

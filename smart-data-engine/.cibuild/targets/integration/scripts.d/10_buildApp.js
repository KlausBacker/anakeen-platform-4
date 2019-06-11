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
      stubs : {
        command: "make",
        args: ["stubs"],
        path: "./stubs/"
      },
      stability: "beta",
      src: true
    },
    {
      app: {
        command: "make",
        args: ["app-test-autorelease"]
      },
      path: {
        infoXML: "./Tests",
        src: "./Tests/src/"
      },
      stability: "beta",
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

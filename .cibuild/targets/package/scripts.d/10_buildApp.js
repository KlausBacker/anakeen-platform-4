#!/usr/bin/env node

const { produceApp } = require("@anakeen/anakeen-ci");
const { getModuleInfo } = require("@anakeen/anakeen-cli/utils/moduleInfo");

const util = require("util");
const writeFile = util.promisify(fs.writeFile);
const readFile = util.promisify(fs.readFile);

return produceApp({
  apps: [
    {
      app: {
        command: "make",
        args: ["app"]
      },
      path: {
        infoXML: "./",
        src: "./src/"
      },
      stubs: {
        command: "make",
        args: ["stubs"],
        path: "./stubs/"
      },
      src: true
    }
  ],
  getModuleInfo
})
  .then(async () => {
    const result = JSON.parse(await readFile(path.join(outputPath, "app.json"), "utf8"));
    await produceApp({
      apps: [
        {
          app: {
            command: "make",
            args: ["app-test"]
          },
          path: {
            infoXML: "./Tests",
            src: "./Tests/src/"
          },
          src: true
        }
      ],
      getModuleInfo
    });
    const resultTest = JSON.parse(await readFile(path.join(outputPath, "app.json"), "utf8"));
    return await writeFile(
      path.join(outputPath, "app.json"),
      JSON.stringify([...result, ...resultTest])
    );

  })
  .then(() => {
    console.log("OK");
  })
  .catch(err => {
    console.error(err);
    process.exit(42);
  });

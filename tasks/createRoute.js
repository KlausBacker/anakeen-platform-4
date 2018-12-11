const gulp = require("gulp");
const xml2js = require("xml2js");
const fs = require("fs");
const { getModuleInfo } = require("../utils/moduleInfo");

const phpPathDirectory = (callable, moduleData) => {
  let path = callable.split("/");
  path.pop();
  return moduleData.buildInfo.buildPath[0] + "/vendor" + path.join("/");
};
const convertPathInPhpNamespace = callable => {
  let path = callable.split("/");
  path.pop();
  return path.join("\\");
};
const middlewareConf = (
  name,
  callable,
  method,
  pattern,
  description,
  access,
  accessNameSpace,
  priority
) => {
  let accesses = "";
  if (access && accessNameSpace) {
    accesses = `<sde:requiredAccess>
                    <sde:access ns="${accessNameSpace}">${access}</sde:access>
                </sde:requiredAccess>`;
  } else {
    accesses = `<sde:requiredAccess/>`;
  }

  return `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
  <sde:config xmlns:sde="https://platform.anakeen.com/4/schemas/sde/1.0">
        <sde:middleware name="${name}">
            <sde:priority>${priority}</sde:priority>
            <sde:callable>${callable}</sde:callable>
            <sde:method>${method}</sde:method>
            <sde:pattern>${pattern}</sde:pattern>
            <sde:description>${description}</sde:description>
            ${accesses}
        </sde:middleware>
  </sde:config>`;
};

const overridesConf = (
  name,
  callable,
  description,
  access,
  accessNameSpace
) => {
  let accesses = "";
  if (access && accessNameSpace) {
    accesses = `<sde:requiredAccess>
                    <sde:access ns="${accessNameSpace}">${access}</sde:access>
                </sde:requiredAccess>`;
  } else {
    accesses = `<sde:requiredAccess/>`;
  }

  return `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
  <sde:config xmlns:sde="https://platform.anakeen.com/4/schemas/sde/1.0">
        <sde:route-override name="${name}">
            <sde:callable>${callable}</sde:callable>
            <sde:description>${description}</sde:description>
            ${accesses}
        </sde:route-override>
  </sde:config>`;
};

const routesConf = (
  name,
  callable,
  method,
  pattern,
  description,
  access,
  accessNameSpace
) => {
  let accesses = "";
  if (access && accessNameSpace) {
    accesses = `<sde:requiredAccess>
                    <sde:access ns="${accessNameSpace}">${access}</sde:access>
                </sde:requiredAccess>`;
  } else {
    accesses = `<sde:requiredAccess/>`;
  }
  return `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
  <sde:config xmlns:sde="https://platform.anakeen.com/4/schemas/sde/1.0">
        <sde:route name="${name}">
            <sde:callable>${callable}</sde:callable>
            <sde:method>${method}</sde:method>
            <sde:pattern>${pattern}</sde:pattern>
            <sde:description>${description}</sde:description>
            ${accesses}
        </sde:route>
  </sde:config>`;
};
const generatePhpFile = ({ name, namespace }) => {
  const ssName = name.charAt(0).toUpperCase() + name.slice(1).toLowerCase();
  return `<?php

namespace ${namespace};

use Anakeen\\SmartElement;
use Anakeen\\SmartElementManager;

class ${ssName}
{
 protected $viewIdentifier;
    protected $docid;
    /** @var SmartElement */
    protected $document;

    /**
     * @param \\Slim\\Http\\request $request
     * @param \\Slim\\Http\\response $response
     * @param callable $next
     * @param array $args
     * @return mixed
     * @throws \\Anakeen\\Core\\DocManager\\Exception
     */
    public function __invoke(\\Slim\\Http\\request $request, \\Slim\\Http\\response $response, callable $next, array $args = [])
    {
        $this->initParameters($args, $request);
        $request = $this->doRequest($request);
        return $next($request, $response);
    }

    /**
     * @param $args
     * @param \\Slim\\Http\\request $request
     * @throws \\Anakeen\\Core\\DocManager\\Exception
     */
    protected function initParameters($args, \\Slim\\Http\\request $request)
    {
      //TO DO
    }

    protected function doRequest(\\Slim\\Http\\request $request)
    {
        // TO DO
        return $request;
    }
}`;
};

exports.createRoute = ({
  namespace,
  name,
  callable,
  method,
  pattern,
  description,
  access,
  priority,
  accessNameSpace,
  routeConfigPath,
  type,
  sourcePath
}) => {
  return gulp.task("createRoute", async () => {
    let moduleData = await getModuleInfo(sourcePath);
    //Get xml content
    const parser = new xml2js.Parser();
    return new Promise((resolve, reject) => {
      if (!fs.existsSync(routeConfigPath)) {
        let myData = null;
        switch (type) {
          case "middleware":
            myData = middlewareConf(
              name,
              callable,
              method,
              pattern,
              description,
              access,
              accessNameSpace,
              priority
            );
            break;
          case "routes":
            myData = routesConf(
              name,
              callable,
              method,
              pattern,
              description,
              access,
              accessNameSpace
            );
            break;
          case "overrides":
            myData = overridesConf(
              name,
              callable,
              description,
              access,
              accessNameSpace
            );
            break;
        }
        fs.writeFile(routeConfigPath, myData, err => {
          if (err) {
            reject(err);
          }
          resolve();
        });
      } else {
        fs.readFile(routeConfigPath, { encoding: "utf8" }, (err, content) => {
          if (err) {
            return reject(err);
          }
          parser.parseString(content, (err, data) => {
            if (err) {
              return reject(err);
            }
            let middlewareTag = null;
            let routesTag = null;
            let overridesTag = null;
            let configTag = data["sde:config"];
            switch (type) {
              case "middleware":
                middlewareTag = configTag["sde:middlewares"];
                if (middlewareTag) {
                  let myRoute = null;
                  middlewareTag.forEach(item => {
                    if (item.$.namespace === namespace) {
                      myRoute = item;
                    }
                  });
                  if (myRoute !== null) {
                    myRoute["sde:middleware"].push({
                      $: { name: name },
                      "sde:priority": priority,
                      "sde:callable": callable,
                      "sde:method": method,
                      "sde:pattern": pattern,
                      "sde:description": description,
                      "sde:requiredAccess": {
                        $: {},
                        "sde:access": {
                          $: { ns: accessNameSpace },
                          _: access
                        }
                      }
                    });
                  } else {
                    configTag["sde:middlewares"].push({
                      $: { namespace: namespace },
                      "sde:middleware": {
                        $: { name: name },
                        "sde:priority": priority,
                        "sde:callable": callable,
                        "sde:method": method,
                        "sde:pattern": pattern,
                        "sde:description": description,
                        "sde:requiredAccess": {
                          $: {},
                          "sde:access": {
                            $: { ns: accessNameSpace },
                            _: access
                          }
                        }
                      }
                    });
                  }
                } else {
                  configTag["sde:middlewares"] = [];
                  configTag["sde:middlewares"].push({
                    $: { namespace: namespace },
                    "sde:middleware": {
                      $: { name: name },
                      "sde:priority": priority,
                      "sde:callable": callable,
                      "sde:method": method,
                      "sde:pattern": pattern,
                      "sde:description": description,
                      "sde:requiredAccess": {
                        $: {},
                        "sde:access": {
                          $: { ns: accessNameSpace },
                          _: access
                        }
                      }
                    }
                  });
                }
                break;
              case "routes":
                routesTag = configTag["sde:routes"];
                if (routesTag) {
                  let myRoute = null;
                  routesTag.forEach(item => {
                    if (item.$.namespace === namespace) {
                      myRoute = item;
                    }
                  });
                  if (myRoute !== null) {
                    myRoute["sde:route"].push({
                      $: { name: name },
                      "sde:callable": callable,
                      "sde:method": method,
                      "sde:pattern": pattern,
                      "sde:description": description,
                      "sde:requiredAccess": {
                        $: {},
                        "sde:access": {
                          $: { ns: accessNameSpace },
                          _: access
                        }
                      }
                    });
                  } else {
                    configTag["sde:routes"].push({
                      $: { namespace: namespace },
                      "sde:route": {
                        $: { name: name },
                        "sde:callable": callable,
                        "sde:method": method,
                        "sde:pattern": pattern,
                        "sde:description": description,
                        "sde:requiredAccess": {
                          $: {},
                          "sde:access": {
                            $: { ns: accessNameSpace },
                            _: access
                          }
                        }
                      }
                    });
                  }
                } else {
                  configTag["sde:routes"] = [];
                  configTag["sde:routes"].push({
                    $: { namespace: namespace },
                    "sde:route": {
                      $: { name: name },
                      "sde:callable": callable,
                      "sde:method": method,
                      "sde:pattern": pattern,
                      "sde:description": description,
                      "sde:requiredAccess": {
                        $: {},
                        "sde:access": {
                          $: { ns: accessNameSpace },
                          _: access
                        }
                      }
                    }
                  });
                }
                break;
              case "overrides":
                overridesTag = configTag["sde:routes"];
                if (overridesTag) {
                  let myRoute = null;
                  overridesTag.forEach(item => {
                    if (item.$.namespace === namespace) {
                      myRoute = item;
                    }
                  });
                  if (myRoute !== null) {
                    myRoute["sde:route-override"].push({
                      $: { name: name },
                      "sde:callable": callable,
                      "sde:description": description,
                      "sde:requiredAccess": {
                        $: {},
                        "sde:access": {
                          $: { ns: accessNameSpace },
                          _: access
                        }
                      }
                    });
                  } else {
                    configTag["sde:routes"].push({
                      $: { namespace: namespace },
                      "sde:route-override": {
                        $: { name: name },
                        "sde:callable": callable,
                        "sde:description": description,
                        "sde:requiredAccess": {
                          $: {},
                          "sde:access": {
                            $: { ns: accessNameSpace },
                            _: access
                          }
                        }
                      }
                    });
                  }
                } else {
                  configTag["sde:routes"] = [];
                  configTag["sde:routes"].push({
                    $: { namespace: namespace },
                    "sde:route-override": {
                      $: { name: name },
                      "sde:callable": callable,
                      "sde:description": description,
                      "sde:requiredAccess": {
                        $: {},
                        "sde:access": {
                          $: { ns: accessNameSpace },
                          _: access
                        }
                      }
                    }
                  });
                }
                break;
            }
            const builder = new xml2js.Builder();
            fs.writeFile(routeConfigPath, builder.buildObject(data), err => {
              if (err) {
                reject(err);
              }
              resolve();
            });
          });
        });
      }
      const phpFileDirectory = phpPathDirectory(callable, moduleData);
      new Promise((resolve, reject) => {
        if (!fs.existsSync(phpFileDirectory)) {
          fs.mkdir(phpFileDirectory, err => {
            if (err) {
              reject(err);
            }
            resolve(phpFileDirectory);
          });
        } else {
          resolve(phpFileDirectory);
        }
      }).then(currentPath => {
        const phpStruct = generatePhpFile({
          name,
          namespace: convertPathInPhpNamespace(callable)
        });
        fs.writeFile(
          moduleData.buildInfo.buildPath[0] + "/vendor" + callable + ".php",
          phpStruct,
          err => {
            if (err) {
              return reject(err);
            }
            resolve(currentPath);
          }
        );
      });
    });
  });
};

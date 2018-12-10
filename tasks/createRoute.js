const gulp = require("gulp");
const { getModuleInfo } = require("../utils/moduleInfo");
const xml2js = require("xml2js");
const fs = require("fs");

const convertPathInPhpPath = callable => {
  let path = callable.split("\\");
  return path.join("/");
};

const convertPathInPhpNamespace = callable => {
  let path = callable.split("\\");
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
  accessNameSpace
) => {
  let methods = "";
  let accesses = "";
  if (Array.isArray(method)) {
    method.forEach(item => {
      methods.concat(`<sde:method>${item}</sde:method>\n`);
    });
  } else {
    methods = `<sde:method>${method}</sde:method>\n`;
  }
  if (access && accessNameSpace) {
    accesses = `<sde:requiredAccess>
                    <sde:access ns="${accessNameSpace}">${access}</sde:access>
                </sde:requiredAccess>`;
  } else {
    accesses = `<sde:requiredAccess/>`;
  }

  return `<sde:middleware name="${name}">
            <sde:priority />
            <sde:callable>${callable}</sde:callable>
            ${methods}
            <sde:pattern>${pattern}</sde:pattern>
            <sde:description>${description}</sde:description>
            ${accesses}
        </sde:middleware>
`;
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

  return ` <sde:route-override name="${name}">
            <sde:callable>${callable}</sde:callable>
            <sde:description>${description}</sde:description>
            ${accesses}
        </sde:route-override>`;
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
  let methods = "";
  let accesses = "";
  if (Array.isArray(method)) {
    method.forEach(item => {
      methods.concat(`<sde:method>${item}</sde:method>\n`);
    });
  } else {
    methods = `<sde:method>${method}</sde:method>\n`;
  }
  if (access && accessNameSpace) {
    accesses = `<sde:requiredAccess>
                    <sde:access ns="${accessNameSpace}">${access}</sde:access>
                </sde:requiredAccess>`;
  } else {
    accesses = `<sde:requiredAccess/>`;
  }

  return `<sde:route name="${name}">
            <sde:callable>${callable}</sde:callable>
            ${methods}
            <sde:pattern>${pattern}</sde:pattern>
            <sde:description>${description}</sde:description>
            ${accesses}
        </sde:route>`;
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
  accessNameSpace,
  sourcePath,
  routeConfigPath,
  type
}) => {
  return gulp.task("createRoute", async () => {
    const moduleInfo = await getModuleInfo(sourcePath);
    //Get xml content
    const parser = new xml2js.Parser();
    return new Promise((resolve, reject) => {
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
          switch (type) {
            case "middleware":
              middlewareTag = data.module["middlewares"];
              if (middlewareTag["namespace"] === namespace) {
                middlewareTag[0] = middlewareConf(
                  name,
                  callable,
                  method,
                  pattern,
                  description,
                  access,
                  accessNameSpace
                );
              }
              break;
            case "routes":
              routesTag = data.module["routes"];
              if (routesTag["namespace"] === namespace) {
                routesTag[0] = routesConf(
                  name,
                  callable,
                  method,
                  method,
                  pattern,
                  description,
                  access,
                  accessNameSpace
                );
              }
              break;
            case "overrides":
              overridesTag = data.module["routes"];
              if (overridesTag["namespace"] === namespace) {
                overridesTag[0] = overridesConf(
                  name,
                  callable,
                  description,
                  access,
                  accessNameSpace
                );
              }
              break;
          }
          const builder = new xml2js.Builder();
          fs.writeFile(routeConfigPath, builder.buildObject(data), err => {
            if (err) {
              reject(err);
            }
            resolve();
          }).then(() => {
            const phpFileDirectory = convertPathInPhpNamespace(callable);
            let directoryPromise = new Promise((resolve, reject) => {
              fs.mkdir(phpFileDirectory, err => {
                if (err) {
                  reject(err);
                }
                resolve(phpFileDirectory);
              });
            });
            return directoryPromise.then(currentPath => {
              return new Promise((resolve, reject) => {
                //Build the xml
                const builder = new xml2js.Builder();
                const phpStruct = builder.buildObject(
                  generatePhpFile({
                    name,
                    namespace: convertPathInPhpNamespace(callable)
                  })
                );
                fs.writeFile(
                  convertPathInPhpPath(callable) + ".php",
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
        });
      });
    });
  });
};

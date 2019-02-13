const console = require("console");
const path = require("path");
const util = require("util");
const fs = require("fs");

const GenericError = require(path.resolve(__dirname, "GenericError.js"));
const { RepoXML } = require(path.resolve(__dirname, "RepoXML.js"));
const { RepoLockXML } = require(path.resolve(__dirname, "RepoLockXML.js"));
const { AppRegistryBucket } = require(path.resolve(
  __dirname,
  "AppRegistryBucket.js"
));
const { HTTPAgent } = require(path.resolve(__dirname, "HTTPAgent.js"));
const { AppModuleFile } = require(path.resolve(__dirname, "AppModuleFile.js"));
const XMLLoader = require(path.resolve(__dirname, "XMLLoader.js"));

const fs_stat = util.promisify(fs.stat);
const fs_mkdir = util.promisify(fs.mkdir);
const fs_readdir = util.promisify(fs.readdir);

class ComposeError extends GenericError {}

class Compose {
  /**
   * @param {string} localRepo
   * @param [string} localSrc
   * @returns {{compose: {$: {xmlns: string}, registries: {}, config: {localSrc: {path: *}, localRepo: {path: *}}, dependencies: {}}}}
   */
  static repoXMLTemplate({ localRepo, localSrc }) {
    return {
      compose: {
        $: {
          xmlns: "https://platform.anakeen.com/4/schemas/compose/1.0"
        },
        config: {
          localRepo: {
            $: {
              path: localRepo
            }
          },
          localSrc: {
            $: {
              path: localSrc
            }
          }
        },
        registries: {},
        dependencies: {}
      }
    };
  }

  /**
   * Check if a file (or dir) exists
   * @param {string} filename
   * @returns {boolean}
   */
  static async fileExists(filename) {
    try {
      await fs_stat(filename);
    } catch (e) {
      return false;
    }
    return true;
  }

  /**
   * @param {string} localRepo
   * @param {string} localSrc
   * @returns {Promise<void>}
   */
  static async init({ localRepo, localSrc }) {
    let stats;

    if (await Compose.fileExists("repo.xml")) {
      throw new Error(`File 'repo.xml' already exists`);
    }

    try {
      stats = await fs_stat(localRepo);
    } catch (e) {
      try {
        await fs_mkdir(localRepo, { recursive: true });
        stats = await fs_stat(localRepo);
      } catch (e) {
        throw new Error(
          `Could not create localRepo directory '${localRepo}': ${e}`
        );
      }
    }
    if (!stats.isDirectory()) {
      throw new Error(`localRepo '${localRepo}' is not a directory`);
    }

    try {
      stats = await fs_stat(localSrc);
    } catch (e) {
      try {
        await fs_mkdir(localSrc, { recursive: true });
        stats = await fs_stat(localSrc);
      } catch (e) {
        throw new Error(
          `Could not create localRepo directory '${localSrc}': ${e}`
        );
      }
    }
    if (!stats.isDirectory()) {
      throw new Error(`localRepo '${localSrc}' is not a directory`);
    }

    const repoXML = new RepoXML("repo.xml");
    repoXML.setData(Compose.repoXMLTemplate({ localRepo, localSrc }));
    await repoXML.save();
  }

  /**
   * @param {string} name Registry's unique name/identifier
   * @param {string} url Registry's bse URL (e.g. 'http://localhost:8080')
   * @param {string} bucket Registry's bucket name
   * @param {string} authUser Registry's authentication username
   * @param {string} authPassword Registry's authentication password
   * @returns {Promise<{name: *, authUser: *, url: *, authPassword: *}>}
   */
  static async addAppRegistry({ name, url, bucket, authUser, authPassword }) {
    const repoXML = new RepoXML("repo.xml");
    await repoXML.load();
    await repoXML.addAppRegistry({ name, url, bucket, authUser, authPassword });
    await repoXML.save();
  }

  /**
   * @param {string} moduleName Module's name
   * @param {string} moduleVersion Module's semver version
   * @param {string} registryName Registry's unique name/identifier from which the module is to be downloaded.
   */
  static async addModule({
    name: moduleName,
    version: moduleVersion,
    registry: registryName
  }) {
    const repoXML = new RepoXML("repo.xml");
    await repoXML.load();
    const localRepo = await repoXML.getConfigLocalRepo();
    const localSrc = await repoXML.getConfigLocalSrc();

    const registry = repoXML.getRegistryByName(registryName);
    if (!registry) {
      throw new ComposeError(
        `Registry with name '${registryName}' not found in 'repo.xml'`
      );
    }

    const appRegistryBucket = new AppRegistryBucket(registry);
    const ping = await appRegistryBucket.ping();
    if (!ping) {
      throw new ComposeError(
        `Registry '${registryName}' does not seems to be valid`
      );
    }
    const moduleList = await appRegistryBucket.getModuleList(
      moduleName,
      moduleVersion
    );
    if (moduleList.length <= 0) {
      throw new ComposeError(
        `No module '${moduleName}' found satisfying version '${moduleVersion}' on registry '${registryName}'`
      );
    }
    const module = moduleList.slice(0, 1)[0];
    const moduleInfo = await appRegistryBucket.getModuleVersionInfo(
      module.name,
      module.version
    );

    console.dir(moduleInfo);

    const httpAgent = new HTTPAgent({ debug: true });
    const resources = {
      app: undefined,
      src: undefined
    };
    if (moduleInfo.hasOwnProperty("app")) {
      const pathname = await httpAgent.downloadFileTo(
        module.url + "/app",
        [localRepo, moduleInfo.app].join("/")
      );
      resources.app = {
        name: moduleInfo.app,
        pathname: pathname
      };
    }
    if (moduleInfo.hasOwnProperty("src")) {
      const pathname = await httpAgent.downloadFileTo(
        module.url + "/app",
        [localSrc, moduleInfo.src].join("/")
      );
      resources.src = {
        name: moduleInfo.src,
        pathname: pathname
      };
    }

    console.dir(resources);

    const appList = await Compose.genRepoContentXML(localRepo);

    throw new Error(`DEBUG`);

    await repoXML.addModule({
      name: moduleName,
      version: moduleVersion,
      registry: registryName
    });

    const repoLockXML = new RepoLockXML("repo.lock.xml");
    await repoLockXML.load();
    repoLockXML.addModule({
      name: moduleName,
      version: module.version,
      registry: registryName
    });

    await repoLockXML.addOrUpdateModule(module);

    await repoXML.save();
  }

  static async genRepoContentXML(repoDir) {
    const xmlLoader = new XMLLoader();
    let fileList = await fs_readdir(repoDir);
    console.dir(fileList);
    fileList = fileList.filter(filename => {
      return filename.match(/\.app$/);
    });
    for (let i = 0; i < fileList.length; i++) {
      const file = fileList[i];
      const moduleFile = new AppModuleFile([repoDir, file].join("/"));
      const xml = await moduleFile.getInfoXMLText();
      await xmlLoader.loadFromString(xml);
      const moduleNode = xmlLoader.data.module;
      console.dir(moduleNode);
    }
  }
}

module.exports = { Compose, ComposeError };

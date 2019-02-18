const console = require("console");
const path = require("path");
const util = require("util");
const fs = require("fs");

const GenericError = require(path.resolve(__dirname, "GenericError.js"));
const { RepoXML } = require(path.resolve(__dirname, "RepoXML.js"));
const { RepoLockXML } = require(path.resolve(__dirname, "RepoLockXML.js"));
const { AppRegistry } = require(path.resolve(__dirname, "AppRegistry.js"));
const { HTTPAgent } = require(path.resolve(__dirname, "HTTPAgent.js"));
const { RepoContentXML } = require(path.resolve(
  __dirname,
  "RepoContentXML.js"
));
const SHA256Digest = require(path.resolve(__dirname, "SHA256Digest"));

const fs_stat = util.promisify(fs.stat);
const fs_mkdir = util.promisify(fs.mkdir);
const fs_readdir = util.promisify(fs.readdir);

class ComposeError extends GenericError {}

class Compose {
  constructor(options = {}) {
    if (typeof options !== "object") {
      options = {};
    }
    this.$ = {
      debug: options.hasOwnProperty("debug") && options.debug === true
    };
  }

  debug(msg, options = {}) {
    if (this.$.debug === true) {
      if (typeof msg === "object") {
        console.dir(msg, options);
      } else {
        console.log(msg);
      }
    }
  }

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
  async init({ localRepo, localSrc }) {
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
   * @param {string} url Registry's URL (e.g. 'http://localhost:8080/my/bucket')
   * @param {string} authUser Registry's authentication username
   * @param {string} authPassword Registry's authentication password
   * @returns {Promise<{name: *, authUser: *, url: *, authPassword: *}>}
   */
  async addAppRegistry({ name, url, authUser, authPassword }) {
    const repoXML = new RepoXML("repo.xml");
    await repoXML.load();
    await repoXML.addAppRegistry({ name, url, authUser, authPassword });
    await repoXML.save();
  }

  /**
   * @param {string} moduleName Module's name
   * @param {string} moduleVersion Module's semver version
   * @param {string} registryName Registry's unique name/identifier from which the module is to be downloaded.
   */
  async addModule({
    name: moduleName,
    version: moduleVersion,
    registry: registryName
  }) {
    const repoXML = new RepoXML("repo.xml");
    await repoXML.load();
    const localRepo = await repoXML.getConfigLocalRepo();
    const localSrc = await repoXML.getConfigLocalSrc();

    const repoLockXML = new RepoLockXML("repo.lock.xml");
    await repoLockXML.load();

    const registry = repoXML.getRegistryByName(registryName);
    if (!registry) {
      throw new ComposeError(
        `Registry with name '${registryName}' not found in 'repo.xml'`
      );
    }

    await repoXML.addModule({
      name: moduleName,
      version: moduleVersion,
      registry: registryName
    });

    const appRegistry = new AppRegistry(registry);
    const ping = await appRegistry.ping();
    if (!ping) {
      throw new ComposeError(
        `Registry '${registryName}' does not seems to be valid`
      );
    }
    const moduleList = await appRegistry.getModuleList(
      moduleName,
      moduleVersion
    );
    if (moduleList.length <= 0) {
      throw new ComposeError(
        `No module '${moduleName}' found satisfying version '${moduleVersion}' on registry '${registryName}'`
      );
    }
    const module = moduleList.slice(0, 1)[0];
    const moduleInfo = await appRegistry.getModuleVersionInfo(
      module.name,
      module.version
    );
    this.debug({ moduleInfo });

    const httpAgent = new HTTPAgent({ debug: this.$.debug });
    const resources = {
      app: undefined,
      src: undefined
    };
    if (moduleInfo.hasOwnProperty("app")) {
      const srcUrl = [module.url, "app", moduleInfo.app].join("/");
      const tmpFile = await httpAgent.downloadFileTo(
        srcUrl,
        [localRepo, moduleInfo.app].join("/")
      );
      resources.app = {
        name: moduleInfo.app,
        src: srcUrl,
        sha256: await SHA256Digest.file(tmpFile),
        tmpFile: tmpFile
      };
    }
    if (moduleInfo.hasOwnProperty("src")) {
      const srcUrl = [module.url, "src", moduleInfo.src].join("/");
      const tmpFile = await httpAgent.downloadFileTo(
        srcUrl,
        [localSrc, moduleInfo.src].join("/")
      );
      resources.src = {
        name: moduleInfo.src,
        src: srcUrl,
        sha256: await SHA256Digest.file(tmpFile),
        tmpFile: tmpFile
      };
    }

    this.debug({ resources: resources }, { depth: 20 });

    repoLockXML.addModule({
      name: module.name,
      version: module.version,
      src: resources.app.src,
      sha256: resources.app.sha256
    });

    const appList = await this.genRepoContentXML(localRepo);

    this.debug({ appList: appList }, { depth: 20 });

    this.debug({ repoXML: repoXML.data }, { depth: 20 });
    this.debug({ repoLockXML: repoLockXML.data }, { depth: 20 });

    await repoXML.save();
    await repoLockXML.save();
  }

  async genRepoContentXML(repoDir) {
    const repoContentXML = new RepoContentXML(
      [repoDir, "content.xml"].join("/")
    );
    repoContentXML.reset();

    let moduleFileList = await fs_readdir(repoDir);
    moduleFileList = moduleFileList.filter(filename => {
      return filename.match(/\.app$/);
    });
    for (let i = 0; i < moduleFileList.length; i++) {
      const moduleFile = moduleFileList[i];
      await repoContentXML.addModuleFile([repoDir, moduleFile].join("/"));
    }

    await repoContentXML.save();

    return moduleFileList;
  }
}

module.exports = { Compose, ComposeError };

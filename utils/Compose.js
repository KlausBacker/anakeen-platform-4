const console = require("console");
const path = require("path");
const util = require("util");
const fs = require("fs");
const semver = require("semver");
const signale = require("signale");

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
const { ComposeCtx } = require(path.resolve(__dirname, "ComposeCtx"));

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

    const repoLockXML = new RepoLockXML("repo.lock.xml");
    await repoLockXML.load();

    await repoXML.addModule({
      name: moduleName,
      version: moduleVersion,
      registry: registryName
    });

    const composeCtx = new ComposeCtx(repoXML, repoLockXML);

    await this._ctx_installModule({
      ctx: composeCtx,
      name: moduleName,
      version: moduleVersion,
      registry: registryName
    });

    await repoXML.save();
    await repoLockXML.save();
  }

  /**
   * Install a module that is present in the 'repo.xml'
   *
   * @param {ComposeCtx} composeCtx
   * @param {string} moduleName
   * @param {string} moduleVersion
   * @param {string} registryName
   * @returns {Promise<void>}
   * @private
   */
  async _ctx_installModule({
    ctx: composeCtx,
    name: moduleName,
    version: moduleVersion,
    registry: registryName
  }) {
    const localRepo = await composeCtx.repoXML.getConfigLocalRepo();
    const localSrc = await composeCtx.repoXML.getConfigLocalSrc();

    const registry = composeCtx.repoXML.getRegistryByName(registryName);
    if (!registry) {
      throw new ComposeError(
        `Registry with name '${registryName}' not found in 'repo.xml'`
      );
    }

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
      const pathname = [localRepo, moduleInfo.app].join("/");
      signale.note(`Downloading '${srcUrl}' to '${pathname}'...`);
      const tmpFile = await httpAgent.downloadFileTo(
        srcUrl,
        pathname
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
      const pathname = [localSrc, moduleInfo.src].join("/");
      signale.note(`Downloading '${srcUrl}' to '${pathname}'...`);
      const tmpFile = await httpAgent.downloadFileTo(
        srcUrl,
        pathname
      );
      resources.src = {
        name: moduleInfo.src,
        src: srcUrl,
        sha256: await SHA256Digest.file(tmpFile),
        tmpFile: tmpFile
      };
    }

    this.debug({ resources: resources }, { depth: 20 });

    composeCtx.repoLockXML.addModule({
      name: module.name,
      version: module.version,
      src: resources.app.src,
      sha256: resources.app.sha256
    });

    signale.note(`Generating 'content.xml' in '${localRepo}'...`);
    const appList = await this.genRepoContentXML(localRepo);

    this.debug({ appList: appList }, { depth: 20 });

    this.debug({ repoXML: composeCtx.repoXML.data }, { depth: 20 });
    this.debug({ repoLockXML: composeCtx.repoLockXML.data }, { depth: 20 });
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

  async install() {
    const repoXML = new RepoXML("repo.xml");
    const repoLockXML = new RepoLockXML("repo.lock.xml");

    await repoXML.load();
    await repoLockXML.load();

    const moduleLockList = repoLockXML.getModuleList();
    const moduleList = repoXML.getModuleList();

    const triage = Compose.triageList(moduleList, moduleLockList);
    this.debug({ triage: triage }, { depth: 20 });

    const composeCtx = new ComposeCtx(repoXML, repoLockXML);

    /*
     * Process new modules (not yet locked)
     */
    let count = triage.notLockedList.length;
    if (count > 0) {
      signale.note(`Found ${count} new module(s) to install`);
      for (let i = 0; i < triage.notLockedList.length; i++) {
        const module = triage.notLockedList[i];
        await this._ctx_installModule({
          ctx: composeCtx,
          name: module.$.name,
          version: module.$.version,
          registry: module.$.registry
        });
      }
    }

    /*
     * Process existing modules (already locked)
     */
    count = triage.lockedList.length;
    if (count > 0) {
      signale.note(`Found ${count} locked module(s) to install`);
      for (let i = 0; i < triage.lockedList.length; i++) {
        const bimod = triage.lockedList[i];
        // semver.satisfies(semver.coerce(elmt.version), filterVersion)
        if (
          semver.satisfies(
            semver.coerce(bimod.locked.$.version),
            bimod.required.$.version
          )
        ) {
          await this._ctx_installModuleFromLock({
            ctx: composeCtx,
            lockedModule: bimod.locked
          });
        } else {
          await this._ctx_installModule({
            ctx: composeCtx,
            name: bimod.required.$.name,
            version: bimod.required.$.version,
            registry: bimod.$.required.registry
          });
        }
      }
    }

    /*
     * Process orphans
     */
    count = triage.orphanLockedList.length;
    if (count > 0) {
      signale.note(`Found ${count} orphaned locked module(s)`);
      for (let i = 0; i < triage.orphanLockedList.length; i++) {
        const module = triage.orphanLockedList[i];
        composeCtx.repoLockXML.deleteModuleByName(module.$.name);
      }
    }

    this.debug({ repoLockXML }, { depth: 20 });
  }

  async _ctx_installModuleFromLock({ ctx: composeCtx, lockedModule }) {
    const localRepo = composeCtx.repoXML.getConfigLocalRepo();
    const localSrc = composeCtx.repoXML.getConfigLocalSrc();

    const src = lockedModule.$.src;
    const sha256 = lockedModule.$.sha256;
    const basename = path.basename(new URL(src).pathname);
    const pathname = [localRepo, basename].join("/");

    this.debug({ src, sha256, basename, pathname });

    let localIsOutdated = true;
    if (await Compose.fileExists(pathname)) {
      const localSha256 = await SHA256Digest.file(pathname);
      localIsOutdated = localSha256 !== sha256;
    }

    if (localIsOutdated) {
      signale.note(
        `Updating outdated module '${pathname}' from lock src '${src}'`
      );
      const httpAgent = new HTTPAgent({ debug: this.$.debug });
      await httpAgent.downloadFileTo(src, pathname);
      signale.note(`Done.`);
    } else {
      signale.note(`Local module '${pathname}' is up-to-date`);
    }

    /* Fetch src */
    const moduleFromRepo = composeCtx.repoXML.getModuleByName(
      lockedModule.$.name
    );
    this.debug({ moduleFromRepo }, { depth: 20 });
    if (typeof moduleFromRepo !== "object") {
      throw new ComposeError(
        `Could not get module '${lockedModule.$.name}' from 'repo.xml'`
      );
    }

    const registryName = moduleFromRepo.registry;
    const registry = composeCtx.repoXML.getRegistryByName(registryName);
    if (!registry) {
      throw new ComposeError(
        `Registry with name '${registryName}' not found in 'repo.xml'`
      );
    }

    const appRegistry = new AppRegistry(registry);
    const moduleInfo = await appRegistry.getModuleVersionInfo(
      lockedModule.$.name,
      lockedModule.$.version
    );
    this.debug({ moduleInfo });

    if (moduleInfo.hasOwnProperty("src")) {
      const httpAgent = new HTTPAgent({ debug: this.$.debug });
      const pathname = [localSrc, moduleInfo.src].join("/");
      const src = [
        appRegistry.getURL(),
        encodeURI(lockedModule.$.name),
        encodeURI(lockedModule.$.version),
        "src"
      ].join("/");
      signale.note(`Downloading '${src}' to '${pathname}'...`);
      await httpAgent.downloadFileTo(src, pathname);
    }
  }

  /**
   * @param {Array} moduleList
   * @param {Array} moduleLockList
   * @returns {{orphanLockedList: Array, notLockedList: Array, lockedList: Array}}
   */
  static triageList(moduleList, moduleLockList) {
    const notLockedList = [];
    const lockedList = [];
    const orphanLockedList = [];

    for (let i = 0; i < moduleList.length; i++) {
      const module = moduleList[i];
      const lockedModule = Compose.isModuleInList(
        module.$.name,
        moduleLockList
      );
      if (lockedModule) {
        lockedList.push({
          required: module,
          locked: lockedModule
        });
      } else {
        notLockedList.push(module);
      }
    }
    for (let i = 0; i < moduleLockList.length; i++) {
      const module = moduleLockList[i];
      const lockedModule = Compose.isModuleInList(module.$.name, moduleList);
      if (!lockedModule) {
        orphanLockedList.push(module);
      }
    }
    return { notLockedList, lockedList, orphanLockedList };
  }

  static isModuleInList(moduleName, moduleList) {
    for (let i = 0; i < moduleList.length; i++) {
      if (moduleList[i].$.name === moduleName) {
        return moduleList[i];
      }
    }
    return undefined;
  }
}

module.exports = { Compose, ComposeError };

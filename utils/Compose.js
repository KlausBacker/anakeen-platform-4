const console = require("console");
const path = require("path");
const util = require("util");
const fs = require("fs");
const semver = require("semver");
const signale = require("signale");
const tar = require("tar");
const rimraf = require("rimraf");

const GenericError = require(path.resolve(__dirname, "GenericError.js"));
const { RepoXML } = require(path.resolve(__dirname, "RepoXML.js"));
const { RepoLockXML } = require(path.resolve(__dirname, "RepoLockXML.js"));
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
      debug: options.hasOwnProperty("debug") && options.debug === true,
      frozenLockfile:
        options.hasOwnProperty("frozenLockfile") &&
        options["frozenLockfile"] === true,
      latest: options.hasOwnProperty("latest") && options.latest === true
    };
  }

  /**
   * Load 'repo.xml' and 'repo.lock.xml' files
   * @returns {Promise<void>}
   */
  async loadContext() {
    if (typeof this.repoXML !== "undefined") {
      throw new ComposeError(`repoXML already loaded!`);
    }
    this.repoXML = new RepoXML("repo.xml");
    await this.repoXML.load();

    if (typeof this.repoLockXML !== "undefined") {
      throw new ComposeError(`repoLockXML already loaded!`);
    }
    this.repoLockXML = new RepoLockXML("repo.lock.xml");
    await this.repoLockXML.load();
  }

  /**
   * Commit changes back to 'repo.xml' and 'repo.lock.xml' files
   * @returns {Promise<void>}
   */
  async commitContext() {
    await this.repoXML.save();
    await this.repoLockXML.save();
  }

  debug(msg, options = {}) {
    if (this.$.debug) {
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
   * @param {string} pathname
   * @returns {Promise<*>}
   */
  static async rm_Rf(pathname) {
    return new Promise((resolve, reject) => {
      rimraf(pathname, { glob: false }, err => {
        if (err) {
          reject(err);
        } else {
          resolve(pathname);
        }
      });
    });
  }

  /**
   * Check if a file (or dir) exists
   * @param {string} filename
   * @returns {boolean|{fs.Stats}}
   */
  static async fileExists(filename) {
    try {
      return await fs_stat(filename);
    } catch (e) {
      return false;
    }
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
    await this.loadContext();
    await this.repoXML.addAppRegistry({ name, url, authUser, authPassword });
    await this.commitContext();
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
    await this.loadContext();

    await this.repoXML.addModule({
      name: moduleName,
      version: moduleVersion,
      registry: registryName
    });

    await this._installSemverModule({
      name: moduleName,
      version: moduleVersion,
      registry: registryName
    });

    await this.commitContext();
  }

  /**
   * Install a module that is present in the 'repo.xml'
   *
   * @param {string} moduleName Module's name
   * @param {string} moduleVersion SemVer version
   * @param {string} registryName Registry's name
   * @returns {Promise<void>}
   * @private
   */
  async _installSemverModule({
    name: moduleName,
    version: moduleVersion,
    registry: registryName
  }) {
    const lockExists = Compose.fileExists("repo.lock.xml");
    if (lockExists === true && this.$.frozenLockfile) {
      throw new ComposeError(
        `Cannot install module '${moduleName}' while using '--frozen-lock' option`
      );
    }

    const appRegistry = this.repoXML.getRegistryByName(registryName);
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

    await this._installAndLockModuleVersion({
      name: module.name,
      version: module.version,
      registry: registryName
    });
  }

  async _installAndLockModuleVersion({
    name: moduleName,
    version: moduleVersion,
    registry: registryName
  }) {
    const localRepo = await this.repoXML.getConfigLocalRepo();
    const localSrc = await this.repoXML.getConfigLocalSrc();

    const appRegistry = this.repoXML.getRegistryByName(registryName);

    /* Remove previous module's resources */
    const lockedModule = this.repoLockXML.getModuleByName(moduleName);
    if (lockedModule) {
      if (lockedModule.$.version === moduleVersion) {
        signale.note(
          `Module '${moduleName}' with version '${moduleVersion}' is up-to-date`
        );
        return;
      } else {
        await this.deleteModuleResources(lockedModule);
      }
    }

    const moduleInfo = await appRegistry.getModuleVersionInfo(
      moduleName,
      moduleVersion
    );
    this.debug({ moduleInfo });

    const resources = {
      app: undefined,
      src: undefined
    };
    for (let type of ["app", "src"]) {
      if (!moduleInfo.hasOwnProperty(type)) {
        continue;
      }
      const url = [
        appRegistry.getModuleVersionURL(moduleName, moduleVersion),
        type,
        moduleInfo[type]
      ].join("/");
      let pathname;
      if (type === "app") {
        pathname = [localRepo, moduleInfo[type]].join("/");
      } else if (type === "src") {
        pathname = [localSrc, moduleInfo[type]].join("/");
      } else {
        throw new ComposeError(`Unrecognized resource type '${type}'`);
      }
      await this._updateLocalResource({
        type,
        src: url,
        pathname,
        moduleName
      });
      resources[type] = {
        name: moduleInfo[type],
        src: url,
        sha256: await SHA256Digest.file(pathname),
        pathname: pathname
      };
    }

    this.debug({ resources: resources }, { depth: 20 });

    const newModule = {
      name: moduleName,
      version: moduleVersion,
      resources: []
    };
    if (typeof resources.app !== "undefined") {
      newModule.resources.push({
        type: "app",
        src: resources.app.src,
        sha256: resources.app.sha256
      });
    }
    if (typeof resources.src !== "undefined") {
      newModule.resources.push({
        type: "src",
        src: resources.src.src,
        sha256: resources.src.sha256
      });
    }

    this.repoLockXML.addOrUpdateModule(newModule);

    signale.note(`Generating 'content.xml' in '${localRepo}'...`);
    const appList = await this.genRepoContentXML(localRepo);

    this.debug({ appList: appList }, { depth: 20 });

    this.debug({ repoXML: this.repoXML.data }, { depth: 20 });
    this.debug({ repoLockXML: this.repoLockXML.data }, { depth: 20 });
  }

  async deleteModuleResources(lockedModule) {
    if (!lockedModule.hasOwnProperty("resources")) {
      return;
    }
    const resources = lockedModule["resources"][0];
    const rmList = [];
    for (let type of ["app", "src"]) {
      if (!resources.hasOwnProperty(type)) {
        continue;
      }
      const resource = resources[type][0];
      const url = resource.$.src;

      let basename;
      let dirname;
      switch (type) {
        case "app":
          basename = path.basename(url);
          dirname = this.repoXML.getConfigLocalRepo();
          rmList.push({ type: "file", path: path.join(dirname, basename) });
          break;
        case "src":
          basename = path.basename(url, ".src");
          dirname = this.repoXML.getConfigLocalSrc();
          rmList.push({
            type: "file",
            path: path.join(dirname, basename + ".src")
          });
          rmList.push({ type: "dir", path: path.join(dirname, basename) });
          break;
      }
    }

    for (let elmt of rmList) {
      if (Compose.fileExists(elmt.path)) {
        signale.note(`Removing ${elmt.type} '${elmt.path}'...`);
        await Compose.rm_Rf(elmt.path);
      }
    }
  }

  /**
   * Unpack archive into a directory created from
   * the archive's basename: `/path/to/archive.src` will be unpacked in
   * `/path/to/archive` (without the `.src` suffix)
   * @param {string} archive Path to archive
   * @param {string} moduleName name of the module
   * @returns {Promise<*>}
   */
  async unpackSrc(archive, moduleName) {
    if (!archive.match(/\.src$/)) {
      throw new ComposeError(`Archive '${archive}' has no '.src' suffix`);
    }
    const basename = moduleName || path.basename(archive, ".src");
    const dirname = path.dirname(archive);
    const pathname = path.normalize([dirname, basename].join("/"));
    if (await Compose.fileExists(pathname)) {
      await Compose.rm_Rf(pathname);
    }
    await fs_mkdir(pathname, { recursive: true });
    return tar.x({
      C: pathname,
      file: archive
    });
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

  /**
   * @returns {Promise<void>}
   */
  async install() {
    await this.loadContext();

    const moduleLockList = this.repoLockXML.getModuleList();
    const moduleList = this.repoXML.getModuleList();

    const triage = Compose.triageList(moduleList, moduleLockList);
    this.debug({ triage: triage }, { depth: 20 });

    /*
     * (1) Process new modules (not yet locked)
     */
    let count = triage.notLockedList.length;
    if (count > 0) {
      signale.note(`Found ${count} new module(s) to install`);
      for (let i = 0; i < triage.notLockedList.length; i++) {
        const module = triage.notLockedList[i];
        await this._installSemverModule({
          name: module.$.name,
          version: module.$.version,
          registry: module.$.registry
        });
      }
    }

    /*
     * (2) Process existing modules (already locked)
     */
    count = triage.lockedList.length;
    if (count > 0) {
      signale.note(`Found ${count} locked module(s) to install`);
      for (let i = 0; i < triage.lockedList.length; i++) {
        const bimod = triage.lockedList[i];
        if (
          semver.satisfies(
            semver.coerce(bimod.locked.$.version),
            bimod.required.$.version
          )
        ) {
          signale.note(
            `Installing module '${bimod.locked.$.name}' with version '${
              bimod.locked.$.version
            }' from lock file`
          );
          await this._installModuleFromLock({
            lockedModule: bimod.locked
          });
        } else {
          if (this.$.frozenLockfile) {
            throw new ComposeError(
              `Locked version '${
                bimod.locked.$.version
              }' does not satisfies requested semver version '${
                bimod.required.$.version
              }'`
            );
          }
          signale.note(
            `Installing module '${bimod.required.$.name}' with version '${
              bimod.required.$.version
            }'`
          );
          await this._installSemverModule({
            name: bimod.required.$.name,
            version: bimod.required.$.version,
            registry: bimod.required.$.registry
          });
        }
      }
    }

    /*
     * (3) Process orphans
     */
    count = triage.orphanLockedList.length;
    if (count > 0) {
      signale.note(`Found ${count} orphan locked module(s)`);
      for (let i = 0; i < triage.orphanLockedList.length; i++) {
        const module = triage.orphanLockedList[i];
        if (this.$.frozenLockfile) {
          throw new ComposeError(
            `Cannot remove orphan locked module '${
              module.$.name
            }' while using '--frozen-lockfile' option`
          );
        }
        this.repoLockXML.deleteModuleByName(module.$.name);
      }
    }

    this.debug({ repoLockXML: this.repoLockXML }, { depth: 20 });

    await this.commitContext();
  }

  async _installModuleFromLock({ lockedModule }) {
    const localRepo = this.repoXML.getConfigLocalRepo();
    const localSrc = this.repoXML.getConfigLocalSrc();

    const resourceDir = {
      app: localRepo,
      src: localSrc
    };

    for (let type of ["app", "src"]) {
      if (
        !lockedModule.hasOwnProperty("resources") ||
        !Array.isArray(lockedModule.resources) ||
        lockedModule.resources.length <= 0 ||
        !lockedModule.resources[0].hasOwnProperty(type) ||
        !Array.isArray(lockedModule.resources[0][type]) ||
        lockedModule.resources[0][type].length <= 0
      ) {
        continue;
      }
      const resource = lockedModule.resources[0][type][0];
      const src = resource.$.src;
      const sha256 = resource.$.sha256;
      const basename = path.basename(new URL(src).pathname);
      const pathname = [resourceDir[type], basename].join("/");

      let localIsOutdated = true;
      if (await Compose.fileExists(pathname)) {
        const localSha256 = await SHA256Digest.file(pathname);
        localIsOutdated = localSha256 !== sha256;
      }

      if (
        !localIsOutdated &&
        type === "src" &&
        !Compose._srcUnpackDirExists(pathname)
      ) {
        localIsOutdated = true;
      }

      if (localIsOutdated) {
        signale.note(
          `Updating outdated resource '${pathname}' from lock src '${src}'`
        );
        await this._updateLocalResource({
          type,
          src,
          pathname,
          moduleName: lockedModule.$.name
        });
      } else {
        signale.note(`Local resource '${pathname}' is up-to-date`);
      }
    }

    signale.note(`Generating 'content.xml' in '${localRepo}'...`);
    await this.genRepoContentXML(localRepo);
  }

  static _srcUnpackDirExists(srcPathname) {
    const srcBasename = path.basename(srcPathname, ".src");
    const srcDirname = path.dirname(srcPathname);
    const srcUnpackDir = path.join(srcDirname, srcBasename);
    return Compose.fileExists(srcUnpackDir);
  }

  async _updateLocalResource({ type, src, pathname, moduleName }) {
    signale.note(`Downloading '${src}' to '${pathname}'...`);
    const httpAgent = new HTTPAgent({ debug: this.$.debug });
    await httpAgent.downloadFileTo(src, pathname);
    if (type === "src") {
      signale.note(`Unpacking '${type}' from '${src}' into '${pathname}'`);
      await this.unpackSrc(pathname, moduleName);
    }
    signale.note(`Done.`);
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

  /**
   * @param {[{string}]} moduleList List of module's name[@version]
   * @returns {Promise<void>}
   */
  async upgrade(moduleList = []) {
    await this.loadContext();

    if (moduleList.length <= 0) {
      moduleList = this.repoXML.getModuleList().map(module => {
        return {
          name: module.$.name,
          version: this.$.latest ? "latest" : module.$.version,
          registry: module.$.registry
        };
      });
    } else {
      for (let i = 0; i < moduleList.length; i++) {
        const moduleAtVersion = this.parseNameAtVersion(moduleList[i]);
        const module = this.repoXML.getModuleByName(moduleAtVersion.name);
        moduleList[i] = {
          name: module.$.name,
          version: this.$.latest
            ? "latest"
            : moduleAtVersion.version !== ""
            ? moduleAtVersion.version
            : module.$.version,
          registry: module.$.registry
        };
      }
    }

    this.debug(moduleList, { depth: 20 });

    for (let i = 0; i < moduleList.length; i++) {
      const module = moduleList[i];
      signale.note(
        `Installing '${module.name}' with version '${module.version}'`
      );
      await this._installSemverModule({
        name: module.name,
        version: module.version,
        registry: module.registry
      });
    }

    this.debug(this.repoLockXML.data, { depth: 20 });

    await this.commitContext();
  }

  /**
   * @param {string} str
   * @returns {{name: (*|string), version: string}}
   */
  parseNameAtVersion(str) {
    const tokens = str.split("@");
    if (tokens.length > 2) {
      throw new ComposeError(`Malformed name[@version] string '${str}'`);
    }
    const name = tokens[0];
    let version = "";
    if (tokens.length === 2) {
      version = tokens[1];
    }
    return { name, version };
  }
}

module.exports = { Compose, ComposeError };

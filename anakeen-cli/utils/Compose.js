const console = require("console");
const path = require("path");
const util = require("util");
const fs = require("fs");
const URL = require("url").URL;
const semver = require("semver");
const signale = require("signale");
const tar = require("tar");
const rimraf = require("rimraf");
const fetch = require("node-fetch");
const urlJoin = require("url-join");

const GenericError = require(path.resolve(__dirname, "GenericError.js"));
const { RepoXML } = require(path.resolve(__dirname, "RepoXML.js"));
const { RepoLockXML } = require(path.resolve(__dirname, "RepoLockXML.js"));
const { HTTPAgent } = require(path.resolve(__dirname, "HTTPAgent.js"));
const { RepoContentXML } = require(path.resolve(__dirname, "RepoContentXML.js"));
const SHA256Digest = require(path.resolve(__dirname, "SHA256Digest"));
const Utils = require(path.resolve(__dirname, "Utils.js"));
const { HTTPCredentialStore } = require(path.resolve(__dirname, "HTTPCredentialStore.js"));
const { checkFile } = require("@anakeen/anakeen-module-validation");

const fs_stat = util.promisify(fs.stat);
const fs_mkdir = util.promisify(fs.mkdir);
const fs_readdir = util.promisify(fs.readdir);

class ComposeError extends GenericError {}
class ComposeLockError extends GenericError {}

const REPO_NAME = "repo.xml";
const REPO_LOCK_NAME = "repo.lock.xml";

class Compose {
  constructor(options = {}) {
    if (typeof options !== "object") {
      options = {};
    }
    this.modeDebug = options.hasOwnProperty("debug") && options.debug === true;
    this.frozenLockfile = options.hasOwnProperty("frozenLockfile") && options.frozenLockfile === true;
    this.latest = options.hasOwnProperty("latest") && options.latest === true;
    this.cwd = options.cwd;
    this.currentRepoPath = path.resolve(this.cwd, REPO_NAME);
    this.currentLockPath = path.resolve(this.cwd, REPO_LOCK_NAME);
    this.credentialStore = new HTTPCredentialStore(this.cwd);
  }

  /**
   * Check if the repo exist and the XML files are valid
   * @returns {Promise<Compose>}
   */
  async checkIfInitialized() {
    if (!fs.existsSync(this.currentRepoPath)) {
      throw new ComposeError(`There is no compose repository at this path ${this.currentRepoPath}`);
    }
    const check = checkFile(this.currentRepoPath);
    if (!check.ok) {
      throw new ComposeError(`The repo file is not valid ${this.currentRepoPath} : ${check.error}`);
    }
    if (fs.existsSync(this.currentLockPath)) {
      const checkLock = checkFile(this.currentLockPath);
      if (!checkLock.ok) {
        throw new ComposeError(`The repo file is not valid ${this.currentLockPath} : ${checkLock.error}`);
      }
    }
    return this;
  }

  /**
   * Load 'repo.xml' and 'repo.lock.xml' files
   * @returns {Promise<void>}
   */
  async loadContext() {
    if (!this.repoXML) {
      this.repoXML = new RepoXML(this.currentRepoPath, this.credentialStore);
      await this.repoXML.load();
    }

    if (!this.repoLockXML) {
      this.repoLockXML = new RepoLockXML(this.currentLockPath);
      await this.repoLockXML.load();
    }
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
    if (this.modeDebug) {
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
   * @param {string} localRepo
   * @param {string} localSrc
   * @returns {Promise<void>}
   */
  async init({ localRepo, localSrc }) {
    let stats;

    localRepo = path.resolve(this.cwd, localRepo);
    localSrc = path.resolve(this.cwd, localSrc);

    if (await Utils.fileExists(this.currentRepoPath)) {
      throw new Error(`File 'repo.xml' already exists`);
    }
    const checkRepo = async repo => {
      try {
        stats = await fs_stat(repo);
      } catch (e) {
        try {
          await fs_mkdir(repo, { recursive: true });
          stats = await fs_stat(repo);
        } catch (e) {
          throw new Error(`Could not create localRepo directory '${repo}': ${e}`);
        }
      }
      if (!stats.isDirectory()) {
        throw new Error(`localRepo '${repo}' is not a directory`);
      }
    };

    await checkRepo(localRepo);
    await checkRepo(localSrc);

    const repoXML = new RepoXML(this.currentRepoPath);
    repoXML.setData(
      Compose.repoXMLTemplate({
        localRepo: path.relative(path.dirname(this.currentRepoPath), localRepo),
        localSrc: path.relative(path.dirname(this.currentRepoPath), localSrc)
      })
    );
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
    await this.repoXML.addAppRegistry({ name, url });
    if (authUser) {
      let httpCredentialStore = new HTTPCredentialStore(this.cwd);
      await httpCredentialStore.loadCredentialStore();
      await httpCredentialStore.setCredential(url, authUser, authPassword);
      await httpCredentialStore.saveCredentialStore();
    }
    await this.commitContext();
  }

  async checkRegistry({ url, authUser, authPassword }) {
    const response = await fetch(url, {
      headers: { Authorization: "Basic " + Buffer.from(authUser + ":" + authPassword).toString("base64") }
    });
    if (!response.ok) {
      throw new Error(`Unexpected HTTP status ${response.status} ('${response.statusText}')`);
    }
    return true;
  }

  /**
   * @param {string} moduleName Module's name
   * @param {string} moduleVersion Module's semver version
   * @param {string} registryName Registry's unique name/identifier from which the module is to be downloaded.
   */
  async addModule({ moduleName: moduleName, moduleVersion: moduleVersion, registry: registryName }) {
    await this.loadContext();

    //Throw an exception if the registry doesn't exist
    await this.repoXML.getRegistryByName(registryName);

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
  async _installSemverModule({ name: moduleName, version: moduleVersion, registry: registryName }) {
    const appRegistry = this.repoXML.getRegistryByName(registryName);
    let ping = false;
    try {
      ping = await appRegistry.ping();
    } catch (e) {
      throw new ComposeError(e.message);
    }
    if (!ping) {
      throw new ComposeError(`Registry '${registryName}' does not seems to be valid`);
    }
    const moduleList = await appRegistry.getModuleList(moduleName, moduleVersion);
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

    await this.repoXML.addModule({
      name: module.name,
      version: `^${module.version}`,
      registry: registryName
    });
  }

  async _installAndLockModuleVersion({ name: moduleName, version: moduleVersion, registry: registryName }) {
    const localRepo = await this.repoXML.getConfigLocalRepo();
    const localSrc = await this.repoXML.getConfigLocalSrc();

    const appRegistry = this.repoXML.getRegistryByName(registryName);

    /* Remove previous module's resources */
    const lockedModule = this.repoLockXML.getModuleByName(moduleName);
    if (lockedModule) {
      if (lockedModule.$.version === moduleVersion) {
        signale.note(`Module '${moduleName}' with version '${moduleVersion}' is up-to-date`);
        return;
      } else {
        await this.deleteModuleResources(lockedModule);
      }
    }

    const moduleInfo = await appRegistry.getModuleVersionInfo(moduleName, moduleVersion);
    this.debug({ moduleInfo });

    const resources = {
      app: undefined,
      src: undefined
    };
    for (let type of ["app", "src"]) {
      if (!moduleInfo.hasOwnProperty(type)) {
        continue;
      }
      const url = urlJoin(appRegistry.getModuleVersionURL(moduleName, moduleVersion), type, moduleInfo[type]);
      let pathname;
      if (type === "app") {
        pathname = path.join(this.cwd, localRepo, moduleInfo[type]);
      } else if (type === "src") {
        pathname = path.join(this.cwd, localSrc, moduleInfo[type]);
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
        sha256: resources.app.sha256,
        pathname: path.basename(resources.app.pathname)
      });
    }
    if (typeof resources.src !== "undefined") {
      newModule.resources.push({
        type: "src",
        src: resources.src.src,
        sha256: resources.src.sha256,
        pathname: path.basename(resources.src.pathname)
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
          rmList.push({ type: "file", path: path.join(this.cwd, dirname, basename) });
          break;
        case "src":
          basename = path.basename(url, ".src");
          dirname = this.repoXML.getConfigLocalSrc();
          rmList.push({
            type: "file",
            path: path.join(dirname, basename + ".src")
          });
          rmList.push({ type: "dir", path: path.join(this.cwd, dirname, basename) });
          break;
      }
    }

    for (let elmt of rmList) {
      if (await Utils.fileExists(elmt.path)) {
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
    const basename = moduleName;
    const dirname = path.dirname(archive);
    const pathname = path.join(dirname, basename);
    if (await Utils.fileExists(pathname)) {
      await Compose.rm_Rf(pathname);
    }
    await fs_mkdir(pathname, { recursive: true });
    return tar.x({
      C: pathname,
      file: archive
    });
  }

  async genRepoContentXML(repoDir) {
    const repoContentXML = new RepoContentXML(path.join(this.cwd, repoDir, "content.xml"));
    repoContentXML.reset();

    let moduleFileList = await fs_readdir(path.join(this.cwd, repoDir));
    moduleFileList = moduleFileList.filter(filename => {
      return filename.match(/\.app$/);
    });
    for (let i = 0; i < moduleFileList.length; i++) {
      const moduleFile = moduleFileList[i];
      await repoContentXML.addModuleFile(path.join(this.cwd, repoDir, moduleFile));
    }

    await repoContentXML.save();

    return moduleFileList;
  }

  /**
   * Install the element if needed
   *
   * @returns {Promise<void>}
   */
  async install() {
    await this.loadContext();

    const moduleLockList = this.repoLockXML.getModuleList();
    const moduleList = this.repoXML.getModuleList();

    const organizedLockList = moduleLockList.reduce((acc, currentLockElement) => {
      acc[currentLockElement.$.name] = currentLockElement;
      return acc;
    }, {});

    //Analyze the lock and the demand part and deduce the module to install
    const analyzedList = moduleList.reduce(
      (acc, currentElement) => {
        if (
          organizedLockList[currentElement.$.name] &&
          semver.satisfies(organizedLockList[currentElement.$.name].$.version, currentElement.$.version)
        ) {
          acc.alreadyLocked[currentElement.$.name] = organizedLockList[currentElement.$.name];
          signale.note(`Found ${currentElement.$.name} locked with ${currentElement.$.version}`);
          return acc;
        }
        acc.toInstall[currentElement.$.name] = currentElement;
        return acc;
      },
      {
        toInstall: {},
        alreadyLocked: {}
      }
    );

    //Check if it's lock file mode and there is things to install
    if (this.frozenLockfile && Object.keys(analyzedList.toInstall).length > 0) {
      throw new ComposeLockError(
        `Some modules to install are not in the lockfile ( ${Object.keys(analyzedList.toInstall).join(
          " "
        )} ), try without the frozen lock option`
      );
    }

    //Install the elements
    //Swipe the lockfile to reconstruct it during the install
    this.repoLockXML.swipeModuleList();
    signale.note(`Found ${Object.keys(analyzedList.toInstall).length} new module(s) to install`);
    await Object.values(analyzedList.toInstall).reduce((acc, module) => {
      return acc.then(async () => {
        return await this._installSemverModule({
          name: module.$.name,
          version: module.$.version,
          registry: module.$.registry
        });
      });
    }, Promise.resolve());

    //Cleaning part
    //Add the keeped module to the lockfile
    Object.values(analyzedList.alreadyLocked).map(currentLocked => {
      moduleList.push(currentLocked);
    });
    //Destroy all the elements, that are not in the lock file
    //Agregate all lockfile path
    const toKeep = this.repoLockXML.getModuleList().reduce(
      (acc, currentModule) => {
        //Aggregate the app part
        if (currentModule.ressources && currentModule.ressources.app) {
          acc.app = [
            ...acc.app,
            ...currentModule.ressources.app.map(currentApp => {
              return currentApp.$.path;
            })
          ];
        }
        //Aggregate the src part
        if (currentModule.ressources && currentModule.ressources.src) {
          acc.src = [
            ...acc.src,
            ...currentModule.ressources.src.map(currentApp => {
              return currentApp.$.path;
            })
          ];
        }
        return acc;
      },
      {
        app: [],
        src: []
      }
    );

    console.log(toKeep);

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
      const pathname = path.join(this.cwd, resourceDir[type], basename);

      let localIsOutdated = true;
      if (await Utils.fileExists(pathname)) {
        const localSha256 = await SHA256Digest.file(pathname);
        localIsOutdated = localSha256 !== sha256;
      }

      if (!localIsOutdated && type === "src" && !Compose._srcUnpackDirExists(pathname)) {
        localIsOutdated = true;
      }

      if (localIsOutdated) {
        signale.note(`Updating outdated resource '${pathname}' from lock src '${src}'`);
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
    return Utils.fileExists(srcUnpackDir);
  }

  async _updateLocalResource({ type, src, pathname, moduleName }) {
    signale.note(`Downloading '${src}' to '${pathname}'...`);
    const httpAgent = new HTTPAgent({ debug: this.modeDebug, credentialStore: this.credentialStore });
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
      const lockedModule = Compose.isModuleInList(module.$.name, moduleLockList);
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
          version: this.latest ? "latest" : module.$.version,
          registry: module.$.registry
        };
      });
    } else {
      for (let i = 0; i < moduleList.length; i++) {
        const moduleAtVersion = this.parseNameAtVersion(moduleList[i]);
        const module = this.repoXML.getModuleByName(moduleAtVersion.name);
        moduleList[i] = {
          name: module.name,
          version: this.latest ? "latest" : moduleAtVersion.version !== "" ? moduleAtVersion.version : module.version,
          registry: module.registry
        };
      }
    }

    this.debug(moduleList, { depth: 20 });

    for (let i = 0; i < moduleList.length; i++) {
      const module = moduleList[i];
      signale.note(`Installing '${module.name}' with version '${module.version}'`);
      await this._installSemverModule({
        name: module.name,
        version: module.version,
        registry: module.registry
      });

      //Update repo.xml
      const lockModule = this.repoLockXML.getModuleByName(module.name);
      this.repoXML.updateModule({
        name: module.name,
        version: lockModule.$.version,
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

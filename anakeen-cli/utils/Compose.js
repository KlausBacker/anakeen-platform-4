const console = require("console");
const path = require("path");
const util = require("util");
const fs = require("fs");
const semver = require("semver");
const signale = require("signale");
const tar = require("tar");
const fetch = require("node-fetch");
const urlJoin = require("url-join");
const JSZip = require("jszip");
const Mustache = require("mustache");

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
const fs_readfile = util.promisify(fs.readFile);
const glob = util.promisify(require("glob"));
const rimraf = util.promisify(require("rimraf"));
const fs_copyFile = util.promisify(fs.copyFile);

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
    this.dev = options.hasOwnProperty("dev") && options.dev === true;
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
      throw new ComposeError(`There is no repo.xml at this path ${this.currentRepoPath}, you should use init command`);
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

  /**
   * Add a local path for the install command
   *
   * @param localPath
   * @returns {Promise<void>}
   */
  async addLocalPath({ localPath }) {
    await this.loadContext();
    this.repoXML.addAppLocalPath({ localPath });
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

    if (!registryName || !moduleVersion) {
      //We don't know the version or the registry, maybe the module is already registred
      let module = this.repoXML.getModuleByName(moduleName);
      if (!registryName) {
        registryName = module.registry;
      }
      if (!moduleVersion) {
        moduleVersion = module.version;
      }
    }

    //Throw an exception if the registry doesn't exist
    await this.repoXML.getRegistryByName(registryName);

    const moduleToAdd = await this._getModuleRefFromRegistry({
      moduleName,
      moduleVersion,
      registryName
    });

    await this.repoXML.addModule({
      name: moduleToAdd.name,
      version: moduleVersion !== "latest" ? moduleVersion : `^${moduleToAdd.version}`,
      registry: registryName
    });

    return moduleToAdd;
  }

  /**
   * Get the version of the registry (need access to registry)
   *
   * @param moduleName
   * @param moduleVersion
   * @param registryName
   * @returns {Promise<{name, version}>}
   * @private
   */
  async _getModuleRefFromRegistry({ moduleName, moduleVersion, registryName }) {
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
    //Get the first element of the array ?¿
    return moduleList[moduleList.length - 1];
  }

  /**
   * Install a module that is present in the 'repo.xml'
   *
   * @param {string} moduleName Module's name
   * @param {string} moduleVersion SemVer version
   * @param {string} registryName Registry's name
   * @param {boolean} noUpdateRepo, only for refresh, we not update the repo.xml
   * @returns {Promise<void>}
   * @private
   */
  async _installSemverModule(
    { name: moduleName, version: moduleVersion, registry: registryName },
    noUpdateRepo = false
  ) {
    const module = await this._getModuleRefFromRegistry({ moduleName, moduleVersion, registryName });

    await this._installAndLockModuleVersion({
      name: module.name,
      version: module.version,
      registry: registryName
    });

    if (noUpdateRepo) {
      return;
    }

    await this.repoXML.addModule({
      name: module.name,
      version: moduleVersion !== "latest" ? moduleVersion : `^${module.version}`,
      registry: registryName
    });
  }

  async _installAndLockModuleVersion({ name: moduleName, version: moduleVersion, registry: registryName }) {
    const localRepo = this._convertPathToAbsolute(this.repoXML.getConfigLocalRepo());
    const localSrc = this._convertPathToAbsolute(this.repoXML.getConfigLocalSrc());

    const appRegistry = this.repoXML.getRegistryByName(registryName);

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
        pathname = path.join(localRepo, moduleInfo[type]);
      } else if (type === "src") {
        pathname = path.join(localSrc, moduleInfo[type]);
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
        sha256: await SHA256Digest.hash(pathname),
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
          dirname = this._convertPathToAbsolute(this.repoXML.getConfigLocalRepo());
          rmList.push({ type: "file", path: path.join(dirname, basename) });
          break;
        case "src":
          basename = path.basename(url, ".src");
          dirname = this._convertPathToAbsolute(this.repoXML.getConfigLocalSrc());
          rmList.push({
            type: "file",
            path: path.join(dirname, basename + ".src")
          });
          rmList.push({ type: "dir", path: path.join(dirname, basename) });
          break;
      }
    }

    for (let elmt of rmList) {
      if (await Utils.fileExists(elmt.path)) {
        signale.note(`Removing ${elmt.type} '${elmt.path}'...`);
        await rimraf(elmt.path);
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
      await rimraf(pathname);
    }
    await fs_mkdir(pathname, { recursive: true });
    return tar.x({
      C: pathname,
      file: archive
    });
  }

  async genRepoContentXML(repoDir) {
    const repoContentXML = new RepoContentXML(path.join(repoDir, "content.xml"));
    repoContentXML.reset();

    let moduleFileList = await fs_readdir(repoDir);
    moduleFileList = moduleFileList.filter(filename => {
      return filename.match(/\.app$/);
    });
    for (let i = 0; i < moduleFileList.length; i++) {
      const moduleFile = moduleFileList[i];
      await repoContentXML.addModuleFile(path.join(repoDir, moduleFile));
    }

    await repoContentXML.save();

    return moduleFileList;
  }

  /**
   * Generate a control with the app inside
   * @returns {Promise<void>}
   */
  async generateLocalControl({
    localRepoName = "repo",
    controlTarget = "control.zip",
    customReadme = "",
    addLocalApp = ""
  }) {
    await this.loadContext();
    signale.note(`Launch install to refresh repo and add custom app`);
    if (addLocalApp) {
      if (!path.isAbsolute(addLocalApp)) {
        addLocalApp = path.resolve(process.cwd(), addLocalApp);
      }
      const testLocalPath = await glob(addLocalApp, { absolute: true });
      if (testLocalPath.length === 0) {
        throw new ComposeError(`No elements were found at ${addLocalApp}`);
      }
      signale.note(`Add local app from ${addLocalApp}`);
    }
    await this.install({ customLocalPath: addLocalApp });
    const localRepo = this._convertPathToAbsolute(this.repoXML.getConfigLocalRepo());
    const controlLock = this.repoLockXML.getModuleByName("anakeen-control");
    if (controlLock === undefined) {
      throw new ComposeError(
        "You have no control in your repo.lock.xml, you should add it (compose install anakeen-control)"
      );
    }
    signale.note(`Open the control zip`);
    const controlPath = path.join(localRepo, controlLock.resources[0].app[0].$.path);
    const zipFile = await new JSZip.external.Promise((resolve, reject) => {
      fs.readFile(controlPath, function(err, data) {
        if (err) {
          reject(err);
        } else {
          resolve(data);
        }
      });
    }).then(data => {
      return JSZip.loadAsync(data);
    });
    signale.note(`Add app from ${localRepo}`);
    //add localRepo files
    const appFile = await glob(path.join(localRepo, "/**/*.app"), { absolute: true });
    appFile.map(currentApp => {
      const stream = fs.createReadStream(currentApp);
      this.debug(`Add app ${currentApp}`);
      zipFile.file(path.join(localRepoName, path.basename(currentApp)), stream);
    });
    zipFile.file(path.join(localRepoName, "content.xml"), fs.createReadStream(path.join(localRepo, "content.xml")));
    const readPath = customReadme === "" ? path.join(__dirname, "templates", "README.md.mustache") : customReadme;
    const tpl_readme = await fs_readfile(readPath, {
      encoding: "utf-8"
    });
    signale.note(`Add README.md`);
    zipFile.file(
      "README.md",
      Mustache.render(tpl_readme, {
        path: localRepoName
      })
    );

    signale.note(`Save the new zip`);
    return new Promise((resolve, reject) => {
      zipFile
        .generateNodeStream({ type: "nodebuffer", streamFiles: true, platform: "UNIX" })
        .pipe(fs.createWriteStream(controlTarget))
        .on("error", () => {
          reject(`Unable to write ${controlTarget}`);
        })
        .on("finish", () => {
          signale.note(`${controlTarget} done`);
          resolve();
        });
    }).then(async () => {
      signale.note(`Launch install to refresh repo and remove custom app`);
      return await this.install({});
    });
  }

  /**
   * Install the element if needed
   *
   * @returns {Promise<void>}
   */
  async install({ withoutLockFile = false, latest = false, customLocalPath = "", dev = false }) {
    let moduleLockList = [];
    //region prepare data
    await this.loadContext();

    const localRepo = this._convertPathToAbsolute(this.repoXML.getConfigLocalRepo());
    const localSrc = this._convertPathToAbsolute(this.repoXML.getConfigLocalSrc());

    //Create it if doesn't exist
    try {
      await fs_mkdir(localRepo);
    } catch (e) {
      if (e.code !== "EEXIST") {
        throw new ComposeError(`Unable to create local repo ${localRepo} : ${JSON.stringify(e)}`);
      }
    }
    try {
      await fs_mkdir(localSrc);
    } catch (e) {
      if (e.code !== "EEXIST") {
        throw new ComposeError(`Unable to create local repo ${localSrc} : ${JSON.stringify(e)}`);
      }
    }

    if (withoutLockFile === false) {
      moduleLockList = this.repoLockXML.getModuleList();
    }
    let moduleList = this.repoXML.getModuleList();
    if (latest) {
      //Process the module list to push all semver requirement to latest
      moduleList = moduleList.map(currentModule => {
        currentModule.$.version = "latest";
        return currentModule;
      });
    } else if (dev) {
      //Process the module list to push all semver requirement to dev
      moduleList = moduleList.map(currentModule => {
        currentModule.$.version = "dev";
        return currentModule;
      });
    }

    const organizedLockList = moduleLockList.reduce((acc, currentLockElement) => {
      acc[currentLockElement.$.name] = currentLockElement;
      return acc;
    }, {});
    //endregion prepare data

    //region analyze data and lock
    const moduleToInstall = {};
    const moduleToRefresh = {};
    const moduleLocked = {};

    if (!moduleList || moduleList.length === 0) {
      signale.note(`No module to install`);
      return;
    }

    //Analyze the lock and the demand part and deduce the module to install
    //It's async for the sha part, so we wait with an await
    await Promise.all(
      moduleList.map(async currentElement => {
        this.debug(`${currentElement.$.name} : check range ${currentElement.$.version}`);
        if (organizedLockList[currentElement.$.name]) {
          this.debug(
            `${currentElement.$.name} : there is a lock with version ${
              organizedLockList[currentElement.$.name].$.version
            }`
          );
        }
        if (
          //Module is in the locked list
          organizedLockList[currentElement.$.name] &&
          //semver is good
          semver.satisfies(organizedLockList[currentElement.$.name].$.version, currentElement.$.version)
        ) {
          if (
            //File is here and sha1 is good
            await this.repoLockXML.checkIfModuleIsValid({
              name: currentElement.$.name,
              appPath: localRepo,
              srcPath: localSrc
            })
          ) {
            this.debug(`${currentElement.$.name} : The lock is good, the files are here, we keep it`);
            //This module is locked, semver is good and files are good, we keep it
            return (moduleLocked[currentElement.$.name] = organizedLockList[currentElement.$.name]);
          } else {
            this.debug(`${currentElement.$.name} : The lock is good, the files not good, we refresh it`);
            this.debug(`${currentElement.$.name} : Mode frozen lock we keep the exact ref of the lock`);
            const refreshElement = JSON.parse(JSON.stringify(currentElement));
            refreshElement.$.version = organizedLockList[currentElement.$.name].$.version;
            return (moduleToRefresh[currentElement.$.name] = refreshElement);
          }
        }
        this.debug(`${currentElement.$.name} : We need to install it`);
        //This module must be installed
        return (moduleToInstall[currentElement.$.name] = currentElement);
      })
    );

    //Check if it's lock file mode and there is things to install
    if (this.frozenLockfile && Object.keys(moduleToInstall).length > 0) {
      //We are frozen and all the module are not good, so it's a fail
      throw new ComposeLockError(
        `Some modules to install are not in the lockfile or have not the good semver or the good sha1 ( ${Object.keys(
          moduleToInstall
        ).join(" ")} ), try without the frozen lock option`
      );
    }
    //endregion analyze data and lock

    //region Install the elements
    //Swipe the lockfile to reconstruct it during the install
    this.repoLockXML.swipeModuleList();
    if (Object.keys(moduleToRefresh).length > 0) {
      signale.note(`Found ${Object.keys(moduleToRefresh).length} new module(s) to refresh`);
      //Refresh all modules to refresh
      //Install is async so we launch a bunch of promises and wait for the result
      await Promise.all(
        Object.values(moduleToRefresh).map(async module => {
          return await this._installSemverModule(
            {
              name: module.$.name,
              version: module.$.version,
              registry: module.$.registry
            },
            true
          );
        })
      );
    }
    signale.note(`Found ${Object.keys(moduleToInstall).length} new module(s) to install`);
    //Install is async so we launch a bunch of promises and wait for the result
    await Promise.all(
      Object.values(moduleToInstall).map(async module => {
        return await this._installSemverModule({
          name: module.$.name,
          version: module.$.version,
          registry: module.$.registry
        });
      })
    );
    //endregion Install the elements

    //region Clean
    //Add the keeped lock module to the lockfile
    Object.values(moduleLocked).map(currentLocked => {
      moduleLockList.push(currentLocked);
    });

    //Destroy all the elements, that are not in the lock file
    //Agregate all lockfile path
    const toKeep = this.repoLockXML.getModuleList().reduce(
      (acc, currentModule) => {
        //Aggregate the app part // Tricky the code generate not the same structure than the parsing :faceplam:
        const resources = currentModule.resources[0] || currentModule.resources;
        if (resources && resources.app) {
          acc.app = [
            ...acc.app,
            ...resources.app.map(currentApp => {
              return currentApp.$.path;
            })
          ];
        }
        //Aggregate the src part
        if (resources && resources.src) {
          acc.src = [
            ...acc.src,
            ...resources.src.map(currentApp => {
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

    //Find all the files
    const filesInApp = await glob(path.join(localRepo, "*"));
    // Deduce files to destroy
    const appFileToDestroy = filesInApp.filter(currentPath => {
      const fileName = path.basename(currentPath);
      //The file is content.xml or an element to keep
      return !(fileName === "content.xml" || toKeep.app.includes(fileName));
    });
    const filesInSrc = await glob(path.join(localSrc, "*"), { nodir: true });
    // Deduce files to destroy
    const srcFilesToDestroy = filesInSrc.filter(currentPath => {
      const fileName = path.basename(currentPath);
      //The file is an element to keep
      return !toKeep.src.includes(fileName);
    });
    await Promise.all(
      [...srcFilesToDestroy, ...appFileToDestroy].map(async currentPath => {
        this.debug(`Suppress file ${currentPath}`);
        return await rimraf(currentPath);
      })
    );
    //endregion Clean

    //region generate repo.xml
    //Generate local repo xml

    //region handle localApp
    const localPath = this.repoXML.getAppLocalPath();
    if (customLocalPath) {
      localPath.push(customLocalPath);
    }
    //Find app
    await Promise.all(
      localPath.map(async currentGlob => {
        //Find app
        this.debug(`LocalApp : search for app in ${currentGlob}`);
        const appPath = await glob(currentGlob, { absolute: true });
        //Copy app
        this.debug(`LocalApp : found ${appPath.join(" ")} in ${currentGlob}`);
        return await Promise.all(
          appPath.map(async currentPath => {
            this.debug(`LocalApp : copy ${currentPath} to ${path.join(localRepo, path.basename(currentPath))}`);
            await fs_copyFile(currentPath, path.join(localRepo, path.basename(currentPath)));
          })
        );
      })
    );
    //Copy app
    //endregion handle localApp

    signale.note(`Generating 'content.xml' in '${localRepo}'...`);
    const appList = await this.genRepoContentXML(localRepo);
    this.debug({ appList: appList }, { depth: 20 });

    //endregion generate repo.xml
    //ommit XML
    await this.commitContext();
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

  _convertPathToAbsolute(currentPath) {
    if (path.isAbsolute(currentPath)) {
      return currentPath;
    }
    return path.join(this.cwd, currentPath);
  }
}

module.exports = { Compose, ComposeError };

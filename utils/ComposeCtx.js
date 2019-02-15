const path = require("path");

const GenericError = require(path.resolve(__dirname, "GenericError.js"));

class ComposeCtxError extends GenericError {}

class ComposeCtx {
  constructor(repoXML, repoLockXML) {
    if (repoXML.constructor.name !== "RepoXML") {
      throw new ComposeCtxError(
        `First argument must be an instance of class 'RepoXML'`
      );
    }
    if (repoLockXML.constructor.name !== "RepoLockXML") {
      throw new ComposeCtxError(
        `First argument must be an instance of class 'RepoLockXML'`
      );
    }
    this.repoXML = repoXML;
    this.repoLockXML = repoLockXML;
  }
}

module.exports = { ComposeCtx, ComposeCtxError };

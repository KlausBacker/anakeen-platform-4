const proxy = require("express-http-proxy");
const platformUrl=process.env.platformUrl;

if (! platformUrl) {
  throw Error("Cannot find platformUrl on environnement var. try to set env \"platformUrl\" before lauch serveur.");
}

const proxyUrl = platformUrl;
console.log("Use server platform on ", proxyUrl);

module.exports = function expressMiddleware(router) {
  ["/api", "/locale", "/Anakeen", "/uiAssets", "/TEST_DOCUMENT_SELENIUM"].forEach(urlPath => {
    router.use(
      `${urlPath}/`,
      proxy(proxyUrl, {
        proxyReqPathResolver: function(req) {
          return `${urlPath}${req.url}`;
        },
        proxyReqOptDecorator: function(proxyReqOpts) {
          const buffer =  Buffer.from(`admin:anakeen`);
          proxyReqOpts.headers["Authorization"] = `Basic ${buffer.toString("base64")}`;

          return proxyReqOpts;
        }
      })
    );
  });



};

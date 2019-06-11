const gulp = require("gulp");
const { build } = require("./build");
const { deploy } = require("./deploy");
const { po } = require("./po");
const { stub } = require("./stub");
const { autoconf } = require("../utils/autoconf");

exports.tasks = () => {
  gulp.task("anakeen-platform-build", async () => {
    const config = (await autoconf()) || {};
    build(config);
    return await gulp.task("build")();
  });

  gulp.task("anakeen-platform-deploy", async () => {
    const config = (await autoconf()) || {};
    deploy(config);
    return await gulp.task("deploy")();
  });

  gulp.task("anakeen-platform-po", async () => {
    const config = (await autoconf()) || {};
    po(config);
    return await gulp.task("po")();
  });

  gulp.task("anakeen-platform-stub", async () => {
    const config = (await autoconf()) || {};
    stub(config);
    return await gulp.task("stub")();
  });
};

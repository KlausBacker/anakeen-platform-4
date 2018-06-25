const gulp = require('gulp');
const tar = require('gulp-tar');
const gzip = require('gulp-gzip');
const addsrc = require('gulp-add-src');
const { getModuleInfo } = require("../utils/moduleInfo");
const path = require("path");
const appConst = require("../utils/appConst");

exports.build = ({sourcePath = '.', targetPath = '.'}) => {
        return gulp.task('build', async () => {
                try {
                        const moduleInfo = await getModuleInfo(sourcePath);
                        const moduleFileName = `${moduleInfo.moduleInfo.name}-${moduleInfo.moduleInfo.version}-${moduleInfo.moduleInfo.release}`;
                        const buildPath = moduleInfo.buildInfo.conf.sources[0].source.map(currentSource => {
                                return path.join(sourcePath, currentSource.$.path, "**");
                        });
                        gulp.src(buildPath)
                                .pipe(tar("content"))
                                .pipe(gzip({ extension: 'tar.gz' }))
                                .pipe(addsrc(path.join(sourcePath, appConst.infoPath)))
                                .pipe(tar(moduleFileName))
                                .pipe(gzip({ extension: 'app' }))
                                .pipe(gulp.dest(targetPath));
                } catch (e) {
                        throw e;
                }
        });
};
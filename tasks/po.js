const gulp = require('gulp');
const gzip = require('gulp-gzip');
const { getPOExtractor } = require("../utils/POExtractor");
const path = require("path");
const appConst = require("../utils/appConst");

exports.po = (sourcePath, targetPath = './extraction', format = 'all') => {
    return gulp.task('po', async () => {
        if(sourcePath === undefined) {
        console.log("No source path specified.");
        return;
    }
    try {
        if(targetPath.startsWith('./')) {
            targetPath = sourcePath + targetPath.substr(1);
        }
        const POExtractor = await getPOExtractor(sourcePath, targetPath, format);
        gulp.src(path.join(POExtractor.extractDir, "**"))
            .pipe(gulp.dest(targetPath));
    } catch (e) {
        throw e;
    }
});
};
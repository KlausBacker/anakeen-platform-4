const gulp = require('gulp');
const { getPOExtractor } = require("../utils/POExtractor");
const path = require("path");
const signale = require('signale');

exports.po = ({sourcePath, targetPath = './extraction', format = 'all'}) => {
    return gulp.task('po', async () => {
        if(sourcePath === undefined) {
        signale.error("No source path specified.");
        return;
    }
    try {
        const POExtractor = await getPOExtractor(sourcePath, targetPath, format);
        gulp.src(path.join(POExtractor.extractDir, "**"))
            .pipe(gulp.dest(targetPath));
    } catch (e) {
        throw e;
    }
});
};
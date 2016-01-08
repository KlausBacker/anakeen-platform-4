var expectResults = [];

var ansi = {
    green: '\x1B[32m',
    red: '\x1B[31m',
    yellow: '\x1B[33m',
    none: '\x1B[0m'
};
/**
 * A jasmine reporter is just an object with the right functions available.
 * None of the functions here are required when creating a custom reporter, any that are not specified on your reporter will just be ignored.
 */
exports.reporter = {
    /**
     * ### jasmineStarted
     *
     * `jasmineStarted` is called after all of the specs have been loaded, but just before execution starts.
     */
    jasmineStarted: function expectjasmineStarted(/*suiteInfo*/)
    {
        'use strict';
        /**
         * suiteInfo contains a property that tells how many specs have been defined
         */
        expectResults = [];
    },

    /**
     * ### specDone
     *
     * `specDone` is invoked when an `it` and its associated `beforeEach` and `afterEach` functions have been run.
     *
     * While jasmine doesn't require any specific functions, not defining a `specDone` will make it impossible for a reporter to know when a spec has failed.
     */
    specDone: function expectspecDone(result)
    {
        'use strict';
        /**
         * The result here is the same object as in `specStarted` but with the addition of a status and lists of failed and passed expectations.
         */

        /**
         * The `passedExpectations` are provided mostly for aggregate information.
         */
        if (result.status !== "pending") {
            expectResults.push({
                description: result.description,
                successCount: result.passedExpectations.length,
                failCount: result.failedExpectations.length
            });

            if (result.failedExpectations.length > 0) {
                process.stdout.write(ansi.red);
                process.stdout.write(result.description+ " : Success "+ result.passedExpectations.length + ", Failed :" + result.failedExpectations.length);
            } else {
                process.stdout.write(ansi.green);
                process.stdout.write(result.description+ " : Success "+ result.passedExpectations.length);
            }
            process.stdout.write(ansi.none + "\n");
        }
    },

    /**
     * ### jasmineDone
     *
     * When the entire suite has finished execution `jasmineDone` is called
     */
    jasmineDone: function expectjasmineDone()
    {
        'use strict';

        var totalSuccess = 0, totalFail = 0;

        expectResults.forEach(function eachexpectjasmineDone(result)
        {
            if (result.failCount > 0) {

            process.stdout.write(ansi.red);
            process.stdout.write("\t- "+result.description+' : ' + result.successCount + " success, " + result.failCount + " failed");
            } else {

            process.stdout.write(ansi.green);
            process.stdout.write("\t- "+result.description+' : ' + result.successCount + " success");
            }
        process.stdout.write(ansi.none + "\n");
            totalSuccess += result.successCount;
            totalFail += result.failCount;
        });

        if (totalFail === 0) {
            process.stdout.write(ansi.green);
            process.stdout.write('Finished : ' + totalSuccess + " success");
        } else {
            process.stdout.write(ansi.red);
            process.stdout.write('Finished : ' + totalSuccess + " success, " + totalFail + " failed");
        }
        process.stdout.write(ansi.none + "\n");
    }
};

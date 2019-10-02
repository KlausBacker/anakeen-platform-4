const util = require("util");
const child_process = require("child_process");

const exec = util.promisify(child_process.exec);

let registry = process.env.CIBUILD_INTEGRATION_ANAKEEN_NPM_REGISTRY;
const stableBranchRegexp = new RegExp("[\\d]+\\.[\\d]+-stable$");

if (stableBranchRegexp.test(process.env.CI_COMMIT_REF_NAME)) {
    registry = process.env.CIBUILD_STABLE_ANAKEEN_NPM_REGISTRY;
}

if (!registry) {
    console.error("Registry not found");
    process.exit(2);
}

console.log(`npm config set @anakeen:registry ${registry}`);
exec(
        `npm config set @anakeen:registry ${registry}`
    )
    .then(result => {
        console.log(result);
    })
    .catch(error => {
        console.error(error);
        process.exit(3);
    });
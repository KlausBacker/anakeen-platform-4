let bucket = process.env.CIBUILD_INTEGRATION_ANAKEEN_APP_REGISTRY_URL;
const stableBranchRegexp = new RegExp("[\\d]+\\.[\\d]+-stable$");

if (stableBranchRegexp.test(process.env.CI_COMMIT_REF_NAME)) {
    bucket = process.env.CIBUILD_STABLE_ANAKEEN_APP_REGISTRY_URL;
}

if (!bucket) {
    console.error("bucket not found");
    process.exit(2);
}

console.log(bucket);
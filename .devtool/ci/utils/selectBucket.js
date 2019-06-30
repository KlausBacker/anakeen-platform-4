let bucket = process.env.CIBUILD_INTEGRATION_ANAKEEN_APP_REGISTRY_BUCKET;
const stableBranchRegexp = new RegExp("[\\d]+\\.[\\d]+-stable$");

if (stableBranchRegexp.test(process.env.CI_MERGE_REQUEST_TARGET_BRANCH_NAME)) {
    bucket = process.env.CIBUILD_STABLE_ANAKEEN_APP_REGISTRY_BUCKET;
}

if (!bucket) {
    console.error("bucket not found");
    process.exit(2);
}

console.log(bucket);
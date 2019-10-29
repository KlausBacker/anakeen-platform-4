const fs = require("fs");


function msgError(_success, msg = "") {
    const rjson = {
        success: _success,
        error: "    " + msg
    }

    return rjson;
}

exports.checkSetting = checkSetting => {


    // return console.log(checkSetting);
    if (checkSetting.type === undefined) {
        return msgError(false, "Type of the setting is not in the cmd");
    }
    if (checkSetting.sourcePath !== "." && checkSetting.sourcePath !== undefined) {
        console.log(fs.existsSync(checkSetting.sourcePath))
        if (!fs.existsSync(checkSetting.sourcePath)) {
            return msgError(false, "The path for the \"s\" option not exist");
        }
    }
    if (checkSetting.type === "Masks" || checkSetting.type === "FieldAccess") {
        if (checkSetting.associatedSmartStructure === "" || checkSetting.associatedSmartStructure === undefined) {
            return msgError(false, "You need to associate an Smart Structure with this Type.");
        }
    }
    // if (checkSetting.name === "") {
    //     return msgError(false, "You need to put name in the option.");
    // }
    // if (checkSetting.v !== ) {
    // }
    // if (checkSetting.m !== ) {
    // }
    return msgError(true);
};
import fetch from "node-fetch";
import uuid = require("uuid/v4");
// eslint-disable-next-line no-unused-vars
import AbstractContext, { ISmartElementProps, ISmartElementValues } from "./AbstractContext";
// eslint-disable-next-line no-unused-vars
import Credentials from "./utils/Credentials";
import SmartElement from "./utils/SmartElement";

export default class SimpleContext extends AbstractContext {
  private static CREATION_API: string = "/api/v2/smart-structures/%s/smart-elements/";

  protected testTagUid!: string;

  constructor(credentials: Credentials) {
    super(credentials);
    this.testTagUid = uuid();
  }

  public async getSmartElement(seName: string | ISmartElementProps, seValues?: ISmartElementValues) {
    if (typeof seName === "string") {
      return super.getSmartElement(seName);
    } else {
      // Creation or get (UPSERT mode)
      const ssName = seName.smartStructure;
      const requestData = { document: { attributes: {} } };
      if (ssName) {
        const url = SimpleContext.CREATION_API.replace(/%s/g, ssName);
        if (seValues && typeof seValues === "object") {
          requestData.document.attributes = seValues;
        }
        const response = await fetch(this.credentials.getCompleteUrl(url), {
          body: JSON.stringify(requestData),
          headers: {
            "Content-Type": "application/json",
            ...this.credentials.getBasicHeader()
          },
          method: "post"
        });
        const responseJson = await response.json();
        if (responseJson.success && responseJson.data && responseJson.data.document) {
          return new SmartElement(responseJson.data.document);
        } else {
          throw new Error("Unfound Smart Element data");
        }
      }
    }
  }

  public clean() {
    // eslint-disable-next-line no-console
    console.log("Clean");
  }
}

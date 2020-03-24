/**
 * Class to manage Grid errors.
 */
import GridEvent from "../AnkGridEvent/AnkGridEvent";
import AnkSmartElementGrid from "../AnkSEGrid.component";

export enum GridErrorCodes {
  URL_NOT_EXIST = "grid/url-not-exist",
  UNKNOWN = "grid/unknown-error",
  LOCAL_STORAGE = "grid/storage-not-available",
  CONFIGURATION = "grid/configuration-error",
  CONTENT = "grid/content-error",
  EXPORT = "grid/export-error",
  EXPORT_POLLING = "grid/export-polling-error",
  NETWORK = "grid/network-error"
}

export default class GridError {
  protected vueComponent: AnkSmartElementGrid;

  constructor(vueComponent: AnkSmartElementGrid) {
    this.vueComponent = vueComponent;
  }

  error(message: string | Error, code: GridErrorCodes = GridErrorCodes.UNKNOWN): void {
    const error: Error = message as Error;
    const errorMessage = error.message || message;
    console.error(errorMessage);
    const event = new GridEvent(
      {
        code,
        message: errorMessage
      },
      null,
      false,
      "GridErrorEvent"
    );
    this.vueComponent.$emit("gridError", event);
  }
}

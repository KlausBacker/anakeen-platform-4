/**
 * Class to manage Grid errors.
 */
import GridEvent from "./GridEvent";
import AbstractGridUtil from "./AbstractGridUtil";

export default class GridError extends AbstractGridUtil {
  error(message) {
    console.error(message);
    const event = new GridEvent(
      {
        message
      },
      null,
      false,
      "GridErrorEvent"
    );
    this.vueComponent.$emit("grid-error", event);
  }
}

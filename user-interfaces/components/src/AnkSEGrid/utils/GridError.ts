/**
 * Class to manage Grid errors.
 */
import GridEvent from "../AnkGridEvent/AnkGridEvent";
import GridController from "../AnkSEGrid.component";

export default class GridError {
  protected vueComponent: GridController;

  constructor(vueComponent: GridController) {
    this.vueComponent = vueComponent;
  }

  error(message: string): void {
    console.error(message);
    const event = new GridEvent(
      {
        message
      },
      null,
      false,
      "GridErrorEvent"
    );
    this.vueComponent.$emit("gridError", event);
  }
}

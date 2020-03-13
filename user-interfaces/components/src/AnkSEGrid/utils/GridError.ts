/**
 * Class to manage Grid errors.
 */
import GridEvent from "../AnkGridEvent/AnkGridEvent";
import AnkSmartElementGrid from "../AnkSEGrid.component";

export default class GridError {
  protected vueComponent: AnkSmartElementGrid;

  constructor(vueComponent: AnkSmartElementGrid) {
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
    this.vueComponent.$emit("GridError", event);
  }
}

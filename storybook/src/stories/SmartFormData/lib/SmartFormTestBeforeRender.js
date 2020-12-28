import SmartFormTestData from "./SmartFormTestData";

export default class SmartFormTestBeforeRender extends SmartFormTestData {
  constructor(title) {
    super(title);

    this.listeners.ready = this.onReady;
    this.listeners.loaded = this.onLoaded;
    this.listeners.smartFieldBeforeRender = this.onSmartFieldBeforeRender;
    this.isReady = false;
    this._renderedFields = {};
  }

  onLoaded() {
    this._renderedFields = {};
  }

  onSmartFieldBeforeRender(event, smartElement, smartField) {
    if (!this.isReady) {
      const uid = smartElement.controller.uid;
      if (!this._renderedFields[uid]) {
        this._renderedFields[uid] = [];
      }
      this._renderedFields[uid].push(smartField.id);
    }
  }

  get renderedFields() {
    if (this.smartController) {
      const uid = this.smartController.uid;
      return this._renderedFields[uid];
    }
    return [];
  }

  set renderedFields(val) {
    if (this.smartController) {
      const uid = this.smartController.uid;
      this._renderedFields[uid] = val;
    }
  }
}

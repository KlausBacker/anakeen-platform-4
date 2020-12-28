class RecordState {
  constructor() {
    this._sessionId = "@anakeen/storyBookTest";
  }

  get sessionValues() {
    const sessionItem = localStorage.getItem(this._sessionId);
    if (sessionItem) {
      return JSON.parse(sessionItem);
    } else {
      return {};
    }
  }

  setKey(key, value) {
    const values = this.sessionValues;

    values[key] = value;
    localStorage.setItem(this._sessionId, JSON.stringify(values));
  }

  getKey(key) {
    return this.sessionValues[key];
  }

  displayOnSidebar() {
    for (const [key, values] of Object.entries(this.sessionValues)) {
      const anchor = document.getElementById(key);
      if (anchor) {
        let globalSuccess = true;

        values.forEach(testResult => {
          if (testResult.success === false) {
            globalSuccess = false;
          }
        });
        anchor.classList.add("tested");
        if (globalSuccess === true) {
          anchor.classList.add("tested-success");
          anchor.classList.remove("tested-failed");
        } else {
          anchor.classList.add("tested-failed");
          anchor.classList.remove("tested-success");
        }
      }
    }
  }
}

const recording = new RecordState();

export default recording;

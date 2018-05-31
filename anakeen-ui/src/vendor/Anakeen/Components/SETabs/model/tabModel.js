import Vue from 'vue';

export default class TabModel {

    constructor() {
        this.openedTabs = new Vue.kendo.data.ObservableArray([]);
        this.modelListeners = [];
        this.openedTabs.bind('change', (e) => {
            this.modelListeners.forEach(l => {
                if (l.action === e.action) {
                    l.callback.call(this, e, this);
                }
            });
            this.modelListeners = this.modelListeners.filter(l => (l.action !== e.action || !l.once));
        });
    }

    on(action, callback) {
        this.modelListeners.push({
            action,
            callback,
            once: false,
        });
    }

    once(action, callback) {
        this.modelListeners.push({
            action,
            callback,
            once: true,
        });
    }

    add(...tab) {
        this.openedTabs.push(...tab);
    }

    get(identifier) {
        if (typeof identifier === 'number') {
            if (identifier >= 0 && identifier < this.size()) {
                return this.openedTabs[identifier];
            }

            return null;
        } else if (typeof identifier === 'object' && identifier !== null) {
            if (identifier.tabId !== undefined) {
                return this.find((item => item.tabId == identifier.tabId));
            }
        }

        return null;
    }

    findIndex(callback) {
        return this.toJSON().findIndex(callback);
    }

    find(callback) {
        return this.toJSON().find(callback);
    }

    size() {
        return this.openedTabs.length;
    }

    isEmpty() {
        return !this.size();
    }

    join(separator) {
        return this.openedTabs.join(separator);
    }

    remove(identifier) {
        if (typeof identifier === 'number') {
            if (identifier >= 0 && identifier < this.size()) {
                return this.openedTabs.splice(identifier, 1)[0];
            }
        } else if (typeof identifier === 'object' && identifier !== null) {
            if (identifier.tabId !== undefined) {
                const index = this.findIndex(t => t.tabId == identifier.tabId);
                if (index > -1) {
                    return this.openedTabs.splice(index, 1)[0];
                }
            }
        }

        return null;
    }

    removeAll() {
        this.openedTabs.splice(0, this.size());
    }

    replace(identifier, newItem) {
        if (typeof identifier === 'number') {
            if (identifier >= 0 && identifier < this.size()) {
                return this.openedTabs.splice(identifier, 1, newItem)[0];
            }
        } else if (typeof identifier === 'object' && identifier !== null) {
            if (identifier.tabId !== undefined) {
                const index = this.findIndex(t => t.tabId == identifier.tabId);
                if (index > -1) {
                    return this.openedTabs.splice(index, 1, newItem)[0];
                }
            }
        }
    }

    toJSON() {
        return this.openedTabs.toJSON();
    }
}

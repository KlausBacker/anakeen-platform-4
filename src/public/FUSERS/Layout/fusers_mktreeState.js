var FUSERS = FUSERS || {};

/**
 * mktreeState object keep track of a tree's state by storing which nodes are
 * opened or closed.
 *
 * The states are stored through a serie of 'collapse()' or 'expand()'
 * operations on node's ids.
 *
 * The operations are stored internally and can be serialized as string.
 *
 * Each operation is represented in the form of a '-' or '+' sign followed
 * by the id of the node. A special node id '*' represent the global tree's
 * state.
 *
 * Example :
 *
 *     '-*,+LI2,+LI1234' means
 *         - start with all nodes closed
 *         - then open 'LI2'
 *         - and finally open 'LI1234'
 *
 * The coalesce() function is responsible for optimizing the states by removing
 * redundant states: ex. remove all '-<nodeId>' when the global state if '-*',
 * etc.
 */

FUSERS.mktreeState = (function () {
    var __CLASS__ = function (str) {
        this.states = this.unserializeStates(str);
    };

    __CLASS__.prototype = {
        'debug': false,
        'autostore': true,
        'paramStore': null,
        'states': [],

        'unserializeStates': function (str) {
            var _states = [];
            if (typeof str !== 'string') {
                return _states;
            }
            var stateList = str.split(',');
            for (var i = 0; i < stateList.length; i++) {
                var state = stateList[i];
                var m = /^([+-])(.+)$/.exec(state);
                if (m === null) {
                    continue;
                }
                var sign = m[1];
                var nodeId = m[2];
                _states.push({
                        sign: sign,
                        id: nodeId
                    }
                );
            }
            return _states;
        },

        'log': function (msg) {
            if (this.debug && typeof console == 'object') {
                console.log(msg);
            }
        },

        'reset': function () {
            this.states = [];
        },

        'setParamStore': function (paramStore) {
            this.paramStore = paramStore;
        },

        'setAutoStore': function (autostore) {
            var _autostore = this.autostore;
            this.autostore = (autostore === true);
            return _autostore;
        },

        'storeState': function () {
            if (this.paramStore && this.paramStore.appName && this.paramStore.paramName) {
                var states = this.serializeStates();
                this.log("storeState '" + states + "'");
                return setparamu(this.paramStore.appName, this.paramStore.paramName, states);
            } else {
                this.log("storeState: paramStore is undefined!");
            }
            return false;
        },

        'serializeStates': function () {
            var str = '';
            for (var i = 0; i < this.states.length; i++) {
                str = str + (str == '' ? '' : ',') + this.states[i].sign + this.states[i].id;
            }
            return str;
        },

        'coalesce': function () {
            var _states = [];
            var global = null;
            var i, j;
            /*
             * Extract the global state (i.e. '+*' or '-*') and remove duplicated states
             */
            for (i = 0; i < this.states.length; i++) {
                if (this.states[i].id == '*') {
                    global = this.states[i];
                } else {
                    var dup = false;
                    for (j = 0; j < _states.length; j++) {
                        if (_states[j].id == this.states[i].id) {
                            _states[j] = this.states[i];
                            dup = true;
                        }
                    }
                    if (!dup) {
                        _states.push(this.states[i]);
                    }
                }
            }
            if (global !== null) {
                /*
                 * Remove redundant states that have the same sign as the global state:
                 * remove all '-N' if global is '-*' or remove all '+N' if global is '+*'
                 */
                var _reduced = [];
                for (i = 0; i < _states.length; i++) {
                    if (_states[i].sign != global.sign) {
                        _reduced.push(_states[i]);
                    }
                }
                _states = _reduced;
                _states.unshift(global);
            }

            this.states = _states;
        },

        'add': function (sign, id) {
            if (id == '*') {
                this.states = [
                    {
                        sign: sign,
                        id: id
                    }
                ];
            } else {
                /* Update if entry already exists... */
                var found = false;
                for (var i = 0; i < this.states; i++) {
                    if (this.states[i].id == id) {
                        this.states[i].sign = sign;
                        found = true;
                        break;
                    }
                }
                /* ... or append a new entry */
                if (!found) {
                    this.states.push({
                        sign: sign,
                        id: id
                    });
                }
                this.coalesce();
            }
            if (this.autostore) {
                this.storeState();
            }
        },

        'collapse': function (id) {
            this.add('-', id);
        },

        'expand': function (id) {
            this.add('+', id);
        }
    };

    return __CLASS__;
})();

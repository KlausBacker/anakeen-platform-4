/**
 * @author Anakeen
 *          General Public License
 */

var WIFF = WIFF || {};

/**
 * Callback for executePhaseList end
 * @type {undefined}
 */
WIFF.executePhaseList_end_callback = undefined;

/**
 * Run callbacks defined as:
 *
 *     {
 *         scope: myObject,
 *         function: myObject.methodName,
 *         args: [1, 2, 3]
 *     }
 *
 * @param opts
 */
WIFF.runCallback = function (opts) {
	if (typeof opts === 'object') {
		var scope = window;
		var func = function () {
		};
		var args = [];
		if (typeof opts.scope === 'object') {
			scope = opts.scope;
		}
		if (typeof opts.function === 'function') {
			func = opts.function;
		}
		if (typeof opts.args === 'object') {
			args = opts.args;
		}
		return func.apply(scope, args);
	}
};

/**
 * ContextDeleter
 *
 * @param contextName string
 * @param options object
 * @constructor
 */
WIFF.ContextDeleter = function (contextName, options) {
	this.contextName = contextName;
	this.success = function () {
	};
	this.failure = function () {
	};
	if (typeof options === 'object') {
		if (typeof options.success === 'function') {
			this.success = options.success;
		}
		if (typeof options.failure === 'function') {
			this.failure = options.failure;
		}
	}
	this.modules = [];
	this.errMsg = '';
	this.mask = undefined;
	/* Program/instruction counter */
	this.PC = 0;
	this.instructions = [
		[this.deactivateAllRepo],
		[this.getInstalledModules],
		[this.orderInstalledModules],
		[this.preDeleteModules],
		[this.deleteContextElement, ['crontab']],
		[this.deleteContextElement, ['vault']],
		[this.deleteContextElement, ['database']],
		[this.deleteContextElement, ['root']],
		[this.deleteContextElement, ['unregister']]
	];
};

WIFF.ContextDeleter.prototype.showMask = function () {
	if (this.mask !== undefined) {
		this.mask.show();
	}
};

WIFF.ContextDeleter.prototype.hideMask = function () {
	if (this.mask !== undefined) {
		this.mask.hide();
	}
};

WIFF.ContextDeleter.prototype.setMask = function (msg) {
	this.mask = new Ext.LoadMask(Ext.getBody(), {
		msg: msg
	});
};

WIFF.ContextDeleter.prototype.failure = function () {
	if (typeof this.failure === 'function') {
		return this.failure.call(this);
	}
};

WIFF.ContextDeleter.prototype.next = function () {
	this.PC++;
	this.run();
};

WIFF.ContextDeleter.prototype.run = function () {
	if (this.PC >= this.instructions.length) {
		if (typeof this.success === 'function') {
			return this.success.call(this);
		}
		return;
	}
	var stage = this.instructions[this.PC];
	var method = stage[0];
	var argv = [];
	if (typeof stage[1] === 'object') {
		argv = stage[1];
	}
	method.apply(this, argv);
};

WIFF.ContextDeleter.prototype.deactivateAllRepo = function () {
	Ext.Ajax.request({
		scope: this,
		url: 'wiff.php',
		params: {
			context: this.contextName,
			deactivateAllRepo: true
		},
		success: function (response, options) {
			var responseDecode = Ext.util.JSON.decode(response.responseText);
			if (responseDecode.success == false) {
				Ext.Msg.alert('Warning', responseDecode.error.toString());
				this.hideMask();
				return this.failure();
			}
			this.modules = responseDecode.data;
			return this.next();
		},
		failure: function (response, options) {
			if (options.failureType) {
				Ext.Msg.alert('Warning', options.failureType);
			} else {
				Ext.Msg.alert('Warning', 'Unknow Error');
			}
			return this.failure();
		}
	});
};

WIFF.ContextDeleter.prototype.getInstalledModules = function () {
	Ext.Ajax.request({
		scope: this,
		url: 'wiff.php',
		params: {
			context: this.contextName,
			getInstalledModuleListWithUpgrade: true,
			authInfo: Ext.encode(authInfo)
		},
		success: function (response, options) {
			var responseDecode = Ext.util.JSON.decode(response.responseText);
			if (responseDecode.success == false) {
				Ext.Msg.alert('Warning', responseDecode.error.toString());
				this.hideMask();
				return this.failure();
			}
			this.modules = responseDecode.data;
			return this.next();
		},
		failure: function (response, options) {
			if (options.failureType) {
				Ext.Msg.alert('Warning', options.failureType);
			} else {
				Ext.Msg.alert('Warning', 'Unknow Error');
			}
			return this.failure();
		}
	})
};

WIFF.ContextDeleter.prototype.orderInstalledModules = function () {
	if (this.modules.length <= 0) {
		return this.next();
	}
	var moduleNameList = [];
	for (var i = 0; i < this.modules.length; i++) {
		moduleNameList.push(this.modules[i].name);
	}
	Ext.Ajax.request({
		scope: this,
		url: 'wiff.php',
		params: {
			context: this.contextName,
			'modulelist[]': moduleNameList,
			getModuleDependencies: true,
			onlyInstalled: true,
			authInfo: Ext.encode(authInfo)
		},
		success: function (response, options) {
			var responseDecode = Ext.util.JSON.decode(response.responseText);
			if (responseDecode.success == false) {
				Ext.Msg.alert('Warning', responseDecode.error.toString());
				this.hideMask();
				return this.failure();
			}
			this.modules = responseDecode.data.reverse();
			return this.next();
		},
		failure: function (response, options) {
			if (options.failureType) {
				Ext.Msg.alert('Warning', options.failureType);
			} else {
				Ext.Msg.alert('Warning', 'Unknow Error');
			}
			return this.failure();
		}
	});
};

WIFF.ContextDeleter.prototype.preDeleteModules = function () {
	if (this.modules.length <= 0) {
		return this.next();
	}
	/* Define global list of modules to delete for getPhaseList() */
	toDelete = this.modules;
	/* Show the process execution window with the list of modules to delete */
	getGlobalwin(true, toDelete);
	/*
	 * Setup the callback to return execution on this object after
	 * executePhaseList()
	 */
	WIFF.executePhaseList_end_callback = {
		scope: this,
		function: this.next,
		args: []
	};
	/*
	 * Call getPhaseList() with the first module to
	 * delete.
	 */
	getPhaseList(toDelete[0], 'delete');
};

WIFF.ContextDeleter.prototype.deleteContextElement = function (toDelete) {
	this.hideMask();
	this.setMask('Deleting ' + toDelete + '...');
	this.showMask();
	Ext.Ajax.request({
		scope: this,
		url: 'wiff.php',
		timeout: 3600000,
		params: {
			contextToDelete: this.contextName,
			deleteContext: toDelete
		},
		success: function (response, options) {
			var responseDecode = Ext.util.JSON.decode(response.responseText);
			if (responseDecode.success == false) {
				Ext.Msg.alert('Warning', responseDecode.error.toString());
				this.hideMask();
				return this.failure();
			}
			if (responseDecode.error) {
				this.errMsg = this.errMsg + responseDecode.error;
			}
			return this.next();
		},
		failure: function (response, options) {
			this.hideMask();
			if (options.failureType) {
				Ext.Msg.alert('Warning', options.failureType);
			} else {
				Ext.Msg.alert('Warning', 'Unknow Error');
			}
			return this.failure();
		}
	});
};

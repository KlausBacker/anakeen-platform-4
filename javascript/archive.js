/**
 * @author Anakeen
 *          General Public License
 */

archiveStore = {};

/**
 * Update archive list
 */
function updateArchiveList(select) {

    Ext.Ajax.request({
        url: 'wiff.php',
        params: {
            getArchivedContextList: true,
            authInfo: Ext.encode(authInfo)
        },
        success: function (responseObject) {
            updateArchiveList_success(responseObject, select);
        },
        failure: function (responseObject) {
            updateArchiveList_failure(responseObject);
        }
    });
}

/*function reloadArchiveStore() {
 if (archiveStore[currentArchive] && archiveStore[currentArchive].getCount() > 0) {
 archiveStore[currentArchive].load();
 }
 }*/

function updateArchiveList_success(responseObject, select) {

    var response = eval('(' + responseObject.responseText + ')');
    if (response.error) {
        Ext.Msg.alert('Server Error', response.error);
    }
    var data = response.data;
    if (!data) {
        return;
    }
    archiveList = data;

    var panel = Ext.getCmp('archive-list');
    panel.items.each(function (item, index, len) {
        if (item.id != 'archive-list-title') {
            this.remove(item, true);
        }
    }, panel);

    for (var i = 0; i < data.length; i++) {
        var hasError = data[i].error ? true : false;
        panel.add({
            title: data[i].name
                + (data[i].datetime ? ' ('
                + data[i].datetime.substr(0, 10) + ')' : ''),
            iconCls: (!data[i].inProgress)
                ? (hasError ? 'x-icon-archive-error' : 'x-icon-archive')
                : 'x-icon-loading',
            tabTip: data[i].name + ' (' + (data[i].datetime ? data[i].datetime.substr(0, 16) : '') + ') ' + (data[i].description ? data[i].description : ''),
            style: 'padding:10px;',
            layout: 'fit',
            archive: data[i],
            disabled: data[i].inProgress,
            listeners: {
                activate: function (panel) {
                    currentArchive = panel.archive.id;
                    //reloadArchiveStore(); Must only reload when using a url or proxy for data, here the data are stored directly at the panel's creation
                }
            },
            items: [
                {
                    xtype: 'panel',
                    title: data[i].name,
                    archive: data[i],
                    iconCls: (!data[i].inProgress)
                        ? (hasError ? 'x-icon-archive-error' : 'x-icon-archive')
                        : 'x-icon-loading',
                    id: 'archive-' + data[i].id,
                    bodyStyle: 'overflow-y:auto;',
                    items: [
                        {
                            layout: 'anchor',
                            title: 'Archive Information',
                            style: 'padding:10px;font-size:small;',
                            bodyStyle: 'padding:5px;',
                            xtype: 'panel',
                            archive: data[i],
                            // html: contextInfoHtml,
                            tbar: [
                                {
                                    text: 'Create Context',
                                    tooltip: 'Create Context from Archive',
                                    iconCls: 'x-icon-create',
                                    archive: data[i],
                                    disabled: hasError,
                                    handler: function (button) {

                                        var win = new Ext.Window({
                                            title: 'Create Context from Archive',
                                            iconCls: 'x-icon-create',
                                            layout: 'fit',
                                            border: false,
                                            modal: true,
                                            width: 600,
                                            id: 'create-archive-window',
                                            items: [
                                                {
                                                    xtype: 'form',
                                                    id: 'create-archive-form',
                                                    columnWidth: 1,
                                                    bodyStyle: 'padding:10px',
                                                    frame: true,
                                                    autoHeight: true,
                                                    items: [
                                                        {
                                                            xtype: 'textfield',
                                                            fieldLabel: 'Name',
                                                            name: 'name',
                                                            anchor: '-15',
                                                            value: button.archive.name
                                                        },
                                                        {
                                                            xtype: 'textfield',
                                                            fieldLabel: 'Root',
                                                            name: 'root',
                                                            anchor: '-15'
                                                        },
                                                        {
                                                            xtype: 'textarea',
                                                            fieldLabel: 'Description',
                                                            name: 'desc',
                                                            anchor: '-15',
                                                            value: button.archive.description
                                                        },
                                                        {
                                                            xtype: 'textfield',
                                                            fieldLabel: 'Url',
                                                            name: 'url',
                                                            anchor: '-15'
                                                        },
                                                        {
                                                            xtype: 'textfield',
                                                            fieldLabel: 'Core Database Service',
                                                            name: 'core_pgservice',
                                                            anchor: '-15'
                                                        },
                                                        {
                                                            xtype: 'textfield',
                                                            fieldLabel: 'Vault Root',
                                                            name: 'vault_root',
                                                            anchor: '-15'
                                                        },
                                                        {
                                                            xtype: 'checkbox',
                                                            fieldLabel: 'Remove Profiles',
                                                            name: 'remove_profiles',
                                                            listeners: {
                                                                check: function (checkbox, checked) {
                                                                    if (checked == true) {

                                                                        Ext
                                                                            .getCmp('create-archive-form')
                                                                            .getForm()
                                                                            .findField('user_login')
                                                                            .show();
                                                                        Ext
                                                                            .getCmp('create-archive-form')
                                                                            .getForm()
                                                                            .findField('user_login')
                                                                            .enable();
                                                                        Ext
                                                                            .getCmp('create-archive-form')
                                                                            .getForm()
                                                                            .findField('user_password')
                                                                            .show();
                                                                        Ext
                                                                            .getCmp('create-archive-form')
                                                                            .getForm()
                                                                            .findField('user_password')
                                                                            .enable();

                                                                    } else {

                                                                        Ext
                                                                            .getCmp('create-archive-form')
                                                                            .getForm()
                                                                            .findField('user_login')
                                                                            .hide();
                                                                        Ext
                                                                            .getCmp('create-archive-form')
                                                                            .getForm()
                                                                            .findField('user_login')
                                                                            .disable();
                                                                        Ext
                                                                            .getCmp('create-archive-form')
                                                                            .getForm()
                                                                            .findField('user_password')
                                                                            .hide();
                                                                        Ext
                                                                            .getCmp('create-archive-form')
                                                                            .getForm()
                                                                            .findField('user_password')
                                                                            .disable();

                                                                    }
                                                                }
                                                            }
                                                        },
                                                        {
                                                            xtype: 'textfield',
                                                            fieldLabel: 'User Login',
                                                            name: 'user_login',
                                                            anchor: '-15',
                                                            hidden: true
                                                        },
                                                        {
                                                            xtype: 'textfield',
                                                            fieldLabel: 'User Password',
                                                            name: 'user_password',
                                                            anchor: '-15',
                                                            hidden: true
                                                        },
                                                        {
                                                            xtype: 'checkbox',
                                                            fieldLabel: 'Remove extracted temporary files after restore?',
                                                            name: 'clean_tmp_directory',
                                                            checked: true
                                                        }
                                                    ],

                                                    buttons: [
                                                        {
                                                            text: 'Save',
                                                            handler: function () {

                                                                if (!Ext
                                                                    .getCmp('create-archive-form')
                                                                    .getForm()
                                                                    .findField('name')
                                                                    .getValue()) {
                                                                    Ext.Msg
                                                                        .alert(
                                                                            'Dynacase Control',
                                                                            'A name must be provided.');
                                                                    return;
                                                                }

                                                                if (!Ext
                                                                    .getCmp('create-archive-form')
                                                                    .getForm()
                                                                    .findField('root')
                                                                    .getValue()) {
                                                                    Ext.Msg
                                                                        .alert(
                                                                            'Dynacase Control',
                                                                            'A root must be provided.');
                                                                    return;
                                                                }

                                                                if (!Ext
                                                                    .getCmp('create-archive-form')
                                                                    .getForm()
                                                                    .findField('vault_root')
                                                                    .getValue()) {
                                                                    Ext.Msg
                                                                        .alert(
                                                                            'Dynacase Control',
                                                                            'A vault path must be provided.');
                                                                    return;
                                                                }

                                                                if (!Ext
                                                                    .getCmp('create-archive-form')
                                                                    .getForm()
                                                                    .findField('core_pgservice')
                                                                    .getValue()) {
                                                                    Ext.Msg
                                                                        .alert(
                                                                            'Dynacase Control',
                                                                            'A database service must be provided.');
                                                                    return;
                                                                }

                                                                if (Ext
                                                                    .getCmp('create-archive-form')
                                                                    .getForm()
                                                                    .findField('remove_profiles')
                                                                    .getValue()) {
                                                                    if (!Ext
                                                                        .getCmp('create-archive-form')
                                                                        .getForm()
                                                                        .findField('user_login')
                                                                        .getValue()) {
                                                                        Ext.Msg
                                                                            .alert(
                                                                                'Dynacase Control',
                                                                                'If you remove profiles, you must specify a user login.');
                                                                        return;
                                                                    }
                                                                    if (!Ext
                                                                        .getCmp('create-archive-form')
                                                                        .getForm()
                                                                        .findField('user_password')
                                                                        .getValue()) {
                                                                        Ext.Msg
                                                                            .alert(
                                                                                'Dynacase Control',
                                                                                'If you remove profiles, you must specify a user password.');
                                                                        return;
                                                                    }
                                                                }
                                                                /**
                                                                 * @TODO: Add pre-restore
                                                                 */
                                                                do_restore();

                                                                win.hide();
                                                                (function () {
                                                                    updateContextList();
                                                                }).defer(100);

                                                            }
                                                        }
                                                    ]
                                                }
                                            ]
                                        });

                                        win.show();

                                    }
                                },
                                {
                                    text: 'Download',
                                    tooltip: 'Download this archive',
                                    iconCls: 'x-icon-archive-download',
                                    archive: data[i],
                                    handler: function (button) {
                                        button.el.insertHtml(
                                            'beforeBegin',
                                            '<form action="' + button.archive.urlfile + '" target="_blank" method="get" style="display:none"></form>'
                                        ).submit();
                                    }
                                },
                                {
                                    text: 'Delete',
                                    tooltip: 'Delete',
                                    iconCls: 'x-icon-delete-archive',
                                    archive: data[i],
                                    handler: function (button) {
                                        Ext.Msg.show({
                                            title: 'Warning',
                                            msg: "Do you really want to delete archive '" + Ext.util.Format.htmlEncode(button.archive.name) + "'?",
                                            buttons: Ext.Msg.YESNO,
                                            minWidth: 200,
                                            icon: Ext.Msg.WARNING,
                                            fn: function (btn, text, opt) {
                                                if (btn != 'yes') {
                                                    return false;
                                                }

                                                Ext.Ajax.request({
                                                    url: 'wiff.php',
                                                    params: {
                                                        deleteArchive: true,
                                                        archiveId: button.archive.id
                                                    },
                                                    success: function (responseObject) {
                                                        deleteArchive_success(responseObject);
                                                    },
                                                    failure: function (responseObject) {
                                                        deleteArchive_failure(responseObject);
                                                    }
                                                });
                                            }
                                        });
                                    }
                                }
                            ],
                            refresh: function () {

                                var contextInfoHtml = '<ul><li class="x-form-item"><b>Datetime :</b> '
                                    + Ext.util.Format
                                    .htmlEncode(this.archive.datetime)
                                    + '</li><li class="x-form-item"><b>Description :</b> '
                                    + Ext.util.Format
                                    .htmlEncode(this.archive.description)
                                    + '</li><li class="x-form-item"><b>Archive id :</b> '
                                    + Ext.util.Format
                                    .htmlEncode(this.archive.id)
                                    + '</li><li class="x-form-item"><b>Size</b> : '
                                    + Ext.util.Format
                                    .htmlEncode(this.archive.size)
                                    + '</li><li class="x-form-item"><b>Vault saved :</b> '
                                    + Ext.util.Format
                                    .htmlEncode(this.archive.vault)
                                    + '</li>';
                                if (this.archive.error) {
                                    contextInfoHtml += '<li class="x-form-item error"><b>Error :</b> '
                                        + Ext.util.Format
                                        .htmlEncode(this.archive.error)
                                        + '</li>';
                                }
                                this.body.update(contextInfoHtml + "</ul>");

                            },
                            listeners: {
                                render: function (panel) {
                                    panel.refresh();
                                }
                            }

                        },
                        {
                            id: 'archive-' + data[i].id + '-installed',
                            title: 'Installed',
                            columnWidth: .45,
                            layout: 'fit',
                            style: 'padding:10px;padding-top:0px;',
                            archive: data[i],
                            listeners: {
                                afterrender: function (panel) {

                                    currentArchive = panel.archive.id;
                                    archiveStore[currentArchive] = new Ext.data.JsonStore(
                                        {
                                            data: panel.archive.moduleList,
                                            fields: ['name', 'versionrelease',
                                                'availableversionrelease',
                                                'description', 'infopath',
                                                'errorstatus'],
                                            sortInfo: {
                                                field: 'name',
                                                direction: "ASC"
                                            }
                                        });

                                    var selModel = new Ext.grid.RowSelectionModel();

                                    var grid = new Ext.grid.GridPanel({
                                        selModel: selModel,
                                        loadMask: true,
                                        border: false,
                                        store: archiveStore[currentArchive],
                                        stripeRows: true,
                                        columns: [
                                            {
                                                id: 'name',
                                                header: 'Module',
                                                dataIndex: 'name',
                                                width: 140
                                            },
                                            {
                                                id: 'installed-version',
                                                header: 'Installed Version',
                                                dataIndex: 'versionrelease'
                                            },
                                            {
                                                id: 'description',
                                                header: 'Description',
                                                dataIndex: 'description'
                                            }
                                        ],
                                        autoExpandColumn: 'description',
                                        autoHeight: true
                                    });

                                    grid.getView().getRowClass = function (record, index) {
                                        return (record.data.errorstatus
                                            ? 'red-row'
                                            : '');
                                    };

                                    grid.getView().emptyText = 'No installed modules';
                                    panel.add(grid);
                                }
                            }
                        }
                    ]

                }
            ]
        })
    }

    // Selection of context to display
    if (data.length != 0) {
        var archiveArray = Ext.getCmp('archive-list').items.items;
        if (select == 'select-last') {
            Ext.getCmp('archive-list').setActiveTab(Ext
                .getCmp('archive-list').items.last());
        } else if (select) {
            for (i = 0; i < archiveArray.length; i++) {
                if (archiveArray[i].archive && archiveArray[i].archive[select]) {
                    Ext.getCmp('archive-list')
                        .setActiveTab(archiveArray[i]);
                    break;
                }
            }
        } else if (window.currentArchive) {
            for (i = 0; i < archiveArray.length; i++) {
                if (archiveArray[i].archive && archiveArray[i].archive.id == currentArchive) {
                    Ext.getCmp('archive-list')
                        .setActiveTab(archiveArray[i]);
                    break;
                }
            }

        }
    }
}

function get_restore_process_list(module, phase) {
    Ext.Ajax.request({
        url: 'wiff.php',
        params: {
            context: currentContext,
            module: module.name,
            operation: 'restore',
            phase: phase,
            getProcessList: true,
            authInfo: Ext.encode(authInfo)
        },
        success: function (responseObject) {
            var response = eval('(' + responseObject.responseText + ')');
            if (response.error) {
                Ext.Msg.alert('Server Error', response.error);
            } else {
                currentPhaseList = [phase];
                currentPhaseIndex = 0;
                currentModule = module;
                executePhaseList('restore');
            }
        }
    });
}

function restore_success(responseObject) {
    mask.hide();
    var response = eval('(' + responseObject.responseText + ')');
    if (response.error) {
        Ext.Msg.alert('Server Error', response.error);
    } else {
        toRestoreSave = response.data;
        toRestore = toRestoreSave.slice();
        /**
         * Do post-archive
         */
        getGlobalwin(true, toRestore);
        get_restore_process_list(toRestore[0], 'post-restore');

    }
}

function restore_failure(responseObject) {
    /**
     * @TODO ERRO FOR RESTORE
     */
}
function restore(modulelist) {
    mask = new Ext.LoadMask(Ext.getBody(), {
        msg: 'Resolving dependencies...'
    });
    mask.show();

    Ext.Ajax.request({
        url: 'wiff.php',
        params: {
            context: currentContext,
            'modulelist[]': modulelist,
            getModuleDependencies: true,
            onlyInstalled: true,
            authInfo: Ext.encode(authInfo)
        },
        success: function (responseObject) {
            restore_success(responseObject);
        },
        failure: function (responseObject) {
            restore_failure(responseObject);
        }
    });
}

function execute_next_restore_module_phase(module, phase) {
    if (processpanel[module.name]) {
        processpanel[module.name].hide();
    }

    // Set proper icon
    modulepanel.setModuleIcon(module.name, 'x-icon-ok');

    if (toRestore[0]) {
        get_restore_process_list(toRestore[0], phase);
    } else if (phase == "pre-restore") {
        do_restore();
    } else {
        end_restore();
    }
}
function end_restore() {
    globalwin.close();
    Ext.Msg
        .alert(
        'Dynacase Control',
        'Context '
            + currentContext
            + ' successfully created',
        function () {
            (function () {
                updateContextList();
            })
                .defer(100);
        });
}

function do_restore() {
    //globalwin.close();
    mask = new Ext.LoadMask(Ext
        .getBody(), {
        msg: 'Making Context From Archive...'
    });
    mask.show();
    var win = Ext.getCmp('create-archive-window');
    Ext
        .getCmp('create-archive-form')
        .getForm().submit({
            url: 'wiff.php',
            timeout: 3600,
            success: function (form, action) {
                // updateContextList('select-last');
                form.reset();
                var panel = Ext
                    .getCmp('create-archive-form');
                panel
                    .fireEvent(
                        'render',
                        panel);

                win.close();
                win.destroy();
                mask.hide();
                currentContext = action.result.data.name;
                var modules = [];
                archiveStore[currentArchive].each(function (record) {
                    modules.push(record.get('name'));
                });
                restore(modules);
                (function () {
                    updateContextList();
                })
                    .defer(100);
            },
            failure: function (form, action) {
                // updateContextList('select-last');
                mask.hide();

                form.reset();
                var panel = Ext
                    .getCmp('create-archive-form');
                panel
                    .fireEvent(
                        'render',
                        panel);
                win.close();
                win.destroy();
                if (action
                    && action.result) {
                    Ext.Msg
                        .alert(
                        'Failure',
                        action.result.error,
                        function () {
                            (function () {
                                updateContextList();
                            })
                                .defer(100);
                        });
                } else if (action
                    && action.failureType == Ext.form.Action.CONNECT_FAILURE) {
                    Ext.Msg
                        .alert(
                        'Warning',
                        'Timeout reach if context not created yet please reload page later',
                        function () {
                            (function () {
                                updateContextList();
                            })
                                .defer(100);
                        });
                } else {
                    Ext.Msg
                        .alert(
                        'Warning',
                        'Unknow error',
                        function () {
                            (function () {
                                updateContextList();
                            })
                                .defer(100);
                        });
                }
            },
            params: {
                createContextFromArchive: true,
                archiveId: currentArchive
            }// ,
            // waitMsg :
            // 'Creating
            // Context from
            // Archive...'
        });

    (function () {
        updateContextList();
    })
        .defer(1000);
}
function do_archive() {
    globalwin.close();
    mask = new Ext.LoadMask(Ext
        .getBody(), {
        msg: 'Making Archive...'
    });
    mask.show();
    Ext.getCmp('create-archive-form')
        .getForm().submit({
            url: 'wiff.php',
            timeout: 3600,
            success: function (form, action) {
                // win.hide();
                mask.hide();
                (function () {
                    window.currentArchive = action.result.data;
                    updateArchiveList();
                })
                    .defer(100);
                /**
                 * Do post-archive
                 */
                toArchive = toArchiveSave.slice();
                getGlobalwin(true, toArchive);
                get_archive_process_list(toArchive[0], 'post-archive');
            },
            failure: function (form, action) {

                // win.hide();
                mask.hide();
                if (action
                    && action.result) {
                    Ext.Msg
                        .alert(
                        'Failure',
                        action.result.error,
                        function () {
                            (function () {
                                updateArchiveList();
                            })
                                .defer(100);
                        });
                } else if (action
                    && action.failureType == Ext.form.Action.CONNECT_FAILURE) {
                    Ext.Msg
                        .alert(
                        'Warning',
                        'Timeout reach if archive not created yet please reload page later',
                        function () {
                            (function () {
                                updateArchiveList();
                            })
                                .defer(100);
                        });
                } else {
                    Ext.Msg
                        .alert(
                        'Warning',
                        'Unknow error',
                        function () {
                            (function () {
                                updateArchiveList();
                            })
                                .defer(100);
                        });
                }
                // archive_failure(action.response);
            },
            params: {
                archiveContext: true,
                name: currentContext

            }// ,
            // waitMsg : 'Making
            // Archive...'
        });

    (function () {
        updateArchiveList();
    })
        .defer(1000);
}

function execute_next_archive_module_phase(module, phase) {
    if (processpanel[module.name]) {
        processpanel[module.name].hide();
    }

    // Set proper icon
    modulepanel.setModuleIcon(module.name, 'x-icon-ok');

    if (toArchive[0]) {
        get_archive_process_list(toArchive[0], phase);
    } else if (phase == "pre-archive") {
        do_archive();
    } else {
        end_archive();
    }
}

function end_archive() {
    globalwin.close();
    Ext.Msg
        .alert(
        'Dynacase Control',
        'Context successfully archived',
        function () {
            (function () {
                updateArchiveList();
            })
                .defer(100);
        });
}

function get_archive_process_list(module, phase) {
    Ext.Ajax.request({
        url: 'wiff.php',
        params: {
            context: currentContext,
            module: module.name,
            operation: 'archive',
            phase: phase,
            getProcessList: true,
            authInfo: Ext.encode(authInfo)
        },
        success: function (responseObject) {
            var response = eval('(' + responseObject.responseText + ')');
            if (response.error) {
                Ext.Msg.alert('Server Error', response.error);
            } else {
                currentPhaseList = [phase];
                currentPhaseIndex = 0;
                currentModule = module;
                executePhaseList('archive');
            }
        }
    });
}

function archive_success(responseObject) {
    mask.hide();
    var response = eval('(' + responseObject.responseText + ')');
    if (response.error) {
        Ext.Msg.alert('Server Error', response.error);
    } else {
        toArchiveSave = response.data;
        toArchive = toArchiveSave.slice();
        /**
         * Do pre-archive
         */
        getGlobalwin(true, toArchive);
        get_archive_process_list(toArchive[0], 'pre-archive');

    }
}

function archive_failure(responseObject) {
    updateArchiveList();
    // console.log('Archive Failure');
}

function deleteArchive_success(responseObject) {

    var response = eval('(' + responseObject.responseText + ')');
    if (response.error) {
        Ext.Msg.alert('Server Error', response.error);
    } else {
        Ext.Msg.alert('Dynacase Control', 'Archive deleted.', function () {
            updateArchiveList('select-last');
        });
    }

}

function deleteArchive_failure(responseObject) {
    // console.log('Archive Failure');
}

function updateArchiveList_failure(responseObject) {
    Ext.Msg.alert('Error', 'Could not retrieve archive list');
}
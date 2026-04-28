/*
 * OpenDXP Element Manager.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright 2026 instride AG (https://instride.ch)
 * @license   https://github.com/instride-ch/opendxp-element-manager/blob/main/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

opendxp.registerNS('opendxp.bundle.elementmanager.duplicationIndex.item');

opendxp.bundle.elementmanager.duplicationIndex.item = Class.create({
    iconCls: 'opendxp_element_manager_duplication_icon_indexes',

    initialize: function (data) {
        this.data = data;
    },

    getPanel: function () {
        return new Ext.TabPanel({
            activeTab: 0,
            title: this.data.className,
            closable: true,
            deferredRender: false,
            forceLayout: true,
            iconCls: this.iconCls,
            items: [
                this.buildSubPanel(t('opendxp_element_manager_duplicates_current'), false),
                this.buildSubPanel(t('opendxp_element_manager_duplicates_declined'), true),
            ],
        });
    },

    buildSubPanel: function (title, declined) {
        return new Ext.panel.Panel({
            title: title,
            layout: 'border',
            items: [this.createGrid(this.createStore(declined))],
        });
    },

    createStore: function (declined) {
        const listFields = (this.data.listFields || []).map(function (field) {
            return Ext.isArray(field) ? field.join(',') : field;
        });

        return Ext.create('Ext.data.Store', {
            fields: listFields.concat(['objectId', 'extId', 'duplicationId', 'declined', 'objectIdOther', '_isFirstColumn']),
            groupField: 'duplicationId',
            autoLoad: true,
            proxy: {
                type: 'ajax',
                url: '/admin/opendxp-element-manager/potential-duplicate/get-potential-duplicates',
                extraParams: {
                    className: this.data.className,
                    declined: declined,
                },
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    totalProperty: 'total',
                    idProperty: 'extId',
                },
            },
        });
    },

    createGrid: function (store) {
        const columns = [{
            text: t('id'),
            dataIndex: 'objectId',
            width: 80,
        }];

        const listFields = (this.data.listFields || []).map(function (field) {
            return Ext.isArray(field) ? field.join(',') : field;
        });

        Ext.each(listFields, function (field) {
            columns.push({
                text: field,
                dataIndex: field,
                flex: 1,
            });
        });

        columns.push(this.buildDeclineColumn());

        if (this.data.options && this.data.options.merge_supported) {
            columns.push(this.buildMergeColumn());
        }

        return Ext.create('Ext.grid.Panel', {
            store: store,
            region: 'center',
            columns: columns,
            bbar: opendxp.helpers.grid.buildDefaultPagingToolbar(store),
            features: [{ ftype: 'grouping', collapsible: false }],
        });
    },

    buildDeclineColumn: function () {
        return {
            xtype: 'gridcolumn',
            dataIndex: '_isFirstColumn',
            width: 50,
            align: 'right',
            renderer: function (value, metadata, record, store) {
                if (!value) {
                    return '';
                }
                const id = Ext.id();
                Ext.defer(function () {
                    if (Ext.get(id)) {
                        new Ext.button.Button({
                            renderTo: id,
                            iconCls: 'opendxp_icon_delete',
                            scale: 'small',
                            handler: function () {
                                const url = record.get('declined')
                                    ? '/admin/opendxp-element-manager/potential-duplicate/undecline'
                                    : '/admin/opendxp-element-manager/potential-duplicate/decline';
                                Ext.Ajax.request({
                                    url: url,
                                    method: 'post',
                                    params: { id: record.get('duplicationId') },
                                    success: function () {
                                        store.load();
                                    },
                                });
                            },
                        });
                    }
                }, 200);
                return Ext.String.format('<div id="{0}"></div>', id);
            },
        };
    },

    buildMergeColumn: function () {
        return {
            xtype: 'gridcolumn',
            dataIndex: '_isFirstColumn',
            width: 50,
            align: 'right',
            renderer: function (value, metadata, record) {
                if (!value) {
                    return '';
                }
                const id = Ext.id();
                Ext.defer(function () {
                    if (Ext.get(id) && typeof opendxp.plugin !== 'undefined' && opendxp.plugin.objectmerger) {
                        new Ext.button.Button({
                            renderTo: id,
                            iconCls: 'opendxp_icon_merge',
                            scale: 'small',
                            handler: function () {
                                new opendxp.plugin.objectmerger.panel(record.get('objectId'), record.get('objectIdOther'));
                            },
                        });
                    }
                }, 200);
                return Ext.String.format('<div id="{0}"></div>', id);
            },
        };
    },
});

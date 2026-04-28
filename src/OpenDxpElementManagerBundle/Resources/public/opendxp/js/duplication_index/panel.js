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

opendxp.registerNS('opendxp.bundle.elementmanager.duplicationIndex.panel');

opendxp.bundle.elementmanager.duplicationIndex.panel = Class.create({
    panelId: 'opendxp_element_manager_duplication_indexes_panel',
    iconCls: 'opendxp_element_manager_duplication_icon_indexes',
    openItems: {},

    activate: function () {
        const tabPanel = Ext.getCmp('opendxp_panel_tabs');
        if (tabPanel && this.panel) {
            tabPanel.setActiveTab(this.panel);
        }
    },

    getLayout: function () {
        if (this.panel) {
            return this.panel;
        }

        this.tree = Ext.create('Ext.tree.Panel', {
            region: 'west',
            width: 240,
            title: t('opendxp_element_manager_duplication_indexes'),
            iconCls: this.iconCls,
            rootVisible: false,
            split: true,
            autoScroll: true,
            store: Ext.create('Ext.data.TreeStore', {
                root: {
                    text: 'root',
                    expanded: true,
                    children: [],
                },
            }),
            listeners: {
                itemclick: this.onTreeNodeClick.bind(this),
            },
        });

        this.center = Ext.create('Ext.tab.Panel', {
            region: 'center',
            items: [],
        });

        this.panel = Ext.create('Ext.panel.Panel', {
            id: this.panelId,
            title: t('opendxp_element_manager_duplication_indexes'),
            iconCls: this.iconCls,
            closable: true,
            layout: 'border',
            items: [this.tree, this.center],
        });

        this.loadMetadata();

        return this.panel;
    },

    loadMetadata: function () {
        Ext.Ajax.request({
            url: '/admin/opendxp-element-manager/potential-duplicate/list',
            success: function (response) {
                const data = Ext.decode(response.responseText) || [];
                const children = data.map(function (meta) {
                    return {
                        text: meta.className,
                        className: meta.className,
                        leaf: true,
                        iconCls: 'opendxp_icon_class',
                    };
                });

                this.tree.getRootNode().removeAll();
                this.tree.getRootNode().appendChild(children);
            }.bind(this),
        });
    },

    onTreeNodeClick: function (view, record) {
        const className = record.get('className');
        if (!className) {
            return;
        }

        if (this.openItems[className]) {
            this.center.setActiveTab(this.openItems[className]);
            return;
        }

        Ext.Ajax.request({
            url: '/admin/opendxp-element-manager/potential-duplicate/get',
            params: { className: className },
            success: function (response) {
                const res = Ext.decode(response.responseText);
                if (!res || !res.success) {
                    Ext.Msg.alert(t('error'), t('problem_opening_new_target'));
                    return;
                }

                const itemData = res.data;
                itemData.options = res.options || {};
                itemData.className = className;

                const item = new opendxp.bundle.elementmanager.duplicationIndex.item(itemData);
                const tab = item.getPanel();
                this.openItems[className] = tab;
                this.center.add(tab);
                this.center.setActiveTab(tab);

                tab.on('destroy', function () {
                    delete this.openItems[className];
                }.bind(this));
            }.bind(this),
        });
    },
});

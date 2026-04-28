/**
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

opendxp.registerNS('opendxp.bundle.elementmanager.startup');

opendxp.bundle.elementmanager.startup = Class.create({
    initialize: function () {
        document.addEventListener(opendxp.events.preMenuBuild, this.preMenuBuild.bind(this));
    },

    preMenuBuild: function (e) {
        const menu = e.detail.menu;

        if (!menu.settings) {
            return;
        }

        menu.settings.items.push({
            text: t('opendxp_element_manager_duplication_indexes'),
            iconCls: 'opendxp_element_manager_duplication_nav_icon_indexes',
            priority: 50,
            itemId: 'opendxp_element_manager_duplication_indexes_menu',
            handler: this.openDuplicationIndexes.bind(this),
        });
    },

    openDuplicationIndexes: function () {
        const key = 'opendxp_element_manager_duplication_indexes_panel';
        let instance;

        try {
            instance = opendxp.globalmanager.get(key);
            instance.activate();
        } catch (e) {
            instance = new opendxp.bundle.elementmanager.duplicationIndex.panel();
            opendxp.globalmanager.add(key, instance);

            const layout = instance.getLayout();
            const tabPanel = Ext.getCmp('opendxp_panel_tabs');
            if (tabPanel) {
                tabPanel.add(layout);
                tabPanel.setActiveTab(layout);

                layout.on('destroy', function () {
                    opendxp.globalmanager.remove(key);
                });
            }
        }
    },
});

const opendxpBundleElementManager = new opendxp.bundle.elementmanager.startup();

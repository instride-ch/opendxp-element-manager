/**
 * OpenDxp Element Manager.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright 2024 instride AG (https://instride.ch)
 * @license   https://github.com/instride-ch/opendxp-element-manager/blob/main/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

class OpenDxpElementManager {
    init() {
        const user = opendxp.globalmanager.get('user');

        if (user.isAllowed('plugins')) {
            const duplicationsMenu = new Ext.Action({
                id: 'opendxp_element_manager_duplication_indexes',
                text: t('opendxp_element_manager_duplication_indexes'),
                iconCls: 'opendxp_element_manager_duplication_nav_icon_indexes',
                handler: this.openDuplicationIndexes.bind(this),
            });

            if (layoutToolbar.settingsMenu) {
                layoutToolbar.settingsMenu.add(duplicationsMenu);
            }

            coreshop.global.addStore('opendxp_element_manager_duplication_indexes', 'opendxp_element_manager/potential_duplicates');
        }
    }

    openDuplicationIndexes() {
        try {
            opendxp.globalmanager.get('opendxp_element_manager_duplication_indexes_panel').activate();
        } catch (e) {
            opendxp.globalmanager.add('opendxp_element_manager_duplication_indexes_panel', new opendxp_element_manager.duplication_index.panel());
        }
    }
}

const opendxpElementManagerHandler = new OpenDxpElementManager();

document.addEventListener(opendxp.events.opendxpReady, opendxpElementManagerHandler.init.bind(opendxpElementManagerHandler));

<?php

namespace rp\system\event\listener;

use rp\data\classification\ClassificationCache;
use rp\data\game\GameCache;
use rp\data\role\RoleCache;
use rp\system\cache\runtime\CharacterProfileRuntimeCache;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\WCF;

/**
 * Displays information about the character on the about page.
 * 
 * @author  Marco Daries
 * @license Raidplaner License <https://daries.dev/licence/raidplaner.txt>
 */
class SWTORAboutCharacterProfileMenuContentListener implements IParameterizedEventListener
{

    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters): void
    {
        if (GameCache::getInstance()->getCurrentGame()->identifier !== 'swtor') return;

        $characterID = isset($_REQUEST['id']) ? \intval($_REQUEST['id']) : 0;
        if ($characterID) {
            $character = CharacterProfileRuntimeCache::getInstance()->getObject($characterID);
            $fightStyles = [];
            foreach ($character->fightStyles as $key => $fightStyle) {
                if (!$fightStyle['fightStyleEnable']) continue;

                $fightStyles[$key] = [
                    'classification' => ClassificationCache::getInstance()->getClassificationByID($fightStyle['classificationID'])?->getTitle() ?? '',
                    'role' => RoleCache::getInstance()->getRoleByID($fightStyle['roleID'])?->getTitle() ?? '',
                    'itemLevel' => $fightStyle['itemLevel'],
                    'implants' => $fightStyle['implants'],
                    'upgradeBlue' => $fightStyle['upgradeBlue'],
                    'upgradePurple' => $fightStyle['upgradePurple'],
                    'upgradeGold' => $fightStyle['upgradeGold'],
                ];
            }

            WCF::getTPL()->assign([
                'character' => $character,
                'fightStyles' => $fightStyles,
            ]);
        }
    }
}

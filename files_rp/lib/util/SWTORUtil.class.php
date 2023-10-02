<?php

namespace rp\util;

use rp\data\character\Character;
use rp\data\classification\ClassificationCache;
use rp\data\role\RoleCache;

/**
 * Contains swtor-related functions.
 * 
 * @author  Marco Daries
 * @license Raidplaner License <https://daries.dev/licence/raidplaner.txt>
 */
final class SWTORUtil
{

    /**
     * Returns an array with information about character specific fight style.
     */
    public static function getClassArrayList(Character $character): array
    {
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

        return $fightStyles;
    }
}

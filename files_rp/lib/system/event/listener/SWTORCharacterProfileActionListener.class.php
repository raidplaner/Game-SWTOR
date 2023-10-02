<?php

namespace rp\system\event\listener;

use rp\data\character\CharacterProfileAction;
use rp\data\game\GameCache;
use rp\system\cache\runtime\CharacterProfileRuntimeCache;
use rp\util\SWTORUtil;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\WCF;

/**
 * Extended character profile action's
 * 
 * @author  Marco Daries
 * @license Raidplaner License <https://daries.dev/licence/raidplaner.txt>
 */
class SWTORCharacterProfileActionListener implements IParameterizedEventListener
{

    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters): void
    {
        if (GameCache::getInstance()->getCurrentGame()->identifier !== 'swtor') return;
        if (!($eventObj instanceof CharacterProfileAction)) return;

        switch ($eventObj->getActionName()) {
            case 'getPopover':
                $this->getPopover($eventObj);
                break;
        }
    }

    protected function getPopover(CharacterProfileAction $eventObj): void
    {
        $characterID = $eventObj->getObjectIDs()[0] ?? 0;
        if (!$characterID) return;

        $characterProfile = CharacterProfileRuntimeCache::getInstance()->getObject($characterID);
        if ($characterProfile === null) return;

        WCF::getTPL()->assign('popupFightStyles', SWTORUtil::getClassArrayList($characterProfile->getDecoratedObject()));
    }
}

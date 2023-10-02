<?php

namespace rp\system\event\listener;

use rp\data\character\CharacterList;
use rp\data\character\CharacterProfile;
use rp\data\classification\ClassificationCache;
use rp\data\event\raid\attendee\EventRaidAttendeeAction;
use rp\data\game\GameCache;
use rp\data\role\RoleCache;
use rp\system\cache\runtime\CharacterRuntimeCache;
use rp\system\cache\runtime\EventRaidAttendeeRuntimeCache;
use rp\util\SWTORUtil;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\WCF;
use rp\data\character\Character;

/**
 * Extended raid event attendee action's
 * 
 * @author  Marco Daries
 * @license Raidplaner License <https://daries.dev/licence/raidplaner.txt>
 */
class SWTOREventRaidAttendeeActionListener implements IParameterizedEventListener
{

    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters): void
    {
        if (GameCache::getInstance()->getCurrentGame()->identifier !== 'swtor') return;
        if (!($eventObj instanceof EventRaidAttendeeAction)) return;

        switch ($eventName) {
            case 'initializeAction':
                if ($eventObj->getActionName() === 'getPopover') {
                    $this->getPopover($eventObj);
                }
                break;
            case 'getLeaderAddDialogCharacters':
                $this->getLeaderAddDialogCharacters($parameters);
                break;
            case 'submitAddDialog':
            case 'submitLeaderAddDialogCharacter':
                $this->submitAddDialog($parameters);
                break;
        }
    }

    protected function getPopover(EventRaidAttendeeAction $eventObj): void
    {
        $attendeeID = $eventObj->getObjectIDs()[0] ?? 0;
        if (!$attendeeID) return;

        $attendee = EventRaidAttendeeRuntimeCache::getInstance()->getObject($attendeeID);
        if ($attendee === null) return;

        /** @var Character $character */
        $character = CharacterRuntimeCache::getInstance()->getObject($attendee->characterID);
        if ($character === null) return;

        WCF::getTPL()->assign('popupFightStyles', SWTORUtil::getClassArrayList($character));
    }

    protected function getLeaderAddDialogCharacters(array &$parameters): void
    {
        $charactersFormField = $parameters['charactersFormField'];
        $actionParameters = $parameters['parameters'];
        $parameters['fieldChanged'] = true;

        $characterList = new CharacterList();
        $characterList->getConditionBuilder()->add('member.gameID = ?', [RP_DEFAULT_GAME_ID]);
        $characterList->getConditionBuilder()->add('member.isDisabled = ?', [0]);
        if (!empty($actionParameters['characterIDs'])) {
            $characterList->getConditionBuilder()->add('member.characterID NOT IN (?)', [$actionParameters['characterIDs']]);
        }
        $characterList->readObjects();

        $options = [];
        /** @var CharacterProfile $character */
        foreach ($characterList->getObjects() as $characterID => $character) {
            $fightStyles = $character->fightStyles;
            foreach ($fightStyles as $fightStyleID => $fightStyle) {
                if (!$fightStyle['fightStyleEnable']) continue;

                $id = $character->characterID . '_' . $fightStyleID;

                $label = '';
                $classification = ClassificationCache::getInstance()->getClassificationByID($fightStyle['classificationID']);
                if ($classification) {
                    $label = $classification->getTitle();
                }

                $role = RoleCache::getInstance()->getRoleByID($fightStyle['roleID']);
                if ($role) {
                    if (!empty($label)) $label .= ', ';
                    $label .= $role->getTitle();
                }

                $options[] = [
                    'depth' => 0,
                    'label' => $character->getTitle() . ' (' . $label . ')',
                    'userID' => $character->userID,
                    'value' => $id,
                ];
            }
        }

        $charactersFormField->options($options, true);
    }

    protected function submitAddDialog(array &$parameters): void
    {
        [$characterID, $fightStyleID] = \explode('_', $parameters['characterID'], 2);

        $character = CharacterRuntimeCache::getInstance()->getObject($characterID);
        $fightStyle = $character->fightStyles[$fightStyleID];

        $parameters['saveData'] = [
            'characterID' => $character->characterID,
            'characterName' => $character->characterName,
            'classificationID' => $fightStyle['classificationID'],
            'internID' => $parameters['characterID'],
            'roleID' => $fightStyle['roleID'],
        ];
        $parameters['characterID'] = null;
    }
}

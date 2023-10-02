<?php

namespace rp\system\event\listener;

use rp\data\character\CharacterList;
use rp\data\character\CharacterProfile;
use rp\data\classification\ClassificationCache;
use rp\data\game\GameCache;
use rp\data\role\RoleCache;
use rp\form\RaidAddForm;
use wcf\system\event\listener\IParameterizedEventListener;

/**
 * Extended raid add form
 * 
 * @author  Marco Daries
 * @license Raidplaner License <https://daries.dev/licence/raidplaner.txt>
 */
class SWTORRaidAddFormListener implements IParameterizedEventListener
{

    protected function attendeesCreateForm(array &$parameters): void
    {
        $charactersFormField = $parameters['charactersFormField'];
        $parameters['fieldChanged'] = true;

        $characterList = new CharacterList();
        $characterList->getConditionBuilder()->add('member.gameID = ?', [RP_DEFAULT_GAME_ID]);
        $characterList->getConditionBuilder()->add('member.isDisabled = ?', [0]);
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

    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters): void
    {
        if (GameCache::getInstance()->getCurrentGame()->identifier !== 'swtor') return;
        if (!($eventObj instanceof RaidAddForm)) return;

        switch ($eventName) {
            case 'attendeesCreateForm':
                $this->attendeesCreateForm($parameters);
                break;
        }
    }
}

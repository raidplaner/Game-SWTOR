<?php

namespace rp\system\event\listener;

use rp\data\character\CharacterProfile;
use rp\data\classification\ClassificationCache;
use rp\data\event\Event;
use rp\data\game\GameCache;
use rp\data\role\RoleCache;
use rp\system\event\RaidEventController;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\util\StringUtil;

/**
 * Extended raid event controller
 * 
 * @author  Marco Daries
 * @license Raidplaner License <https://daries.dev/licence/raidplaner.txt>
 */
class SWTORRaidEventControllerListener implements IParameterizedEventListener
{

    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters): void
    {
        if (GameCache::getInstance()->getCurrentGame()->identifier !== 'swtor') return;
        if (!($eventObj instanceof RaidEventController)) return;

        switch ($eventName) {
            case 'availableCharacters':
                $this->availableCharacters($eventObj, $parameters);
                break;
            case 'beforeSetFormObjectData':
                $parameters = \array_merge(
                    [
                        'requiredLevel',
                        'requiredItemLevel',
                        'requiredImplants',
                        'requiredUpgradeBlue',
                        'requiredUpgradePurple',
                        'requiredUpgradeGold',
                    ],
                    $parameters,
                );
                break;
            case 'createForm':
                $this->createForm($parameters);
                break;
        }
    }

    protected function availableCharacters(RaidEventController $eventObj, array &$parameters): void
    {
        $parameters['availableCharacters'] = [];

        /** @var Event $event */
        $event = $eventObj->getEvent();

        /** @var CharacterProfile $character */
        foreach ($parameters['characters'] as $characterID => $character) {
            if ($event->requiredLevel !== 0 && $character->level < $event->requiredLevel) continue;

            $fightStyles = $character->fightStyles;
            foreach ($fightStyles as $fightStyleID => $fightStyle) {
                if (!$fightStyle['fightStyleEnable']) continue;

                foreach (['requiredItemLevel', 'requiredImplants'] as $required) {
                    $name = StringUtil::firstCharToLowerCase(\str_replace('required', '', $required));

                    if ($event->{$required} == 0) continue;
                    if ($fightStyle[$name] < $event->{$required}) {
                        continue 2;
                    }
                }

                $highUpgradeCount = 0;
                $notAccess = false;
                foreach (['requiredUpgradeGold', 'requiredUpgradePurple', 'requiredUpgradeBlue'] as $required) {
                    $name = StringUtil::firstCharToLowerCase(\str_replace('required', '', $required));

                    if ($event->{$required} !== 0) {
                        if ($fightStyle[$name] < $event->{$required}) {
                            $max = $highUpgradeCount + $fightStyle[$name];
                            if ($max < 14) $notAccess = true;
                            break;
                        }
                    }

                    $highUpgradeCount += $fightStyle[$name];
                }

                if (!$notAccess) {
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

                    $cloneCharacter = clone $character;
                    $cloneCharacter->characterName = $character->getTitle() . ' (' . $label . ')';
                    $cloneCharacter->classificationID = $fightStyle['classificationID'];
                    $cloneCharacter->roleID = $fightStyle['roleID'];
                    $parameters['availableCharacters'][$id] = $cloneCharacter;
                }
            }
        }
    }

    protected function createForm(array &$parameters): void
    {
        $form = $parameters['form'];

        /** @var FormContainer $conditionContainer */
        $conditionContainer = $form->getNodeById('condition');
        $conditionContainer->appendChildren([
                IntegerFormField::create('requiredLevel')
                ->label('rp.character.swtor.level')
                ->minimum(0)
                ->maximum(80)
                ->value(0),
                IntegerFormField::create('requiredItemLevel')
                ->label('rp.character.swtor.itemLevel')
                ->minimum(0)
                ->maximum(340)
                ->value(0),
                SingleSelectionFormField::create('requiredImplants')
                ->label('rp.character.swtor.implants')
                ->options(function () {
                    return [
                    '0' => 'rp.character.swtor.implants.0',
                    '1' => 'rp.character.swtor.implants.1',
                    '2' => 'rp.character.swtor.implants.2'
                    ];
                }),
                IntegerFormField::create('requiredUpgradeBlue')
                ->label('rp.character.swtor.upgradeBlue')
                ->minimum(0)
                ->maximum(14)
                ->value(0),
                IntegerFormField::create('requiredUpgradePurple')
                ->label('rp.character.swtor.upgradePurple')
                ->minimum(0)
                ->maximum(14)
                ->value(0),
                IntegerFormField::create('requiredUpgradeGold')
                ->label('rp.character.swtor.upgradeGold')
                ->minimum(0)
                ->maximum(14)
                ->value(0)
        ]);
    }
}

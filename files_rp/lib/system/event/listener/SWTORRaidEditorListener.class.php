<?php

namespace rp\system\event\listener;

use rp\data\event\raid\attendee\EventRaidAttendee;
use rp\data\game\GameCache;
use rp\data\raid\RaidEditor;
use rp\system\cache\runtime\CharacterRuntimeCache;
use wcf\system\event\listener\IParameterizedEventListener;

/**
 * Extended raid editor
 * 
 * @author  Marco Daries
 * @license Raidplaner License <https://daries.dev/licence/raidplaner.txt>
 */
class SWTORRaidEditorListener implements IParameterizedEventListener
{

    protected function addAttendees(array &$parameters): void
    {
        $attendeeIDs = $parameters['attendeeIDs'];

        foreach ($attendeeIDs as $attendeeID) {
            [$characterID, $fightStyleID] = \explode('_', $attendeeID, 2);

            $character = CharacterRuntimeCache::getInstance()->getObject($characterID);
            if ($character === null) continue;

            $fightStyles = $character->fightStyles;
            if (!isset($fightStyles[$fightStyleID])) continue;

            $fightStyle = $fightStyles[$fightStyleID];

            $parameters['attendees'][] = new EventRaidAttendee(null, [
                'characterID' => $character->characterID,
                'characterName' => $character->characterName,
                'classificationID' => $fightStyle['classificationID'],
                'roleID' => $fightStyle['roleID'],
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters): void
    {
        if (GameCache::getInstance()->getCurrentGame()->identifier !== 'swtor') return;
        if (!($eventObj instanceof RaidEditor)) return;

        switch ($eventName) {
            case 'addAttendees':
                $this->addAttendees($parameters);
                break;
        }
    }
}

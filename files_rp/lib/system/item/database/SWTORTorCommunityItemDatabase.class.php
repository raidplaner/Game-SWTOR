<?php

namespace rp\system\item\database;

use wcf\data\language\Language;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\HTTPRequest;
use wcf\util\StringUtil;

/**
 * SWTOR:Tor Community implementation for item databases.
 * 
 * @author  Marco Daries
 * @license Raidplaner License <https://daries.dev/licence/raidplaner.txt>
 */
class SWTORTorCommunityItemDatabase implements IItemDatabase
{

    public function getItemData(string|int $itemID, ?Language $language = null, string $type = 'items'): ?array
    {
        if (empty($itemID) || !$itemID) return null;
        $item['id'] = $itemID;

        try {
            $url = 'https://torcommunity.com/db/tooltips/html/' . $itemID . '.torctip';

            $request = new HTTPRequest($url);
            $request->execute();
            $reply = $request->getReply();
            $content = $reply['body'];
        } catch (SystemException $e) {
            @\header('HTTP/1.1 500 Internal Server Error');
            throw new SystemException('connection to torcommunity.com failed: ' . $e->getMessage());
        }

        \preg_match("/(.*)<div class=\"torctip_image (.*)\"><img src=\"(.*)icons\/(.*)\./U", $content, $outputArray);

        if ($language->languageCode == 'de') $link = 'https://torcommunity.com/de/database/item/' . $itemID . '/';
        else $link = 'https://torcommunity.com/database/item/' . $itemID . '/';

        $content = \str_replace("https://torcommunity.com/db/icons/" . $outputArray[4] . ".jpg", '{ITEM_ICON}', $content);

        $item = \array_merge($item, [
            'color' => \str_replace("_image", "", $outputArray[2]),
            'icon' => $outputArray[4],
            'iconExtension' => 'jpg',
            'iconURL' => 'https://torcommunity.com/db/icons/',
            'link' => $link,
            'template' => WCF::getTPL()->fetch('itemSWTORTorCommunity', 'rp', ['content' => $content])
        ]);

        return $item;
    }

    public function searchItemID(string $itemName, ?Language $language = null): array
    {
        $items = [];
        $content = '';

        $name = StringUtil::trim($itemName);
        $name = \rawurlencode($name);
        try {
            if ($language->languageCode == 'de') $url = 'https://torcommunity.com/de/database/search/item?name=' . $name;
            else $url = 'https://torcommunity.com/database/search/item?name=' . $name;

            $request = new HTTPRequest($url);
            $request->execute();
            $reply = $request->getReply();
            $content = $reply['body'];
        } catch (SystemException $e) {
            @\header('HTTP/1.1 500 Internal Server Error');
            throw new SystemException('connection to torcommunity.com failed: ' . $e->getMessage());
        }

        if (!empty($content)) {
            $intMatches = \preg_match_all('/(<table[^>]*>(?:.|\n)*?<\/table>)/', $content, $matches);

            if ($intMatches) {
                foreach ($matches[0] as $key => $match) {
                    \preg_match("/<div class='torctip_name'(.*)>(.*)<\//", $match, $itemNames);
                    if (isset($itemNames[2]) && \strcasecmp($itemName, $itemNames[2]) == 0) {
                        \preg_match("/(.*)\/item\/(.*)\//U", $match, $itemIDMatches);
                        if (!empty($itemIDMatches)) {
                            $itemID = $itemIDMatches[2];
                            return [
                                $itemID,
                                'items'
                            ];
                        }
                    }
                }
            }
        }

        return [0, 'items'];
    }
}

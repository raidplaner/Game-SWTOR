<?php

namespace rp\system\server\status;

use Psr\Http\Message\ResponseInterface;
use rp\system\cache\builder\ServerStatusCacheBuilder;
use wcf\system\WCF;

/**
 *  Project:    Raidplaner: Game: Star Wars: The Old Republic
 *  Package:    info.daries.rp.discord
 *  Link:       http://daries.info
 *
 *  Copyright (C) 2018-2022 Daries.info Developer Team
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Reads the server status for the game 'SWTOR'
 * 
 * @author      Marco Daries
 * @package     Daries\RP\System\Server\Status
 */
class SWTORServerStatus extends AbstractServerStatus
{
    /**
     * swtor server url
     */
    protected string $serverURL = "https://www.swtor.com/server-status";

    /**
     * Returns the content of the SWTOR server status.
     */
    public function getContent(): string
    {
        /** @var ResponseInterface $response */
        $content = (string)ServerStatusCacheBuilder::getInstance()->getData(['url' => $this->serverURL], 'content');

        $regExp = '/data-status="(?<status>UP|DOWN)" data-name="(?P<name>[^"]+)"/';
        \preg_match_all($regExp, $content, $servers, PREG_SET_ORDER);

        foreach ($servers as $server) {
            $serverName = \str_replace(" ", "", $server['name']);
            $serverName = \mb_strtolower($serverName);
            if ($serverName !== $this->getServer()->identifier) continue;

            switch (\mb_strtolower($server['status'])) {
                case 'up':
                    $status = 'online';
                    break;
                case 'down':
                    $status = 'offline';
                    break;
                case 'booting':
                    $status = 'booting';
                    break;
                default:
                    $status = 'searching';
                    break;
            }

            return WCF::getTPL()->fetch('swtorServerStatus', 'rp', [
                    'server' => $this->getServer(),
                    'statusImage' => $this->getServer()->getImagePath() . $status . '.png'
            ]);
        }

        return '';
    }
}

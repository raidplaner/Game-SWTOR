<?php

namespace rp\system\server\status;

use Psr\Http\Message\ResponseInterface;
use rp\system\cache\builder\ServerStatusCacheBuilder;
use wcf\system\WCF;

/**
 * Reads the server status for the game 'SWTOR'
 * 
 * @author  Marco Daries
 * @license Raidplaner License <https://daries.dev/licence/raidplaner.txt>
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

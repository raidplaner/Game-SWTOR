{if $event->getController()->getObjectTypeName() == 'info.daries.rp.event.raid'}
    {if $event->requiredLevel}
        <dt>{lang}rp.character.level{/lang}</dt>
        <dd>{@$event->requiredLevel}</dd>
    {/if}
    {if $event->requiredItemLevel}
        <dt>{lang}rp.character.swtor.itemLevel{/lang}</dt>
        <dd>{@$event->requiredItemLevel}</dd>
    {/if}
    {if $event->requiredImplants}
        <dt>{lang}rp.character.swtor.implants{/lang}</dt>
        <dd>{lang}rp.character.swtor.implants.{@$event->requiredImplants}{/lang}</dd>
    {/if}
    {if $event->requiredUpgradeBlue}
        <dt>{lang}rp.character.swtor.upgradeBlue{/lang}</dt>
        <dd>{@$event->requiredUpgradeBlue}</dd>
    {/if}
    {if $event->requiredUpgradePurple}
        <dt>{lang}rp.character.swtor.upgradePurple{/lang}</dt>
        <dd>{@$event->requiredUpgradePurple}</dd>
    {/if}
    {if $event->requiredUpgradeGold}
        <dt>{lang}rp.character.swtor.upgradeGold{/lang}</dt>
        <dd>{@$event->requiredUpgradeGold}</dd>
    {/if}
{/if}
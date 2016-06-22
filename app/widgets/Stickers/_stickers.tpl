<section class="scroll">
    <ul class="list flex active">
        {loop="$stickers"}
            {if="strlen($value) == 44"}
            <li class="block" onclick="Stickers_ajaxSend('{$jid}', '{$value}'); Drawer.clear();">
                <img class="sticker" src="{$path}{$value}"/>
            </li>
            {/if}
        {/loop}
        <li class="block large">
            <span class="primary icon gray">
                <i class="zmdi zmdi-account"></i>
            </span>
            <p class="line">Corine Tea</p>
            <p class="line">Under CreativeCommon BY NC SA</p>
        </li>
    </ul>
</section>
<div>
    <ul class="tabs">
        <li onclick="Stickers_ajaxShow('{$jid}')" class="active">
            <a href="#"><img alt=":sticker:" class="emoji medium" src="{$icon}"></a>
        </li>
        <li onclick="Stickers_ajaxSmiley('{$jid}')">
            <a href="#"><img alt=":smiley:" class="emoji medium" src="{$c->getSmileyPath('1f603')}"></a>
        </li>
        <li onclick="Stickers_ajaxSmileyTwo('{$jid}')">
            <a href="#"><img alt=":smiley:" class="emoji medium" src="{$c->getSmileyPath('1f44d')}"></a>
        </li>
    </ul>
</div>

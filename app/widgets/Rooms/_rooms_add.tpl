<section>
    <form name="bookmarkmucadd">
        {if="isset($room)"}
            <h3>{$c->__('rooms.edit')}</h3>
        {else}
            <h3>{$c->__('rooms.add')}</h3>
        {/if}

        <div>
            <input
                {if="isset($room)"}value="{$room->conference}" disabled{/if}
                {if="isset($id)"}value="{$id}" disabled{/if}
                name="jid"
                {if="isset($server)"}
                    placeholder="chatroom@{$server}"
                {else}
                    placeholder="chatroom@server.com"
                {/if}
                type="email"
                required />
            <label>{$c->__('chatrooms.id')}</label>
        </div>
        <div>
            <input
                {if="isset($room)"}value="{$room->name}"{/if}
                name="name"
                placeholder="{$c->__('chatrooms.name_placeholder')}"
                required />
            <label>{$c->__('chatrooms.name')}</label>
        </div>
        <div>
            <input
                {if="isset($room) && $room->nick != ''"}
                    value="{$room->nick}"
                {else}
                    value="{$username}"
                {/if}
                name="nick"
                placeholder="{$username}"/>
            <label>{$c->__('chatrooms.nickname')}</label>
        </div>
        <!--
        <div class="element large mini">
            <label>{$c->__('chatroom.autojoin_label')}</label>
            <div class="checkbox">
                <input type="checkbox" id="autojoin" name="autojoin"/>
                <label for="autojoin"></label>
            </div>
        </div>
        -->
    </section>
    <div>
        <a class="button flat" onclick="Dialog_ajaxClear()">
            {$c->__('button.close')}
        </a>
        {if="isset($room)"}
            <a
                class="button flat"
                onclick="Rooms_ajaxChatroomAdd(MovimUtils.parseForm('bookmarkmucadd'));">
                {$c->__('button.edit')}
            </a>
        {else}
            <a
                class="button flat"
                onclick="Rooms_ajaxChatroomAdd(MovimUtils.parseForm('bookmarkmucadd'));">
                {$c->__('button.add')}
            </a>
        {/if}
    </div>

</div>

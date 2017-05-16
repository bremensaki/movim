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
        <div>
            <ul class="list thick">
                <li>
                    <span class="primary">
                        <div class="checkbox">
                            <input
                                {if="$room->autojoin"}checked{/if}
                                type="checkbox"
                                id="autojoin"
                                name="autojoin"/>
                            <label for="autojoin"></label>
                        </div>
                    </span>
                    <p class="normal line">{$c->__('chatrooms.autojoin')}</p>
                </li>
            </ul>
        </div>
    </section>
    <div>
        <button class="button flat" onclick="Dialog_ajaxClear()">
            {$c->__('button.cancel')}
        </button>
        {if="isset($room)"}
            <button
                class="button flat"
                onclick="Rooms_ajaxChatroomAdd(MovimUtils.parseForm('bookmarkmucadd'));">
                {$c->__('button.edit')}
            </button>
        {else}
            <button
                class="button flat"
                onclick="Rooms_ajaxChatroomAdd(MovimUtils.parseForm('bookmarkmucadd'));">
                {$c->__('button.add')}
            </button>
        {/if}
    </div>

</div>

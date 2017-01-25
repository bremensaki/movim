<section class="scroll">
    <form name="config">
        {$form}
    </form>
</section>
<div>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
    <button onclick="Chat_ajaxSetRoomConfig(MovimUtils.parseForm('config'), '{$room}'); Dialog_ajaxClear();" class="button flat">
        {$c->__('button.save')}
    </button>
</div>

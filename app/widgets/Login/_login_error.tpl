<section>
    <h3>{$c->__('error.oops')}</h3>
    <br />
    <h4 class="gray">{$error}</h4>
</section>
<div>
    <button
        class="button flat oppose"
        onclick="MovimUtils.redirect('{$c->route('disconnect')}');">
        {$c->__('button.return')}
    </button>
</div>

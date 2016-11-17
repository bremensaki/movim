<ul class="list middle">
    <li class="subheader">
        <p>
            <span class="info">
                <a href="{$c->route('contact', $me->jid)}">
                    {$c->__('privacy.my_profile')}
                </a>
            </span>
            {$c->__('privacy.privacy_title')}
        </p>
    </li>
    <li>
        <span class="primary">
            <form>
                <div class="control action">
                    <div class="checkbox">
                        <input
                            type="checkbox"
                            id="privacy"
                            name="privacy"
                            {if="$me->privacy"}
                                checked
                            {/if}
                            onchange="{$privacy}">
                        <label for="privacy"></label>
                    </div>
                </div>
            </form>
        </span>

        <p>{$c->__('privacy.privacy_question')}</p>
        <p class="all">{$c->__('privacy.privacy_info')}</p>
    </li>
</ul>

<div class="clear padded"></div>

<form name="vcard4" id="vcard4form" class="flex">
    <h3 class="block large">{$c->__('page.profile')}</h3>
    <div class="block">
        <input type="text" name="fn" class="content" value="{$me->fn}" placeholder="{$c->__('general.name')}">
        <label for="fn">{$c->__('general.name')}</label>
    </div>
    <div class="block">
        <input type="text" name="name" class="content" value="{$me->name}" placeholder="{$c->__('general.nickname')}">
        <label for="name">{$c->__('general.nickname')}</label>
    </div>

    <div class="block large">
        <input type="email" name="email" class="content" value="{$me->email}" placeholder="{$c->__('general.email')}">
        <label for="fn">{$c->__('general.email')}</label>
    </div>

    <!-- The date picker -->

    <div class="block large">
        <label for="day">{$c->__('general.date_of_birth')}</label>

        <div class="select" style="width: 33.33%; float: left;">
            <select name="day" class="datepicker">
                <option value="-1">{$c->__('day.title')}</option>
                {loop="$days"}
                    <option value="{$value}"
                    {if="$key == substr($me->date, 8)"}
                        selected
                    {/if}
                    >{$value}</option>
                {/loop}
            </select>
        </div>
        <div class="select" style="width: 33.33%; float: right;">
            <select name="year" class="datepicker">
                <option value="-1">{$c->__('year.title')}</option>
                {loop="$years"}
                    <option value="{$value}"
                    {if="$value == substr($me->date,0,4)"}
                        selected
                    {/if}
                    >{$value}</option>
                {/loop}
            </select>
        </div>
        <div class="select" style="width: 33.33%;">
            <select name="month" class="datepicker">
                <option value="-1">{$c->__('month.title')}</option>
                {loop="$months"}
                    <option value="{$key}"
                    {if="$key == substr($me->date,5,2)"}
                        selected
                    {/if}
                    >{$value}</option>
                {/loop}
            </select>
        </div>
    </div>

    <div class="block">
        <div class="select">
            <select name="gender">
            {loop="$gender"}
                <option
                {if="$key == $me->gender"}
                    selected
                {/if}
                value="{$key}">{$value}</option>
            {/loop}
            </select>
        </div>
        <label for="gender">{$c->__('general.gender')}</label>
    </div>

    <div class="block">
        <label for="marital">{$c->__('general.marital')}</label>
        <div class="select">
            <select name="marital">
            {loop="$marital"}
                <option
                {if="$key == $me->marital"}
                    selected
                {/if}
                value="{$key}">{$value}</option>
            {/loop}
            </select>
        </div>
    </div>

    <div class="block">
        <input type="url" name ="url" class="content" value="{$me->url}" placeholder="https://mywebsite.com/">
        <label for="url">{$c->__('general.website')}</label>
    </div>

    <div class="block large">
        <textarea name="desc" id="desctext" class="content" onkeyup="MovimUtils.textareaAutoheight(this);">{$desc}</textarea>
        <label for="desc">{$c->__('general.about')}</label>
    </div>

    <div class="clear padded"></div>

    <h3 class="block large">{$c->__('position.position_title')}</h3>

    <div class="block">
        <input type="text" type="locality" name ="locality" class="content" value="{$me->adrlocality}" placeholder="{$c->__('position.locality')}">
        <label for="url">{$c->__('position.locality')}</label>
    </div>

    <div class="block">
        <div class="select">
            <select name="country">
                <option value=""></option>
                {loop="$countries"}
                    <option
                    {if="$value == $me->adrcountry"}
                        selected
                    {/if}
                    value="{$value}">{$value}</option>
                {/loop}
            </select>
        </div>
        <label for="country">{$c->__('position.country')}</label>
    </div>

    <h3 class="block large">{$c->__('accounts.accounts_title')}</h3>

    <div class="block">
        <input type="text" name="twitter" class="content" value="{$me->twitter}" placeholder="{$c->__('accounts.twitter')}">
        <label for="twitter"><i class="fa fa-twitter"></i> {$c->__('accounts.twitter')}</label>
    </div>

    <div class="block">
        <input type="text" name="skype" class="content" value="{$me->skype}" placeholder="{$c->__('accounts.skype')}">
        <label for="skype"><i class="fa fa-skype"></i> {$c->__('accounts.skype')}</label>
    </div>

    <div class="block">
        <input type="email" name="yahoo" class="content" value="{$me->yahoo}" placeholder="{$c->__('accounts.yahoo')}">
        <label for="skype"><i class="fa fa-yahoo"></i> {$c->__('accounts.yahoo')}</label>
    </div>

    <div class="block large">
        <a
            onclick="
                {$submit}
                MovimUtils.buttonSave('#vcard4validate');
                this.value = '{$c->__('Submitting')}';
                this.className='button oppose inactive';"
            class="button color oppose"
            id="vcard4validate"
            >
            {$c->__('button.save')}
        </a>
        <a
            onclick="document.querySelector('#vcard4form').reset();"
            class="button flat oppose">
            {$c->__('button.reset')}
        </a>
    </div>
</form>

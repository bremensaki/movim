/*function showNotifsList() {
    movim_toggle_class('#notifslist', 'show');
}*/

var Notifs = {
    lswidget : localStorage.getItem('username').replace('@', 'at') + '_Notifs',

    refresh : function() {
        var items = document.querySelectorAll('#notifs_widget li:not(.subheader)');
        var i = 0;
        while(i < items.length)
        {
            items[i].onclick = function(e) {
                MovimTpl.showPanel();
                Contact_ajaxGetContact(this.dataset.jid);
                MovimUtils.removeClassInList('active', items);
                MovimUtils.addClass(this, 'active');
            };

            items[i].querySelector('span.control.icon').onclick = function(e) {
                Notifs_ajaxAsk(this.dataset.jid);
            };
            i++;
        }

        /* Should the list of pending invitations show? */
        var invitShown = document.querySelector('#notifs_widget li.subheader')
        if(invitShown){
            var ls = localStorage.getObject(Notifs.lswidget);
            if(ls === null){
                localStorage.setObject(Notifs.lswidget, {"invitShown": true});
                Notifs.ls = localStorage.getObject(Notifs.lswidget);
            }
            if(localStorage.getObject(Notifs.lswidget).invitShown === true)
                document.querySelector('#notifs_widget').className += " groupshown";

            invitShown.onclick = function(e) {
                Notifs.showHide(e.target);
            }
        }
    },
    showHide : function(e){
        state = localStorage.getObject(Notifs.lswidget).invitShown;
        parent = document.querySelector('#notifs_widget');

        if(state === true)
            parent.className = parent.className.replace(" groupshown", "");
        else
            parent.className += " groupshown";

        localStorage.setObject(Notifs.lswidget, {"invitShown": !state});
    },
};

MovimWebsocket.attach(function() {
    Notifs_ajaxGet();
});

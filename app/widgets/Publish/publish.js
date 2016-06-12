var Publish = {
    init: function() {
        if(localStorage.getItem('share_url')) {
            Publish_ajaxCreateBlog();
            MovimTpl.showPanel();
        }
    },

    setEmbed: function() {
        if(localStorage.getItem('share_url')) {
            var embed = document.querySelector('input[name=embed]');
            embed.value = localStorage.getItem('share_url');
            embed.onpaste();
            localStorage.removeItem('share_url');
        }
    },

    enableSend: function() {
        MovimUtils.removeClass('#button_send', 'disabled');
    },

    disableSend: function() {
        MovimUtils.addClass('#button_send', 'disabled');
    },

    enableContent: function() {
        document.querySelector('#enable_content').style.display = 'none';
        document.querySelector('#content_field').style.display = 'block';
    },

    headerBack: function(server, node, ok) {
        // We check if the form is filled
        if(Publish.checkFilled() && ok == false) {
            Publish_ajaxFormFilled(server, node);
            return;
        }

        // We are on the news page
        if(typeof Post_ajaxClear === 'function') {
            Post_ajaxClear();
            //Header_ajaxReset('news');
            MovimTpl.hidePanel();
        } else {
            Group_ajaxGetItems(server, node);
            Group_ajaxGetAffiliations(server, node);
        }
    },

    checkFilled: function() {
        var form = document.querySelector('form[name=post]');

        var i = 0;
        while(i < form.elements.length)
        {
            if(form.elements[i].type != 'hidden'
            && form.elements[i].value != form.elements[i].defaultValue) {
                return true;
            }
            i++;
        }

        return false;
    },

    initEdit: function() {
        Publish.enableContent();
        Publish_ajaxEmbedTest(document.querySelector('#content_link input').value);
        MovimUtils.textareaAutoheight(document.querySelector('#content_field textarea'));
    }
}

Upload.attach(function() {
    var embed = document.querySelector('input[name=embed]');
    embed.value = Upload.get;
    embed.onpaste();
});

MovimWebsocket.attach(function() {
    Publish.init();
});

<?php

use Moxl\Xec\Action\Message\Composing;
use Moxl\Xec\Action\Message\Paused;
use Moxl\Xec\Action\Message\Publish;

use Moxl\Xec\Action\Muc\GetConfig;
use Moxl\Xec\Action\Muc\SetConfig;
use Moxl\Xec\Action\Muc\SetSubject;

use Moxl\Xec\Action\BOB\Request;

use Respect\Validation\Validator;

use Ramsey\Uuid\Uuid;

use Movim\Picture;

class Chat extends \Movim\Widget\Base
{
    private $_pagination = 30;
    private $_wrapper = array();
    private $_msgMap = array();

    function load()
    {
        $this->addjs('chat.js');
        //$this->addjs('chat_otr.js');
        $this->addcss('chat.css');
        $this->registerEvent('carbons', 'onMessage');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('receipt', 'onMessage');
        $this->registerEvent('mamresult', 'onMessageHistory');
        $this->registerEvent('composing', 'onComposing');
        $this->registerEvent('paused', 'onPaused');
        $this->registerEvent('gone', 'onGone');
        $this->registerEvent('subject', 'onConferenceSubject');

        $this->registerEvent('muc_getconfig_handle', 'onRoomConfig');
        $this->registerEvent('muc_setconfig_handle', 'onRoomConfigSaved');

        $this->registerEvent('bob_request_handle', 'onSticker');
        //$this->registerEvent('presence', 'onPresence');
    }

    /*
     * Disabled for the moment, it SPAM a bit too much the user
    function onPresence($packet)
    {
        $contacts = $packet->content;
        if($contacts != null){
            $contact = $contacts[0];

            if($contact->value < 5) {
                $avatar = $contact->getPhoto('s');
                if($avatar == false) $avatar = null;

                $presences = getPresences();
                $presence = $presences[$contact->value];

                Notification::append('presence', $contact->getTrueName(), $presence, $avatar, 4);
            }
        }
    }*/

    function onMessageHistory($packet)
    {
        $this->onMessage($packet, true);
    }

    function onMessage($packet, $history = false)
    {
        $message = $packet->content;
        $cd = new \Modl\ContactDAO;

        if($message->session == $message->jidto && !$history) {
            $from = $message->jidfrom;

            $contact = $cd->getRosterItem($from);
            if($contact == null)
                $contact = $cd->get($from);

            if($contact != null
            && !preg_match('#^\?OTR#', $message->body)
            && $message->type != 'groupchat') {
                $avatar = $contact->getPhoto('s');
                if($avatar == false) $avatar = null;
                Notification::append(
                    'chat|'.$from,
                    $contact->getTrueName(),
                    $message->body,
                    $avatar,
                    4,
                    $this->route('chat', $contact->jid)
                );
            } elseif ($message->type == 'groupchat'
                   && $message->quoted) {
                $cd = new \Modl\ConferenceDAO;
                $c = $cd->get($from);

                Notification::append(
                    'chat|'.$from,
                    ($c != null && $c->name) ? $c->name : $from,
                    $message->resource.': '.$message->body,
                    false,
                    4);
            }

            RPC::call('MovimTpl.fill', '#' . cleanupId($from.'_state'), $contact->jid);
        } else {
            // If the message is from me we reset the notif counter
            $from = $message->jidto;
            $n = new Notification;
            $n->ajaxClear('chat|'.$from);
        }
        if(!preg_match('#^\?OTR#', $message->body)) {
            RPC::call('Chat.appendMessagesWrapper', $this->prepareMessage($message, $from));
        }
    }

    function onSticker($packet)
    {
        list($to, $cid) = array_values($packet->content);
        $this->ajaxGet($to);
    }

    function onComposing($array)
    {
        $this->setState($array, $this->__('message.composing'));
    }

    function onPaused($array)
    {
        $this->setState($array, $this->__('message.paused'));
    }

    function onGone($array)
    {
        $this->setState($array, $this->__('message.gone'));
    }

    function onConferenceSubject($packet)
    {
        $this->ajaxGetRoom($packet->content->jidfrom);
    }

    function onRoomConfig($packet)
    {
        list($config, $room) = array_values($packet->content);

        $view = $this->tpl();

        $xml = new \XMPPtoForm();
        $form = $xml->getHTML($config->x->asXML());

        $view->assign('form', $form);
        $view->assign('room', $room);

        Dialog::fill($view->draw('_chat_config_room', true), true);
    }

    function onRoomConfigSaved($packet)
    {
        Notification::append(false, $this->__('chatroom.config_saved'));
    }

    private function setState($array, $message)
    {
        list($from, $to) = $array;
        if($from == $this->user->getLogin()) {
            $jid = $to;
        } else {
            $jid = $from;
        }

        $view = $this->tpl();
        $view->assign('message', $message);

        $html = $view->draw('_chat_state', true);

        RPC::call('MovimTpl.fill', '#' . cleanupId($jid.'_state'), $html);
    }

    /**
     * @brief Get a discussion
     * @param string $jid
     */
    function ajaxGet($jid = null)
    {
        if($jid == null) {
            RPC::call('MovimUtils.pushState', $this->route('chat'));

            RPC::call('MovimUtils.removeClass', '#chat_widget', 'fixed');
            RPC::call('MovimTpl.fill', '#chat_widget', $this->prepareEmpty());
        } else {
            $chats = new Chats;
            $chats->ajaxGetHistory($jid);

            $notif = new Notification;
            $notif->ajaxClear('chat|'.$jid);

            $html = $this->prepareChat($jid);

            RPC::call('MovimUtils.pushState', $this->route('chat', $jid));

            RPC::call('MovimUtils.addClass', '#chat_widget', 'fixed');
            RPC::call('MovimTpl.fill', '#chat_widget', $html);
            RPC::call('MovimTpl.showPanel');
            RPC::call('Chat.focus');

            $this->prepareMessages($jid);
        }
    }

    /**
     * @brief Get a Drawer view of a contact
     */
    function ajaxGetContact($jid)
    {
        $c = new Contact;
        $c->ajaxGetDrawer($jid);
    }

    /**
     * @brief Get a chatroom
     * @param string $jid
     */
    function ajaxGetRoom($room)
    {
        if(!$this->validateJid($room)) return;

        $cod = new \modl\ConferenceDAO();
        $r = $cod->get($room);

        if($r) {
            $rooms = new Rooms;
            if(!$rooms->checkConnected($r->conference, $r->nick)) {
                RPC::call('Rooms_ajaxJoin', $r->conference, $r->nick);
            }

            $html = $this->prepareChat($room, true);

            RPC::call('MovimUtils.pushState', $this->route('chat', [$room, 'room']));

            RPC::call('MovimUtils.addClass', '#chat_widget', 'fixed');
            RPC::call('MovimTpl.fill', '#chat_widget', $html);
            RPC::call('MovimTpl.showPanel');
            RPC::call('Chat.focus');

            $this->prepareMessages($room, true);

            $notif = new Notification;
            $notif->ajaxClear('chat|'.$room);
            RPC::call('Notification.current', 'chat|'.$room);
        } else {
            RPC::call('Rooms_ajaxAdd', $room);
        }
    }

    /**
     * @brief Send a message
     *
     * @param string $to
     * @param string $message
     * @return void
     */
    function ajaxSendMessage($to, $message, $muc = false, $resource = false, $replace = false) {
        $body = trim(rawurldecode($message));

        if($body == '' || $body == '/me')
            return;

        $m = new \Modl\Message();
        $m->session = $this->user->getLogin();
        $m->jidto   = echapJid($to);
        $m->jidfrom = $this->user->getLogin();

        if($replace != false) {
            $m->newid     = Uuid::uuid4();
            $m->id        = $replace->id;
            $m->edited    = true;
            $m->published = $replace->published;
            $m->delivered = $replace->delivered;
        } else {
            $m->id      = Uuid::uuid4();
            $m->published = gmdate('Y-m-d H:i:s');
        }

        $session    = \Session::start();

        $m->type    = 'chat';
        $m->resource = $session->get('resource');

        if($muc) {
            $m->type        = 'groupchat';
            $m->resource = $session->get('username');
            $m->jidfrom     = $to;
        }

        $m->body      = $body;
        $promise = $m->checkPicture();

        $promise->done(function() use ($m, $to, $muc, $resource, $replace) {
            //$m->html      = prepareString($m->body, false, true);

            if($resource != false) {
                $to = $to . '/' . $resource;
            }

            // We decode URL codes to send the correct message to the XMPP server
            $p = new Publish;
            $p->setTo($to);
            //$p->setHTML($m->html);
            $p->setContent($m->body);

            if($replace != false) {
                $p->setId($m->newid);
                $p->setReplace($m->id);
            } else {
                $p->setId($m->id);
            }

            if($muc) {
                $p->setMuc();
            }

            $p->request();

            /* Is it really clean ? */
            if(!$p->getMuc()) {
                if(!preg_match('#^\?OTR#', $m->body)) {
                    $md = new \Modl\MessageDAO;
                    $md->set($m);
                }

                $packet = new Moxl\Xec\Payload\Packet;
                $packet->content = $m;
                $this->onMessage($packet/*, true*/);
            }
        });
    }

    /**
     * @brief Send a correction message
     *
     * @param string $to
     * @param string $message
     * @return void
     */
    function ajaxCorrect($to, $message)
    {
        $md = new \Modl\MessageDAO;
        $m = $md->getLastItem($to);

        if($m) {
            $this->ajaxSendMessage($to, $message, false, false, $m);
        }
    }

    /**
     * @brief Get the last message sent
     *
     * @param string $to
     * @return void
     */
    function ajaxLast($to)
    {
        $md = new \Modl\MessageDAO;
        $m = $md->getLastItem($to);

        if(!isset($m->sticker))
            RPC::call('Chat.setTextarea', $m->body);
    }

    /**
     * @brief Send a "composing" message
     *
     * @param string $to
     * @return void
     */
    function ajaxSendComposing($to) {
        if(!$this->validateJid($to)) return;

        $mc = new Composing;
        $mc->setTo($to)->request();
    }

    /**
     * @brief Send a "paused" message
     *
     * @param string $to
     * @return void
     */
    function ajaxSendPaused($to) {
        if(!$this->validateJid($to)) return;

        $mp = new Paused;
        $mp->setTo($to)->request();
    }

    /**
     * @brief Get the chat history
     *
     * @param string jid
     * @param string time
     */
    function ajaxGetHistory($jid, $date)
    {
        if(!$this->validateJid($jid)) return;
        $md = new \Modl\MessageDAO;
        $messages = $md->getHistory(echapJid($jid), date(DATE_ISO8601, strtotime($date)), $this->_pagination);

        if(count($messages) > 0) {
            Notification::append(false, $this->__('message.history', count($messages)));

            foreach($messages as $message) {
                if(!preg_match('#^\?OTR#', $message->body)) {
                    //RPC::call('Chat.appendMessage', $this->prepareMessage($message), true);
                    //$this->_msgMap[$message->published.$message->jid] = $message;
                    $this->prepareMessage($message);
                }
            }
            //foreach($this->_msgMap as $message)
            //    $this->prepareMessage($message);
            RPC::call('Chat.appendMessagesWrapper', $this->_wrapper, true);
            $this->_wrapper = array();

            //RPC::call('Chat.cleanBubbles');
        }
    }

    /**
     * @brief Configure a room
     *
     * @param string $room
     */
    function ajaxGetRoomConfig($room)
    {
        if(!$this->validateJid($room)) return;

        $gc = new GetConfig;
        $gc->setTo($room)
           ->request();
    }

    /**
     * @brief Save the room configuration
     *
     * @param string $room
     */
    function ajaxSetRoomConfig($data, $room)
    {
        if(!$this->validateJid($room)) return;

        $sc = new SetConfig;
        $sc->setTo($room)
           ->setData($data)
           ->request();
    }

    /**
     * @brief Get the subject form of a chatroom
     */
    function ajaxGetSubject($room)
    {
        if(!$this->validateJid($room)) return;

        $view = $this->tpl();

        $md = new \Modl\MessageDAO;
        $s = $md->getRoomSubject($room);

        $view->assign('room', $room);
        $view->assign('subject', $s);

        Dialog::fill($view->draw('_chat_subject', true));
    }

    /**
     * @brief Change the subject of a chatroom
     */
    function ajaxSetSubject($room, $form)
    {
        if(!$this->validateJid($room)) return;

        $validate_subject = Validator::stringType()->length(0, 200);
        if(!$validate_subject->validate($form->subject->value)) return;

        $p = new SetSubject;
        $p->setTo($room)
          ->setSubject($form->subject->value)
          ->request();
    }

    function prepareChat($jid, $muc = false)
    {
        $view = $this->tpl();

        $view->assign('jid', $jid);

        $jid = echapJS($jid);

        $view->assign('smiley', $this->call('ajaxSmiley'));

        $view->assign('emoji', prepareString('😀'));
        $view->assign('muc', $muc);
        $view->assign('anon', false);

        if($muc) {
            $md = new \Modl\MessageDAO;
            $s = $md->getRoomSubject($jid);

            $cd = new \Modl\ConferenceDAO;
            $c = $cd->get($jid);

            $pd = new \Modl\PresenceDAO;
            $p = $pd->getMyPresenceRoom($jid);

            $view->assign('room', $jid);
            $view->assign('subject', $s);
            $view->assign('presence', $p);
            $view->assign('conference', $c);
        } else {
            $cd = new \Modl\ContactDAO;

            $cr = $cd->getRosterItem($jid);
            if(isset($cr)) {
                $contact = $cr;
            } else {
                $contact = $cd->get($jid);
            }

            $view->assign('contact', $contact);
            $view->assign('jid', $jid);
        }

        return $view->draw('_chat', true);
    }

    function prepareMessages($jid, $muc = false)
    {
        if(!$this->validateJid($jid)) return;

        $md = new \Modl\MessageDAO;

        if($muc) {
            $messages = $md->getRoom(echapJid($jid));
        } else {
            $messages = $md->getContact(echapJid($jid), 0, $this->_pagination);
        }

        if(is_array($messages)) {
            $messages = array_reverse($messages);

            foreach($messages as $message) {
                $this->prepareMessage($message);
            }
        }

        $view = $this->tpl();
        $view->assign('jid', $jid);

        $cd = new \Modl\ContactDAO;
        $contact = $cd->get($jid);
        $me = $cd->get();
        if($me == null) {
            $me = new \Modl\Contact;
        }

        $view->assign('contact', $contact);
        $view->assign('me', false);
        $left = $view->draw('_chat_bubble', true);

        $view->assign('contact', $me);
        $view->assign('me', true);
        $right = $view->draw('_chat_bubble', true);

        $room = $view->draw('_chat_bubble_room', true);

        RPC::call('Chat.setBubbles', $left, $right, $room);
        //RPC::call('Chat.appendMessages', $messages);
        RPC::call('Chat.appendMessagesWrapper', $this->_wrapper);
        RPC::call('MovimTpl.scrollPanel');
        RPC::call('Chat.clearReplace');
    }

    function prepareMessage(&$message, $jid = null)
    {
        if($jid != $message->jidto && $jid != $message->jidfrom && $jid != null)
            return $this->_wrapper;

        $message->jidto = echapJS($message->jidto);
        $message->jidfrom = echapJS($message->jidfrom);

        if (isset($message->html)) {
            $message->body = $message->html;
        } else {
            $message->convertEmojis();
            $message->addUrls();
        }

        if (isset($message->sticker)) {
            $p = new Picture;
            $sticker = $p->get($message->sticker, false, false, 'png');
            $stickerSize = $p->getSize();

            if ($sticker == false) {
                $r = new Request;
                $r->setTo($message->jidfrom)
                    ->setResource($message->resource)
                    ->setCid($message->sticker)
                    ->request();
            } else {
                $message->sticker = [
                    'url' => $sticker,
                    'width' => $stickerSize['width'],
                    'height' => $stickerSize['height']
                ];
            }
        }

        if (isset($message->picture)) {
            $message->sticker = [
                'url' => $message->picture,
                'picture' => true
            ];
        }

        $message->rtl = isRTL($message->body);

        $message->publishedPrepared = prepareDate(strtotime($message->published), true);

        if ($message->delivered) {
            $message->delivered = prepareDate(strtotime($message->delivered), true);
        }

        $date = substr($message->published, 0, 10);

        if ($message->type == 'groupchat') {
            $message->color = stringToColor($message->session . $message->resource . $message->jidfrom . $message->type);

            //fillup $wrapper
            if ($message->body != "") {
                if (!array_key_exists($date, $this->_wrapper))
                    $this->_wrapper[$date] = [$message];
                else
                    array_push($this->_wrapper[$date], $message);
            }

            $pd = new \Modl\PresenceDAO;
            $p = $pd->getMyPresenceRoom($message->from);
        } else {
            $msgkey = $message->jidfrom . '>' . substr($message->published, 11, 5);

            //fillup $wrapper
            if (!array_key_exists($date, $this->_wrapper)) {
                $sticker = "";

                if (isset($message->sticker)) {
                    $sticker = "sticker";
                }

                $this->_wrapper[$date] = ['0' . $sticker . '<' . $msgkey => [$message]];
            } else { //date contains at least one speaker@time=>msg already
                end($this->_wrapper[$date]);
                $lastkey = key($this->_wrapper[$date]);

                if (substr($lastkey, strpos($lastkey, '<') + 1) == $msgkey // same jidfrom, same min
                    && !isset($message->sticker) // this msg is not a sticker
                    && strpos($lastkey, "sticker<") === false
                ) { // the previous msg was not a sticker
                    array_push($this->_wrapper[$date][$lastkey], $message);
                } else {
                    $sticker = "";

                    if (isset($message->sticker)) {
                        $sticker = "sticker";
                    }

                    $this->_wrapper[$date][count($this->_wrapper[$date]) . $sticker . '<' . $msgkey] = [$message];
                }
            }
        }

        return $this->_wrapper;
    }

    function prepareEmpty()
    {
        $view = $this->tpl();

        $chats = \Movim\Cache::c('chats');
        $chats = ($chats == null) ? false : array_keys($chats);

        $cd = new \Modl\ContactDAO;
        $view->assign('presencestxt', getPresencesTxt());
        $view->assign('top', $cd->getTop(8, $chats));
        return $view->draw('_chat_empty', true);
    }

    /**
     * @brief Validate the jid
     *
     * @param string $jid
     */
    private function validateJid($jid)
    {
        $validate_jid = Validator::stringType()->noWhitespace()->length(6, 60);
        if(!$validate_jid->validate($jid)) return false;
        else return true;
    }

    function getSmileyPath($id)
    {
        return getSmileyPath($id);
    }

    function display()
    {
    }
}

<?php

use Moxl\Xec\Action\Message\Publish;

use Moxl\Xec\Action\BOB\Answer;
use Ramsey\Uuid\Uuid;

use Respect\Validation\Validator;

class Stickers extends \Movim\Widget\Base
{
    function load()
    {
        $this->addcss('stickers.css');
        $this->addjs('stickers.js');
        $this->registerEvent('bob', 'onRequest');
    }

    function onRequest($packet)
    {
        $content = $packet->content;

        $to = $content[0];
        $id = $content[1];
        $cid = $content[2];

        list($c, $ext) = explode('@', $cid);
        list($sh, $key) = explode('+', $c);

        $base64 = base64_encode(file_get_contents(dirname(__FILE__).'/stickers/'.$key.'.png'));

        $a = new Answer;
        $a->setTo($to)
          ->setId($id)
          ->setCid($cid)
          ->setType('image/png')
          ->setBase64($base64)
          ->request();
    }

    function ajaxSend($to, $file)
    {
        if(!$this->validateJid($to)) return;

        list($key, $ext) = explode('.', $file);

        $filepath = dirname(__FILE__).'/stickers/'.$key.'.png';

        if(!file_exists($filepath)) return;

        // We get the base64
        $base64 = base64_encode(file_get_contents($filepath));

        // Caching the picture
        $p = new Picture;
        $p->fromBase($base64);
        $p->set($key, 'png');

        // Creating a message
        $m = new \Modl\Message;
        $m->session = $this->user->getLogin();
        $m->jidto   = echapJid($to);
        $m->jidfrom = $this->user->getLogin();
        $m->sticker = $key;
        $m->body    = $this->__('sticker.sent');

        $m->published = gmdate('Y-m-d H:i:s');

        $session    = \Session::start();

        $m->id      = Uuid::uuid4();
        $m->type    = 'chat';
        $m->resource = $session->get('resource');

        // Sending the sticker
        $html = "<p><img alt='Sticker' src='cid:sha1+".$key."@bob.xmpp.org'/></p>";

        $p = new Publish;
        $p->setTo($m->jidto)
          ->setContent($m->body)
          ->setHTML($html)
          ->setId($m->id)
          ->request();

        $md = new \Modl\MessageDAO;
        $md->set($m);

        // Sending it to Chat
        $packet = new Moxl\Xec\Payload\Packet;
        $packet->content = $m;
        $c = new Chat;
        $c->onMessage($packet/*, true*/);
    }

    function ajaxShow($to)
    {
        if(!$this->validateJid($to)) return;

        $files = scandir(dirname(__FILE__).'/stickers/');

        array_shift($files);
        array_shift($files);

        $view = $this->tpl();
        $view->assign('jid', $to);
        $view->assign('stickers', $files);
        $view->assign('icon', $this->respath('stickers').'/icon.png');
        $view->assign('path', $this->respath('stickers').'/');

        Drawer::fill($view->draw('_stickers', true), true);
    }

    /**
     * @brief Show the smiley list
     */
    function ajaxSmiley($to)
    {
        if(!$this->validateJid($to)) return;

        $view = $this->tpl();
        $view->assign('jid', $to);
        $view->assign('icon', $this->respath('stickers').'/icon.png');
        Drawer::fill($view->draw('_stickers_smiley', true));
    }

    /**
     * @brief Show the smiley list
     */
    function ajaxSmileyTwo($to)
    {
        if(!$this->validateJid($to)) return;

        $view = $this->tpl();
        $view->assign('jid', $to);
        $view->assign('icon', $this->respath('stickers').'/icon.png');
        Drawer::fill($view->draw('_stickers_smiley_two', true));
    }

    /**
     * @brief Get the path of a emoji
     */
    function ajaxSmileyGet($string)
    {
        return prepareString($string, true);
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

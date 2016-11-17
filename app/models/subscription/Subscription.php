<?php

namespace Modl;

use Movim\Picture;

class Subscription extends Model
{
    public $jid;
    protected $server;
    protected $node;
    protected $subscription;
    protected $subid;
    protected $title;
    public $description;
    public $tags;
    public $timestamp;
    public $name;
    public $servicename;
    public $logo;

    public function __construct()
    {
        $this->_struct = '
        {
            "jid" :
                {"type":"string", "size":64, "key":true },
            "server" :
                {"type":"string", "size":64, "key":true },
            "node" :
                {"type":"string", "size":128, "key":true },
            "subscription" :
                {"type":"string", "size":128, "mandatory":true },
            "subid" :
                {"type":"string", "size":128 },
            "title" :
                {"type":"string", "size":128 },
            "tags" :
                {"type":"text" },
            "timestamp" :
                {"type":"date" }
        }';

        parent::__construct();
    }

    public function getLogo()
    {
        $p = new Picture;
        return $p->get($this->server.$this->node, 120);
    }

    function set($jid, $server, $node, $s)
    {
        $this->__set('jid',             $jid);
        $this->__set('server',          $server);
        $this->__set('node',            $node);
        $this->__set('jid',             (string)$s->attributes()->jid);
        $this->__set('subscription',    (string)$s->attributes()->subscription);
        $this->__set('subid',           (string)$s->attributes()->subid);
        $this->__set('tags', serialize([]));

        if($this->subid = '')
            $this->subid = 'default';
    }
}

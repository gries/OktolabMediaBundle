<?php

namespace Oktolab\MediaBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class FinalizeEpisodeEvent extends Event
{
    protected $uniqID;

    public function __construct($uniqID) {
        $this->uniqID = $uniqID;
    }

    public function getUniqID()
    {
        return $this->uniqID;
    }
}

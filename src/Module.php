<?php


namespace PinaGoUnisender;


use Pina\ModuleInterface;
use PinaNotifications\Transports\TransportRegistry;

class Module implements ModuleInterface
{

    public function __construct()
    {
        TransportRegistry::set('email', new Transport());
    }


    public function getPath()
    {
        return __DIR__;
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function getTitle()
    {
        return 'PinaGoUnisender';
    }



}
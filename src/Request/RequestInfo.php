<?php


namespace Behamin\ServiceProxy\Request;


interface RequestInfo
{
    public function getService();

    public function getPath();
}

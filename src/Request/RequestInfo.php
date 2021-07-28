<?php


namespace Behamin\ServiceProxy\Request;


interface RequestInfo
{
    public function getService();

    public function getPath();

    public function getFiles();

    public function getHeaders(): array;

    public function getOptions(): array;
}

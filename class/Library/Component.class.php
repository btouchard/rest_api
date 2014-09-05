<?php
namespace Library;

class Component {

    protected $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }
}
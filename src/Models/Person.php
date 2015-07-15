<?php

namespace Nylas\Models;


class Person {

    public function __construct($name=NULL, $email=NULL) {
        $this->name = $name;
        $this->email = $email;
    }

    public function json() {
        return array("name"  => $this->name,
                     "email" => $this->email);
    }

}
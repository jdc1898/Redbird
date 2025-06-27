<?php

namespace PHPSTORM_META {

    registerArgumentsSet('illuminate_routing_controller',
        \Illuminate\Routing\Controller::class
    );

    registerArgumentsSet('illuminate_facades',
        \Illuminate\Support\Facades\Log::class,
        \Illuminate\Support\Facades\Validator::class
    );

    registerArgumentsSet('illuminate_validation',
        \Illuminate\Validation\ValidationException::class
    );

    // Laravel Model magic methods
    override(\Illuminate\Database\Eloquent\Model::create(0), map([
        '' => '@'
    ]));

    override(\Illuminate\Database\Eloquent\Model::update(0), map([
        '' => '@'
    ]));

    override(\Illuminate\Database\Eloquent\Model::fresh(0), map([
        '' => '@'
    ]));

    // Eloquent magic properties
    exitPoint(\Illuminate\Database\Eloquent\Model::__get());
    exitPoint(\Illuminate\Database\Eloquent\Model::__set());

}

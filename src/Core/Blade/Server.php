<?php

use Illuminate\Support\Facades\Blade;

Blade::directive('broker_redirection', function () {
    return '<input type="hidden" name="broker" value="{{ \Venespana\Sso\Core\AuthSystem::brokerRedirUrl() }}">';
});
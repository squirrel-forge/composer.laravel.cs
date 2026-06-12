<?php

namespace SquirrelForge\Laravel\CoreSupport\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Symfony\Component\HttpFoundation\Cookie;

class PreventRequestForgeryExtended extends PreventRequestForgery {

    /**
     * Replace the core::newCookie method
     * Create a new "XSRF-TOKEN" cookie that contains the CSRF token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $config
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    protected function newCookie($request, $config)
    {
        if (!config('sqf-cs.csrf.enabled')) {
            return parent::newCookie($request, $config);
        }
        return new Cookie(
                'XSRF-TOKEN',
                $request->session()->token(),
                $this->availableAt(60 * $config['lifetime']),
                $config['path'],
                $config['domain'],
                $config['secure'],
                config('sqf-cs.csrf.httpOnly', false),
                false,
                $config['same_site'] ?? null,
                $config['partitioned'] ?? false
        );
    }
}

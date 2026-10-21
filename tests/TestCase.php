<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application and sets a dynamic test encryption key.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        // Garantisce una APP_KEY fittizia a 256-bit valida a runtime per i test
        // senza inserire stringhe statiche nei file tracciati (prevenzione GitGuardian)
        if (empty(config('app.key'))) {
            config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
        }

        return $app;
    }
}

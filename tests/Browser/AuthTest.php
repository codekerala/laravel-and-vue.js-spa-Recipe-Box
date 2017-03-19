<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AuthTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testHeader()
    {
        $this->browse(function ($browser) {
            $browser->visit('/')
                ->assertSee('LOGIN')
                ->assertSee('REGISTER')
                ->assertDontSee('LOGOUT');
        });
    }

    public function testRegisterUser()
    {
        $this->browse(function ($browser) {
            $browser->visit('/')
                ->clickLink('REGISTER')
                ->assertPathIs('/register')
                ->assertSee('Register')
                ->type('name', 'tester')
                ->type('email', 'tester@dusk.dev')
                ->type('password', 'secret')
                ->press('Register')
                ->assertPathIs('/');
        });
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    /**
     * Test the public (200) pages.
     *
     * @return void
     */
    public function testPublicPagesAllowed()
    {
        $this->get('/login')->assertStatus(200);
        $this->get('/password/reset')->assertStatus(200);
    }

    /**
     * Test the non public (302) pages.
     *
     * @return void
     */
    public function testNonPublicPages()
    {
        $this->get('/')->assertStatus(302);
        $this->get('/activate')->assertStatus(302);
        $this->get('/activation')->assertStatus(302);
        $this->get('/exceeded')->assertStatus(302);
        $this->get('/home')->assertStatus(302);
        $this->get('/themes')->assertStatus(302);
        $this->get('/users')->assertStatus(302);
        $this->get('/users/create')->assertStatus(302);
        $this->get('/profile/create')->assertStatus(302);
    }
}

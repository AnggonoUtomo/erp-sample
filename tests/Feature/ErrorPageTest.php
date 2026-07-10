<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    public function test_not_found_page_is_informative(): void
    {
        $this->get('/route-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman ini belum punya alamat yang valid.')
            ->assertSee('Route tidak tersedia');
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class StaticPagesTest extends TestCase
{
    public function test_about_us_returns_200(): void
    {
        $this->get('/about-us')->assertStatus(200);
    }

    public function test_privacy_policy_returns_200(): void
    {
        $this->get('/privacy-policy')->assertStatus(200);
    }

    public function test_returns_exchanges_returns_200(): void
    {
        $this->get('/returns-exchanges')->assertStatus(200);
    }

    public function test_delivery_information_returns_200(): void
    {
        $this->get('/delivery-information')->assertStatus(200);
    }

    public function test_about_us_contains_dealership_name(): void
    {
        $this->get('/about-us')->assertSee('Star Yamaha');
    }

    public function test_about_us_contains_address(): void
    {
        $this->get('/about-us')->assertSee('34 Flinders Parade');
    }

    public function test_about_us_contains_phone(): void
    {
        $this->get('/about-us')->assertSee('(07) 3482 3236');
    }
}

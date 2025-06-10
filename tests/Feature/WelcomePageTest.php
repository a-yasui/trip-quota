<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WelcomePageTest extends TestCase
{
    public function test_welcome_page_loads_successfully()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('TripQuota');
        $response->assertSee('複数人での旅行を簡単に管理・共有できるWebアプリケーション');
    }
    
    public function test_welcome_page_shows_login_buttons_for_guests()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('ログイン');
        $response->assertSee('新規登録');
        $response->assertSee('Googleでログイン');
        $response->assertSee('GitHubでログイン');
    }
    
    public function test_welcome_page_has_feature_descriptions()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('TripQuotaの特徴');
        $response->assertSee('グループ管理');
        $response->assertSee('費用分担');
        $response->assertSee('スケジュール共有');
    }
}
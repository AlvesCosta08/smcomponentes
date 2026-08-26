<?php

namespace Tests\Unit;

use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function pode_criar_um_banner()
    {
        $banner = Banner::factory()->create([
            'titulo' => 'Banner Teste',
            'ativo' => true
        ]);

        $this->assertDatabaseHas('banners', [
            'id' => $banner->id,
            'titulo' => 'Banner Teste'
        ]);
    }

    /** @test */
    public function banner_pode_ser_ativado_ou_desativado()
    {
        $bannerAtivo = Banner::factory()->create(['ativo' => true]);
        $bannerInativo = Banner::factory()->create(['ativo' => false]);
        
        $this->assertTrue($bannerAtivo->ativo);
        $this->assertFalse($bannerInativo->ativo);
    }
}
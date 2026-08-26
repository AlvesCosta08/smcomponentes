<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'titulo' => 'Banner Principal',
                'imagem' => 'banner-principal.jpg',
                'link' => '#',
                'descricao' => 'Banner principal da loja',
                'ativo' => true,
                'ordem' => 1,
                'posicao' => 'home',
                'inicio_em' => now(),
                'termino_em' => null,
            ],
            [
                'titulo' => 'Promoção Especial',
                'imagem' => 'banner-promocao.jpg',
                'link' => '#',
                'descricao' => 'Banner de promoção especial',
                'ativo' => true,
                'ordem' => 2,
                'posicao' => 'home',
                'inicio_em' => now(),
                'termino_em' => now()->addDays(30),
            ],
            [
                'titulo' => 'Novidades',
                'imagem' => 'banner-novidades.jpg',
                'link' => '#',
                'descricao' => 'Banner de novidades',
                'ativo' => true,
                'ordem' => 3,
                'posicao' => 'home',
                'inicio_em' => now(),
                'termino_em' => null,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }

        $this->command->info('✅ Banners criados com sucesso!');
    }
}
<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'titulo' => 'Banner Principal',
                'subtitulo' => 'Confira nossas novidades',
                'descricao' => 'Banner principal da loja com as melhores ofertas',
                'imagem' => 'banner-principal.jpg',
                'tipo' => 'imagem',
                'cor_fundo' => '#1a1a2e',
                'cor_texto' => '#ffffff',
                'link' => '#',
                'texto_botao' => 'Ver Ofertas',
                'cor_botao' => '#e94560',
                'ordem' => 1,
                'ativo' => true,
                'inicio_em' => Carbon::now(),
                'termino_em' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'titulo' => 'Promoção Especial',
                'subtitulo' => 'Até 50% de desconto',
                'descricao' => 'Banner de promoção especial para você aproveitar',
                'imagem' => 'banner-promocao.jpg',
                'tipo' => 'imagem',
                'cor_fundo' => '#16213e',
                'cor_texto' => '#ffffff',
                'link' => '/promocoes',
                'texto_botao' => 'Ver Promoções',
                'cor_botao' => '#f5a623',
                'ordem' => 2,
                'ativo' => true,
                'inicio_em' => Carbon::now(),
                'termino_em' => Carbon::now()->addDays(30),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'titulo' => 'Novidades',
                'subtitulo' => 'Lançamentos exclusivos',
                'descricao' => 'Banner de novidades com os produtos mais novos',
                'imagem' => 'banner-novidades.jpg',
                'tipo' => 'imagem',
                'cor_fundo' => '#0f3460',
                'cor_texto' => '#ffffff',
                'link' => '/novidades',
                'texto_botao' => 'Ver Novidades',
                'cor_botao' => '#533483',
                'ordem' => 3,
                'ativo' => true,
                'inicio_em' => Carbon::now(),
                'termino_em' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }

        $this->command->info('✅ Banners criados com sucesso!');
    }
}
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
                'titulo' => 'SM Componentes',
                'subtitulo' => 'Qualidade em Componentes Eletrônicos',
                'descricao' => 'Encontre os melhores componentes para seus projetos',
                'imagem' => 'banners/1786907051_95XXVUHsXH.png',
                'link' => '/produtos',
                'texto_botao' => 'Ver Produtos',
                'cor_texto' => '#ffffff',
                'cor_botao' => 'primary',
                'cor_fundo' => null,
                'ativo' => true,
                'ordem' => 1,
                'inicio_em' => null,
                'termino_em' => null,
            ],
            [
                'titulo' => 'Ofertas Especiais',
                'subtitulo' => 'Descontos imperdíveis',
                'descricao' => 'Aproveite as melhores ofertas em componentes eletrônicos',
                'imagem' => 'banners/1786907060_5B1WvqO3UG.png',
                'link' => '/ofertas',
                'texto_botao' => 'Ver Ofertas',
                'cor_texto' => '#ffffff',
                'cor_botao' => 'warning',
                'cor_fundo' => null,
                'ativo' => true,
                'ordem' => 2,
                'inicio_em' => null,
                'termino_em' => null,
            ],
            [
                'titulo' => 'Novidades',
                'subtitulo' => 'Os melhores componentes',
                'descricao' => 'Confira os novos produtos que chegaram',
                'imagem' => 'banners/1787065996_6a84768cb9887.png',
                'link' => '/produtos/novos',
                'texto_botao' => 'Ver Novidades',
                'cor_texto' => '#ffffff',
                'cor_botao' => 'success',
                'cor_fundo' => null,
                'ativo' => true,
                'ordem' => 3,
                'inicio_em' => null,
                'termino_em' => null,
            ],
            [
                'titulo' => 'Componentes Eletrônicos',
                'subtitulo' => 'Qualidade garantida',
                'descricao' => 'Os melhores componentes para sua loja',
                'imagem' => 'banners/1787066632_hqZrq2wasA.png',
                'link' => '/produtos',
                'texto_botao' => 'Comprar Agora',
                'cor_texto' => '#ffffff',
                'cor_botao' => 'primary',
                'cor_fundo' => null,
                'ativo' => true,
                'ordem' => 4,
                'inicio_em' => null,
                'termino_em' => null,
            ],
            [
                'titulo' => 'Promoção Especial',
                'subtitulo' => 'Descontos incríveis',
                'descricao' => 'Aproveite os melhores preços do mercado',
                'imagem' => 'banners/1787511487_StFn1EPRhCb9.png',
                'link' => '/ofertas',
                'texto_botao' => 'Ver Ofertas',
                'cor_texto' => '#ffffff',
                'cor_botao' => 'danger',
                'cor_fundo' => null,
                'ativo' => true,
                'ordem' => 5,
                'inicio_em' => null,
                'termino_em' => null,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }
    }
}
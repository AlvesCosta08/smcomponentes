<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Buscar produtos em destaque (sem cache)
        $produtosDestaque = Produto::where('ativo', true)
            ->where('quantidade', '>', 0)
            ->where('destaque', true)
            ->latest()
            ->take(8)
            ->get();

        // Se não houver produtos em destaque, pega os mais recentes
        if ($produtosDestaque->isEmpty()) {
            $produtosDestaque = Produto::where('ativo', true)
                ->where('quantidade', '>', 0)
                ->latest()
                ->take(8)
                ->get();
        }

        // Buscar ofertas
        $ofertas = Produto::where('ativo', true)
            ->where('quantidade', '>', 0)
            ->whereNotNull('preco_promocional')
            ->where('preco_promocional', '>', 0)
            ->latest()
            ->take(4)
            ->get();

        // Buscar novos produtos
        $novosProdutos = Produto::where('ativo', true)
            ->where('quantidade', '>', 0)
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        // Buscar mais vendidos (placeholder)
        $maisVendidos = Produto::where('ativo', true)
            ->where('quantidade', '>', 0)
            ->latest()
            ->take(4)
            ->get();

        return view('welcome', compact(
            'produtosDestaque',
            'maisVendidos',
            'novosProdutos',
            'ofertas'
        ));
    }

    // Limpar cache (simplificado)
    public function clearCache()
    {
        \Illuminate\Support\Facades\Cache::flush();
        return back()->with('success', 'Cache limpo com sucesso!');
    }
}
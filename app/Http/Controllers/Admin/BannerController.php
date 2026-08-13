<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('ordem')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'nullable|string|max:255',
            'subtitulo' => 'nullable|string|max:255',
            'descricao' => 'nullable|string',
            'imagem' => 'required_if:tipo,imagem|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'tipo' => 'required|in:imagem,texto,misto',
            'cor_fundo' => 'nullable|string|max:255',
            'cor_texto' => 'nullable|string|max:7',
            'link' => 'nullable|url|max:255',
            'texto_botao' => 'nullable|string|max:50',
            'cor_botao' => 'nullable|string|max:7',
            'ordem' => 'nullable|integer',
            'ativo' => 'boolean',
            'inicio_em' => 'nullable|date',
            'termino_em' => 'nullable|date|after:inicio_em',
        ]);

        // Upload da imagem
        if ($request->hasFile('imagem')) {
            $path = $request->file('imagem')->store('banners', 'public');
            $validated['imagem'] = $path;
        }

        // Definir ordem automaticamente
        if (empty($validated['ordem'])) {
            $validated['ordem'] = Banner::max('ordem') + 1;
        }

        Banner::create($validated);

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner criado com sucesso!');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'titulo' => 'nullable|string|max:255',
            'subtitulo' => 'nullable|string|max:255',
            'descricao' => 'nullable|string',
            'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'tipo' => 'required|in:imagem,texto,misto',
            'cor_fundo' => 'nullable|string|max:255',
            'cor_texto' => 'nullable|string|max:7',
            'link' => 'nullable|url|max:255',
            'texto_botao' => 'nullable|string|max:50',
            'cor_botao' => 'nullable|string|max:7',
            'ordem' => 'nullable|integer',
            'ativo' => 'boolean',
            'inicio_em' => 'nullable|date',
            'termino_em' => 'nullable|date|after:inicio_em',
        ]);

        // Upload da nova imagem
        if ($request->hasFile('imagem')) {
            // Remove imagem antiga
            if ($banner->imagem && Storage::disk('public')->exists($banner->imagem)) {
                Storage::disk('public')->delete($banner->imagem);
            }
            
            $path = $request->file('imagem')->store('banners', 'public');
            $validated['imagem'] = $path;
        }

        $banner->update($validated);

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner atualizado com sucesso!');
    }

    public function destroy(Banner $banner)
    {
        // Remove imagem
        if ($banner->imagem && Storage::disk('public')->exists($banner->imagem)) {
            Storage::disk('public')->delete($banner->imagem);
        }

        $banner->delete();

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner removido com sucesso!');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'banners' => 'required|array',
            'banners.*' => 'exists:banners,id'
        ]);

        foreach ($request->banners as $index => $id) {
            Banner::where('id', $id)->update(['ordem' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function toggleStatus(Banner $banner)
    {
        $banner->update(['ativo' => !$banner->ativo]);
        
        return redirect()
            ->back()
            ->with('success', 'Status do banner alterado!');
    }
}
<?php
// app/Http/Controllers/Admin/BannerController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BannerRequest;
use App\Models\Banner;
use App\Services\BannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class BannerController extends Controller
{
    public function __construct(
        protected BannerService $bannerService
    ) {}

    /**
     * Listar banners
     */
    public function index(): View
    {
        try {
            $banners = $this->bannerService->listBanners();
            return view('admin.banners.index', compact('banners'));
        } catch (\Exception $e) {
            Log::error('Erro ao listar banners: ' . $e->getMessage());
            return view('admin.banners.index', ['banners' => collect()])
                ->with('error', 'Erro ao carregar banners: ' . $e->getMessage());
        }
    }

    /**
     * Formulário de criação
     */
    public function create(): View
    {
        return view('admin.banners.create');
    }

    /**
     * Salvar novo banner
     */
    public function store(BannerRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            
            // Separar arquivo de imagem
            if ($request->hasFile('imagem')) {
                $data['imagem_file'] = $request->file('imagem');
            }

            $banner = $this->bannerService->createBanner($data);

            return redirect()
                ->route('admin.banners.index')
                ->with('success', 'Banner criado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao criar banner: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao criar banner: ' . $e->getMessage());
        }
    }

    /**
     * Formulário de edição
     */
    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', compact('banner'));
    }

    /**
     * Atualizar banner
     */
    public function update(BannerRequest $request, Banner $banner): RedirectResponse
    {
        try {
            $data = $request->validated();
            
            // Separar arquivo de imagem
            if ($request->hasFile('imagem')) {
                $data['imagem_file'] = $request->file('imagem');
            }

            $this->bannerService->updateBanner($banner, $data);

            return redirect()
                ->route('admin.banners.index')
                ->with('success', 'Banner atualizado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar banner: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar banner: ' . $e->getMessage());
        }
    }

    /**
     * Excluir banner
     */
    public function destroy(Banner $banner): RedirectResponse
    {
        try {
            $this->bannerService->deleteBanner($banner);

            return redirect()
                ->route('admin.banners.index')
                ->with('success', 'Banner removido com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao excluir banner: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Erro ao excluir banner: ' . $e->getMessage());
        }
    }

    /**
     * Reordenar banners (drag-and-drop)
     */
    public function reorder(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'banners' => 'required|array',
                'banners.*' => 'exists:banners,id'
            ]);

            $this->bannerService->reorderBanners($request->banners);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Erro ao reordenar banners: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao reordenar banners: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alternar status do banner
     */
    public function toggleStatus(Banner $banner): RedirectResponse
    {
        try {
            $this->bannerService->toggleStatus($banner);
            
            $status = $banner->ativo ? 'ativado' : 'desativado';
            return redirect()
                ->back()
                ->with('success', "Banner '{$banner->titulo}' foi {$status}!");

        } catch (\Exception $e) {
            Log::error('Erro ao alternar status: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Erro ao alternar status: ' . $e->getMessage());
        }
    }

    /**
     * Visualizar banners ativos (público)
     */
    public function ativos(): JsonResponse
    {
        try {
            $banners = $this->bannerService->listActiveBanners();
            return response()->json([
                'success' => true,
                'data' => $banners
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar banners ativos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar banners'
            ], 500);
        }
    }
}
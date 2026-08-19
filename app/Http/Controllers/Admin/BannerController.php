<?php

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
        $bannersMaxOrder = Banner::max('ordem') ?? 0;
        return view('admin.banners.create', compact('bannersMaxOrder'));
    }

    /**
     * Salvar novo banner
     */
    public function store(BannerRequest $request): RedirectResponse
    {
        try {
            Log::info('📝 INICIANDO CRIAÇÃO DE BANNER', [
                'metodo' => 'store',
                'usuario_id' => auth()->id(),
                'dados' => $request->all()
            ]);

            // Validar dados
            $data = $request->validated();
            Log::info('✅ Dados validados', ['dados' => $data]);

            // Verificar se tem arquivo
            Log::info('📁 Verificando arquivo de imagem', [
                'hasFile' => $request->hasFile('imagem'),
                'isValid' => $request->hasFile('imagem') ? $request->file('imagem')->isValid() : false,
                'error' => $request->hasFile('imagem') ? $request->file('imagem')->getError() : 'N/A'
            ]);

            // Separar arquivo de imagem
            if ($request->hasFile('imagem') && $request->file('imagem')->isValid()) {
                $file = $request->file('imagem');
                Log::info('📄 Arquivo recebido', [
                    'nome_original' => $file->getClientOriginalName(),
                    'tamanho' => $file->getSize(),
                    'extensao' => $file->getClientOriginalExtension(),
                    'mime_type' => $file->getMimeType()
                ]);
                $data['imagem_file'] = $file;
            } else {
                Log::warning('⚠️ Nenhum arquivo de imagem válido recebido');
                if ($request->hasFile('imagem')) {
                    Log::error('❌ Arquivo inválido', [
                        'error_code' => $request->file('imagem')->getError(),
                        'error_message' => $this->getUploadErrorMessage($request->file('imagem')->getError())
                    ]);
                }
            }

            // Remover campos vazios
            $data = array_filter($data, function ($value) {
                return $value !== null && $value !== '';
            });
            Log::info('📊 Dados após filtro', ['dados' => array_keys($data)]);

            // Criar banner
            Log::info('🚀 Criando banner...');
            $banner = $this->bannerService->createBanner($data);
            Log::info('✅ Banner criado com sucesso!', [
                'banner_id' => $banner->id,
                'titulo' => $banner->titulo,
                'imagem' => $banner->imagem
            ]);

            return redirect()
                ->route('admin.banners.index')
                ->with('success', 'Banner criado com sucesso!');

        } catch (\Exception $e) {
            Log::error('❌ ERRO AO CRIAR BANNER', [
                'mensagem' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao criar banner: ' . $e->getMessage());
        }
    }

    /**
     * Visualizar banner individual
     */
    public function show(Banner $banner): View
    {
        return view('admin.banners.show', compact('banner'));
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
            Log::info('📝 INICIANDO ATUALIZAÇÃO DE BANNER', [
                'metodo' => 'update',
                'banner_id' => $banner->id,
                'usuario_id' => auth()->id()
            ]);

            $data = $request->validated();
            Log::info('✅ Dados validados', ['dados' => $data]);

            if ($request->hasFile('imagem') && $request->file('imagem')->isValid()) {
                $file = $request->file('imagem');
                Log::info('📄 Nova imagem recebida', [
                    'nome_original' => $file->getClientOriginalName(),
                    'tamanho' => $file->getSize()
                ]);
                $data['imagem_file'] = $file;
            }

            $data = array_filter($data, function ($value) {
                return $value !== null && $value !== '';
            });

            $this->bannerService->updateBanner($banner, $data);
            Log::info('✅ Banner atualizado com sucesso!', ['banner_id' => $banner->id]);

            return redirect()
                ->route('admin.banners.index')
                ->with('success', 'Banner atualizado com sucesso!');

        } catch (\Exception $e) {
            Log::error('❌ ERRO AO ATUALIZAR BANNER', [
                'mensagem' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine()
            ]);
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
            Log::info('🗑️ EXCLUINDO BANNER', [
                'banner_id' => $banner->id,
                'titulo' => $banner->titulo,
                'usuario_id' => auth()->id()
            ]);

            $this->bannerService->deleteBanner($banner);

            Log::info('✅ Banner excluído com sucesso', ['banner_id' => $banner->id]);

            return redirect()
                ->route('admin.banners.index')
                ->with('success', 'Banner removido com sucesso!');

        } catch (\Exception $e) {
            Log::error('❌ ERRO AO EXCLUIR BANNER', [
                'banner_id' => $banner->id,
                'erro' => $e->getMessage()
            ]);
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
            Log::info('🔄 REORDENANDO BANNERS', [
                'quantidade' => count($request->banners)
            ]);

            $request->validate([
                'banners' => 'required|array',
                'banners.*' => 'exists:banners,id'
            ]);

            $this->bannerService->reorderBanners($request->banners);

            Log::info('✅ Banners reordenados com sucesso');

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('❌ ERRO AO REORDENAR BANNERS', [
                'erro' => $e->getMessage()
            ]);
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
            Log::info('🔄 ALTERANDO STATUS DO BANNER', [
                'banner_id' => $banner->id,
                'status_atual' => $banner->ativo ? 'ativo' : 'inativo'
            ]);

            $this->bannerService->toggleStatus($banner);
            
            $status = $banner->ativo ? 'ativado' : 'desativado';
            $titulo = $banner->titulo ?? 'Banner sem título';
            
            Log::info('✅ Status alterado', [
                'banner_id' => $banner->id,
                'novo_status' => $status
            ]);

            return redirect()
                ->back()
                ->with('success', "Banner '{$titulo}' foi {$status}!");

        } catch (\Exception $e) {
            Log::error('❌ ERRO AO ALTERAR STATUS', [
                'banner_id' => $banner->id,
                'erro' => $e->getMessage()
            ]);
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
            Log::error('❌ ERRO AO BUSCAR BANNERS ATIVOS', [
                'erro' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar banners'
            ], 500);
        }
    }

    /**
     * Obter mensagem de erro de upload
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        $messages = [
            UPLOAD_ERR_OK => 'Arquivo enviado com sucesso.',
            UPLOAD_ERR_INI_SIZE => 'O arquivo excede o tamanho máximo permitido pelo servidor.',
            UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o tamanho máximo permitido pelo formulário.',
            UPLOAD_ERR_PARTIAL => 'O arquivo foi parcialmente enviado.',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado.',
            UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada.',
            UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever o arquivo no disco.',
            UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão PHP.',
        ];

        return $messages[$errorCode] ?? 'Erro desconhecido no upload.';
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriaAdminController extends Controller
{
    /**
     * Lista todas as categorias.
     */
    public function index()
    {
        $categorias = Categoria::withCount('produtos')
            ->orderBy('ordem')
            ->orderBy('nome')
            ->paginate(15);

        return view('admin.categorias.index', compact('categorias'));
    }

    /**
     * Mostra o formulário de criação.
     */
    public function create()
    {
        $categoriasPai = Categoria::whereNull('categoria_pai_id')
            ->orderBy('nome')
            ->get();

        return view('admin.categorias.create', compact('categoriasPai'));
    }

    /**
     * Armazena uma nova categoria.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:100|unique:categorias,nome',
            'slug' => 'nullable|string|max:100|unique:categorias,slug',
            'descricao' => 'nullable|string|max:500',
            'categoria_pai_id' => 'nullable|exists:categorias,id',
            'ordem' => 'nullable|integer|min:0',
            'ativo' => 'nullable|boolean',
        ]);

        $data = $request->all();

        // Gerar slug automaticamente se não for fornecido
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['nome']);
        }

        // Garantir valores padrão
        $data['ativo'] = $request->has('ativo') ? 1 : 0;
        $data['ordem'] = $request->ordem ?? 0;

        $categoria = Categoria::create($data);

        return redirect()
            ->route('admin.categorias.index')
            ->with(
                'success',
                "Categoria '{$categoria->nome}' criada com sucesso!"
            );
    }

    /**
     * Mostra o formulário de edição.
     */
    public function edit(Categoria $categoria)
    {
        $categoriasPai = Categoria::whereNull('categoria_pai_id')
            ->where('id', '!=', $categoria->id)
            ->orderBy('nome')
            ->get();

        return view(
            'admin.categorias.edit',
            compact('categoria', 'categoriasPai')
        );
    }

    /**
     * Atualiza uma categoria.
     */
    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            'nome' => 'required|string|max:100|unique:categorias,nome,' . $categoria->id,
            'slug' => 'nullable|string|max:100|unique:categorias,slug,' . $categoria->id,
            'descricao' => 'nullable|string|max:500',
            'categoria_pai_id' => 'nullable|exists:categorias,id|different:' . $categoria->id,
            'ordem' => 'nullable|integer|min:0',
            'ativo' => 'nullable|boolean',
        ]);

        $data = $request->all();

        // Gerar slug automaticamente se não for fornecido
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['nome']);
        }

        // Garantir valores padrão
        $data['ativo'] = $request->has('ativo') ? 1 : 0;
        $data['ordem'] = $request->ordem ?? 0;

        $categoria->update($data);

        return redirect()
            ->route('admin.categorias.index')
            ->with(
                'success',
                "Categoria '{$categoria->nome}' atualizada com sucesso!"
            );
    }

    /**
     * Remove uma categoria.
     */
    public function destroy(Categoria $categoria)
    {
        $nome = $categoria->nome;

        // Verificar se tem produtos
        if ($categoria->produtos()->count() > 0) {
            return back()->with(
                'error',
                "Não é possível excluir a categoria '{$nome}' pois possui produtos associados."
            );
        }

        // Verificar se tem subcategorias
        if ($categoria->subcategorias()->count() > 0) {
            return back()->with(
                'error',
                "Não é possível excluir a categoria '{$nome}' pois possui subcategorias."
            );
        }

        $categoria->delete();

        return redirect()
            ->route('admin.categorias.index')
            ->with(
                'success',
                "Categoria '{$nome}' excluída com sucesso!"
            );
    }

    /**
     * Alterna o status da categoria.
     */
    public function toggleStatus(Categoria $categoria)
    {
        $categoria->ativo = !$categoria->ativo;
        $categoria->save();

        $status = $categoria->ativo ? 'ativada' : 'desativada';

        return redirect()
            ->route('admin.categorias.index')
            ->with(
                'success',
                "Categoria '{$categoria->nome}' {$status} com sucesso!"
            );
    }

    /**
     * Reordena as categorias.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'ordem' => 'required|array',
            'ordem.*' => 'required|integer|exists:categorias,id',
        ]);

        foreach ($request->ordem as $ordem => $id) {
            Categoria::where('id', $id)->update([
                'ordem' => $ordem + 1,
            ]);
        }

        return response()->json([
            'success' => true,
        ], 200);
    }
}


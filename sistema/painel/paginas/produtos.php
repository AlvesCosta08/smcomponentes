<?php
if (isset($produtos) && $produtos === 'ocultar') {
    header('Location: ../index.php');
    exit();
}

$pag = 'produtos';
?>

<!-- Cabeçalho com layout mais estável -->
<div class="container-fluid mb-4">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div class="d-flex flex-column">
      <h2 class="h3 mb-0 text-primary">
        <i class="fe fe-package me-2"></i>Gerenciar <?php echo ucfirst($pag); ?>
      </h2>
      <p class="text-muted small mb-0">Adicione, edite ou visualize seus produtos</p>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2">
      <button class="btn btn-primary text-white" onclick="inserir()" type="button">
        <i class="fe fe-plus me-1"></i>Adicionar <?php echo ucfirst($pag); ?>
      </button>

      <button class="btn btn-danger d-none" href="#" onclick="deletarSel()" title="Excluir" id="btn-deletar">
        <i class="fe fe-trash-2"></i> Deletar Selecionados
      </button>

      <select class="form-select form-select-sm" name="categoria" id="busca" style="width:300px;" onchange="buscar()">
        <option value="">Buscar por Categoria</option>
        <?php
        $query = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($res as $item) { ?>
          <option value="<?php echo htmlspecialchars($item['id']); ?>"><?php echo htmlspecialchars($item['nome']); ?></option>
        <?php } ?>
      </select>
    </div>
  </div>
</div>

<!-- Área de listagem dinâmica -->
<div class="container-fluid">
  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="bs-example widget-shadow p-3" id="listar"></div>
    </div>
  </div>
</div>

<input type="hidden" id="ids">
<input type="hidden" id="id_produto" value="">

<!-- Modal Inserir -->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h4 class="modal-title"><span id="titulo_inserir"></span></h4>
        <button id="btn-fechar" aria-label="Close" class="btn-close btn-close-white" data-bs-dismiss="modal" type="button"></button>
      </div>
      <form id="form_produtos" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-7">
              <div class="form-group mb-3">
                <label>Nome do Produto *</label>
                <input type="text" class="form-control" id="nome" name="nome" placeholder="Digite o nome do produto" required>
              </div>
            </div>
            <div class="col-md-5">
              <div class="form-group mb-3">
                <label>Categoria *</label>
                <select class="form-select" id="categoria" name="categoria" required>
                  <?php
                  $query = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
                  $res = $query->fetchAll(PDO::FETCH_ASSOC);
                  if (count($res) > 0) {
                    foreach ($res as $item) {
                      echo '<option value="' . htmlspecialchars($item['id']) . '">' . htmlspecialchars($item['nome']) . '</option>';
                    }
                  } else {
                    echo '<option value="0">Cadastre uma Categoria</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
          </div>

          <div class="row mt-2">
            <div class="col-md-6">
              <label>Código de Referência <small>(código original do produto)</small></label>
              <input type="text" class="form-control" id="codigo_referencia" name="codigo_referencia" placeholder="Ex: XYZ-12345">
            </div>
            <div class="col-md-6">
              <label>Descrição do Produto</label>
              <textarea class="form-control" id="descricao" name="descricao" placeholder="Detalhes adicionais"></textarea>
            </div>
          </div>

          <!-- PRECIFICAÇÃO -->
          <div class="row mt-3">
            <div class="col-md-3">
              <div class="form-group mb-3">
                <label>Valor de Compra (R$) *</label>
                <input type="text" class="form-control" id="valor_compra" name="valor_compra" placeholder="0,00" required>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group mb-3">
                <label>IPI (%)</label>
                <input type="number" step="0.01" class="form-control" id="percentual_ipi" name="percentual_ipi" value="0.00" min="0" max="1000">
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group mb-3">
                <label>Custos NFe (%)</label>
                <input type="number" step="0.01" class="form-control" id="percentual_custo_nfe" name="percentual_custo_nfe" value="0.00" min="0" max="1000">
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group mb-3">
                <label>Margem (%) *</label>
                <input type="number" step="0.01" class="form-control" id="percentual_margem_lucro" name="percentual_margem_lucro" value="30.00" required min="0" max="1000">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group mb-3">
                <label>Custo Unitário (R$)</label>
                <input type="text" class="form-control" id="custo_unitario" readonly placeholder="Será calculado">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group mb-3">
                <label>Valor de Venda (R$)</label>
                <input type="text" class="form-control" id="valor_venda_calculado" readonly placeholder="Será calculado">
              </div>
            </div>
          </div>

          <!-- PROMOÇÃO E ESTOQUE -->
          <div class="row mt-2">
            <div class="col-md-3">
              <div class="form-group mb-3">
                <label>Valor Promoção (R$)</label>
                <input type="text" class="form-control" id="val_promocional" name="val_promocional" placeholder="0,00">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group mb-3">
                <label>Alerta Estoque</label>
                <input type="number" class="form-control" id="nivel_estoque" name="nivel_estoque" placeholder="Nível mínimo">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group mb-3">
                <label>Tem Estoque?</label>
                <select class="form-select" id="tem_estoque" name="tem_estoque">
                  <option value="Sim">Sim</option>
                  <option value="Não">Não</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group mb-3">
                <label>Fornecedor</label>
                <select class="form-select" id="fornecedor" name="fornecedor">
                  <option value="">Selecione um Fornecedor</option>
                  <?php
                  $query = $pdo->query("SELECT * FROM fornecedores ORDER BY nome ASC");
                  $res = $query->fetchAll(PDO::FETCH_ASSOC);
                  if (count($res) > 0) {
                    foreach ($res as $item) {
                      echo '<option value="' . htmlspecialchars($item['id']) . '">' . htmlspecialchars($item['nome']) . '</option>';
                    }
                  } else {
                    echo '<option value="">Cadastre um Fornecedor</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
          </div>

          <!-- OUTRAS OPÇÕES -->
          <div class="row mt-2">
            <div class="col-md-3">
              <div class="form-group mb-3">
                <label>Preparado?</label>
                <select class="form-select" id="preparado" name="preparado">
                  <option value="Não">Não</option>
                  <option value="Sim">Sim</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group mb-3">
                <label>Quantidade em Estoque</label>
                <input type="number" class="form-control" id="estoque" name="estoque" value="0">
              </div>
            </div>
          </div>

          <!-- IMAGEM -->
          <div class="row mt-3">
            <div class="col-md-9">
              <div class="form-group mb-3">
                <label>Foto</label>
                <input class="form-control" type="file" name="foto" onChange="carregarImg();" id="foto" accept="image/*">
              </div>
            </div>
            <div class="col-md-3">
              <div id="divImg" class="d-flex justify-content-center">
                <img src="images/produtos/sem-foto.jpg" width="100" id="target" class="rounded shadow-sm">
              </div>
            </div>
          </div>

          <input type="hidden" name="id" id="id" value="">
          <br>
          <div id="mensagem" class="alert alert-info text-center d-none"></div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary" id="btn_salvar">
            Salvar<i class="fa fa-check ms-2"></i>
          </button>
          <button class="btn btn-primary d-none" type="button" id="btn_carregando">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Carregando...
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Dados (visualização) -->
<div class="modal fade" id="modalDados" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h4 class="modal-title" id="exampleModalLabel"><span id="nome_dados"></span></h4>
        <button id="btn-fechar-dados" aria-label="Close" class="btn-close btn-close-white" data-bs-dismiss="modal" type="button"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-8">
            <div class="tile">
              <div class="table-responsive">
                <table class="text-left table table-bordered">
                  <tr><td width="30%" class="bg-primary text-white">Categoria</td><td><span id="categoria_dados"></span></td></tr>
                  <tr><td class="bg-primary text-white">Fornecedor</td><td><span id="fornecedor_dados"></span></td></tr>
                  <tr><td class="bg-primary text-white">Valor Compra</td><td>R$ <span id="valor_compra_dados"></span></td></tr>
                  <tr><td class="bg-primary text-white">IPI (%)</td><td><span id="ipi_dados"></span>%</td></tr>
                  <tr><td class="bg-primary text-white">Custos NFe (%)</td><td><span id="custo_nfe_dados"></span>%</td></tr>
                  <tr><td class="bg-primary text-white">Margem (%)</td><td><span id="margem_dados"></span>%</td></tr>
                  <tr><td class="bg-primary text-white">Custo Unitário</td><td>R$ <span id="custo_unitario_dados"></span></td></tr>
                  <tr><td class="bg-primary text-white">Valor Venda</td><td>R$ <span id="valor_venda_calculado_dados"></span></td></tr>
                  <tr><td class="bg-primary text-white">Valor Promoção</td><td>R$ <span id="val_promocional_dados"></span></td></tr>
                  <tr><td class="bg-primary text-white">Estoque</td><td><span id="estoque_dados"></span></td></tr>
                  <tr><td class="bg-primary text-white">Alerta Estoque</td><td><span id="nivel_estoque_dados"></span></td></tr>
                  <tr><td class="bg-primary text-white">Tem Estoque</td><td><span id="tem_estoque_dados"></span></td></tr>
                  <tr><td class="bg-primary text-white">Preparado</td><td><span id="preparado_dados"></span></td></tr>
                </table>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="tile">
              <div class="table-responsive">
                <table class="text-left table table-bordered">
                  <tr><td align="center"><img src="" id="target_mostrar" width="200px" class="rounded shadow-sm"></td></tr>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="tile">
              <div class="table-responsive">
                <table class="text-left table table-bordered">
                  <tr><td width="15%" class="bg-primary text-white">Código Referência</td><td><span id="codigo_referencia_dados"></span></td></tr>
                  <tr><td class="bg-primary text-white">Descrição</td><td><span id="descricao_dados"></span></td></tr>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Saida -->
<div class="modal fade" id="modalSaida" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h4 class="modal-title" id="exampleModalLabel"><span id="nome_saida"></span></h4>
        <button id="btn-fechar-saida" aria-label="Close" class="btn-close btn-close-white" data-bs-dismiss="modal" type="button"></button>
      </div>

      <div class="modal-body">
        <form id="form-saida">
          <div class="row">
            <div class="col-md-4">
              <div class="form-group mb-3">
                <input type="number" class="form-control" id="quantidade_saida" name="quantidade_saida"
                  placeholder="Quantidade" required>
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group mb-3">
                <input type="text" class="form-control" id="motivo_saida" name="motivo_saida" placeholder="Motivo Saída"
                  required>
              </div>
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
          </div>

          <input type="hidden" id="id_saida" name="id">
          <input type="hidden" id="estoque_saida" name="estoque">
        </form>

        <br>
        <div id="mensagem-saida" class="text-center"></div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Entrada -->
<div class="modal fade" id="modalEntrada" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h4 class="modal-title" id="exampleModalLabel"><span id="nome_entrada"></span></h4>
        <button id="btn-fechar-entrada" aria-label="Close" class="btn-close btn-close-white" data-bs-dismiss="modal" type="button"></button>
      </div>

      <div class="modal-body">
        <form id="form-entrada">
          <div class="row">
            <div class="col-md-4">
              <div class="form-group mb-3">
                <input type="number" class="form-control" id="quantidade_entrada" name="quantidade_entrada"
                  placeholder="Quantidade" required>
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group mb-3">
                <input type="text" class="form-control" id="motivo_entrada" name="motivo_entrada"
                  placeholder="Motivo Entrada" required>
              </div>
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
          </div>

          <input type="hidden" id="id_entrada" name="id">
          <input type="hidden" id="estoque_entrada" name="estoque">
        </form>

        <br>
        <div id="mensagem-entrada" class="text-center"></div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Variações -->
<div class="modal fade" id="modalVariacoes" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h4 class="modal-title" id="exampleModalLabel"><span id="titulo_nome_var"></span></h4>
        <button id="btn-fechar-var" aria-label="Close" class="btn-close btn-close-white" data-bs-dismiss="modal" type="button"></button>
      </div>

      <div class="modal-body">
        <form id="form-var">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label for="exampleInputEmail1">Variação</label>
                <div id="listar_var_cat"></div>
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-group mb-3">
                <label for="exampleInputEmail1">Valor</label>
                <input type="text" class="form-control" id="valor_var" name="valor" placeholder="R$ 50,00" required>
              </div>
            </div>

            <div class="col-md-3" style="margin-top: 25px">
              <button id="btn_var" type="submit" class="btn btn-primary">Salvar</button>
            </div>
          </div>

          <input type="hidden" id="id_var" name="id">
          <input type="hidden" id="id_it_var" name="id_item">
        </form>

        <div id="mensagem-var" class="text-center mt-2"></div>
        <div id="listar-var"></div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Grades -->
<div class="modal fade" id="modalGrades" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h4 class="modal-title" id="exampleModalLabel"><span id="titulo_nome_grades"></span></h4>
        <button id="btn-fechar-grades" aria-label="Close" class="btn-close btn-close-white" data-bs-dismiss="modal" type="button"></button>
      </div>

      <div class="modal-body">
        <form id="form-grades">
          <div class="row">
            <div class="col-md-8">
              <div class="form-group mb-3">
                <label for="exampleInputEmail1">Descrição na hora da compra <small>(Até 70 Caracteres)</small></label>
                <input maxlength="70" type="text" class="form-control" id="texto" name="texto"
                  placeholder="Descrição do item" required="">
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group mb-3">
                <label for="exampleInputEmail1">Tipo Item
                  <div class="icones_mobile" class="dropdown" style="display: inline-block; ">
                    <a title="Clique para ver as Informações" href="#" aria-expanded="false" aria-haspopup="true"
                      data-bs-toggle="dropdown" class="dropdown"><i class="fa fa-info-circle text-primary"></i> </a>
                    <div class="dropdown-menu tx-13" style="width:500px">
                      <div class="dropdown-item-text botao_excluir" style="width:500px">
                        <div class="notification_desc2">
                          <p>
                            <b>Seletor Único</b><br>
                            <span class="text-muted" style="font-size: 12px">Você poderá selecionar apenas uma opção,
                              exemplo, esse produto acompanha uma bebida, selecione a bebida desejada.</span>
                          </p><br>

                          <p>
                            <b>Seletor Múltiplos</b><br>
                            <span class="text-muted" style="font-size: 12px">Você poderá selecionar diversos itens
                              dentro desta grade, exemplo de adicionais, 3 adicionais de bacon, 2 de cheddar,
                              etc.</span>
                          </p><br>

                          <p>
                            <b>Apenas 1 Item de cada</b><br>
                            <span class="text-muted" style="font-size: 12px">Você pode selecionar várias opções porém só
                              poderá inserir 1 item de cada, exemplo remoção de ingredientes, retirar cebola, retirar
                              tomate, etc, será sempre uma unica seleção por cada item.</span>
                          </p><br>

                          <p>
                            <b>Seletor Variação</b><br>
                            <span class="text-muted" style="font-size: 12px">Você poderá selecionar apenas uma opção,
                              exemplo, Tamanho Grande, Médio, etc, será mostrado em locais onde define a variação do
                              produto.</span>
                          </p><br>
                        </div>
                      </div>
                    </div>
                  </div>
                </label>
                <select class="form-select" id="tipo_item" name="tipo_item" style="width:100%;">
                  <option value="Único">Seletor Único</option>
                  <option value="Múltiplo">Seletor Múltiplos</option>
                  <option value="1 de Cada">1 item de Cada</option>
                  <option value="Variação">Variação Produto</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-9">
              <div class="form-group mb-3">
                <label for="exampleInputEmail1">Descrição Comprovante <small>(Até 70 Caracteres)</small></label>
                <input maxlength="70" type="text" class="form-control" id="nome_comprovante" name="nome_comprovante"
                  placeholder="Descrição do item no comprovante" required="">
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-group mb-3">
                <label for="exampleInputEmail1">É Adicional?</label>
                <select class="form-select" id="adicional" name="adicional" style="width:100%;">
                  <option value="Não">Não</option>
                  <option value="Sim">Sim</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-5">
              <div class="form-group mb-3">
                <label for="exampleInputEmail1">Tipo Valor</label>
                <select class="form-select" id="valor_item" name="valor_item" style="width:100%;">
                  <option value="Agregado">Valor Agregado</option>
                  <option value="Único">Valor Único Produto</option>
                  <option value="Produto">Mesmo Valor do Produto</option>
                  <option value="Sem Valor">Sem Valor</option>
                </select>
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group mb-3">
                <label for="exampleInputEmail1">Limite de Seleção Itens</label>
                <input type="number" class="form-control" id="limite" name="limite"
                  placeholder="Selecionar até x Itens">
              </div>
            </div>

            <div class="col-md-2" style="margin-top: 24px">
              <button id="btn_grade" type="submit" class="btn btn-primary">Salvar</button>
            </div>
          </div>

          <input type="hidden" id="id_grades" name="id">
          <input type="hidden" id="id_grade_editar" name="id_grade_editar">
        </form>

        <div id="mensagem-grades" class="text-center mt-2"></div>
        <hr>
        <div id="listar-grades"></div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Itens -->
<div class="modal fade" id="modalItens" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h4 class="modal-title" id="exampleModalLabel"><span id="titulo_nome_itens"></span></h4>
        <button id="btn-fechar-itens" aria-label="Close" class="btn-close btn-close-white" data-bs-dismiss="modal" type="button"></button>
      </div>

      <div class="modal-body">
        <form id="form-itens">
          <div class="row">
            <div class="col-md-12" id="div_nome">
              <div class="form-group mb-3">
                <label for="exampleInputEmail1">Nome <small>(Até 70 Caracteres)</small></label>
                <input maxlength="70" type="text" class="form-control" id="texto_item" name="texto"
                  placeholder="Descrição do item">
              </div>
            </div>

            <div class="col-md-12" id="div_adicional">
              <div class="form-group mb-3">
                <label for="exampleInputEmail1">Escolher Adicional </label>
                <select class="form-select" id="adicional_grade" name="adicional" style="width:100%;"
                  onchange="alterarValor()">
                  <option value="">Selecione um Adicional</option>
                  <?php
                  $query = $pdo->query("SELECT * FROM adicionais ORDER BY nome asc");
                  $res = $query->fetchAll(PDO::FETCH_ASSOC);
                  $total_reg = @count($res);
                  if ($total_reg > 0) {
                    for ($i = 0; $i < $total_reg; $i++) {
                      foreach ($res[$i] as $key => $value) {
                      }
                      echo '<option value="' . htmlspecialchars($res[$i]['id']) . '">' . htmlspecialchars($res[$i]['nome']) . '</option>';
                    }
                  } else {
                    echo '<option value="">Cadastre os Adicionais</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group mb-3">
                <label for="exampleInputEmail1">Valor</label>
                <input type="text" class="form-control" id="valor_do_item" name="valor" placeholder="Valor Se Houver">
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group mb-3">
                <label for="exampleInputEmail1">Limite de Seleção Itens</label>
                <input type="number" class="form-control" id="limite_itens" name="limite"
                  placeholder="Selecionar até x Itens">
              </div>
            </div>

            <div class="col-md-3" style="margin-top: 24px">
              <button id="btn_itens" type="submit" class="btn btn-primary">Salvar</button>
            </div>
          </div>

          <input type="hidden" id="id_item" name="id">
          <input type="hidden" id="id_item_produto" name="id_item_produto">
          <input type="hidden" id="e_adicional" name="e_adicional">
          <input type="hidden" id="id_item_editar" name="id_item_editar">
        </form>

        <div id="mensagem-itens" class="text-center mt-2"></div>
        <hr>
        <div id="listar-itens"></div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  var pag = "<?= $pag ?>"
</script>
<script src="js/ajax.js"></script>

<script>
  $(document).ready(function() {
    // Inicializar Select2
    $('.sel7').select2();
    $('.sel3').select2({ dropdownParent: $('#modalForm') });
    $('.sel5').select2({ dropdownParent: $('#modalItens') });
    
    // Carregar produtos ao abrir a página
    buscar();
    
    // Configurar submissão dos formulários de entrada/saída
    $('#form-saida').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        
        $.ajax({
            url: 'paginas/' + pag + '/saida.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#mensagem-saida').html('<div class="spinner-border spinner-border-sm" role="status"></div> Processando...').removeClass('text-danger').addClass('text-info');
            },
            success: function(mensagem) {
                if (mensagem.trim() == "Salvo com Sucesso") {
                    $('#mensagem-saida').html('<span class="text-success"><i class="fa fa-check"></i> ' + mensagem + '</span>');
                    setTimeout(function() {
                        $('#btn-fechar-saida').click();
                        buscar();
                        $('#mensagem-saida').html('');
                    }, 1500);
                } else {
                    $('#mensagem-saida').html('<span class="text-danger"><i class="fa fa-times"></i> ' + mensagem + '</span>');
                }
            },
            error: function(xhr, status, error) {
                $('#mensagem-saida').html('<span class="text-danger"><i class="fa fa-times"></i> Erro: ' + error + '</span>');
            }
        });
    });
    
    $('#form-entrada').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        
        $.ajax({
            url: 'paginas/' + pag + '/entrada.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#mensagem-entrada').html('<div class="spinner-border spinner-border-sm" role="status"></div> Processando...').removeClass('text-danger').addClass('text-info');
            },
            success: function(mensagem) {
                if (mensagem.trim() == "Salvo com Sucesso") {
                    $('#mensagem-entrada').html('<span class="text-success"><i class="fa fa-check"></i> ' + mensagem + '</span>');
                    setTimeout(function() {
                        $('#btn-fechar-entrada').click();
                        buscar();
                        $('#mensagem-entrada').html('');
                    }, 1500);
                } else {
                    $('#mensagem-entrada').html('<span class="text-danger"><i class="fa fa-times"></i> ' + mensagem + '</span>');
                }
            },
            error: function(xhr, status, error) {
                $('#mensagem-entrada').html('<span class="text-danger"><i class="fa fa-times"></i> Erro: ' + error + '</span>');
            }
        });
    });

    // Função para buscar produto ao editar via link
    $(document).on('click', '[data-editar]', function(e) {
        e.preventDefault();
        
        var id = $(this).data('id');
        var nome = $(this).data('nome');
        var categoria = $(this).data('categoria');
        var descricao = $('#descricao_' + id).val();
        var codigo_referencia = $('#codigo_referencia_' + id).val();
        var valor_compra = $(this).data('valor-compra');
        var foto = $(this).data('foto');
        var nivel_estoque = $(this).data('nivel-estoque');
        var tem_estoque = $(this).data('tem-estoque');
        var preparado = $(this).data('preparado');
        var val_promocional = $(this).data('val-promocional');
        var ipi_percentual = $('#percentual_ipi_' + id).val();
        var custo_nfe_percentual = $('#percentual_custo_nfe_' + id).val();
        var margem_lucro_percentual = $('#percentual_margem_lucro_' + id).val();
        var fornecedor = $('#fornecedor_id_' + id).val();
        var estoque = $(this).closest('tr').find('td:nth-child(5)').text().trim();
        
        editar(id, nome, categoria, descricao, codigo_referencia, valor_compra, 
               ipi_percentual, custo_nfe_percentual, margem_lucro_percentual, 
               val_promocional, nivel_estoque, estoque, tem_estoque, 
               fornecedor, preparado, foto);
    });
  });
</script>

<script type="text/javascript">
  function carregarImg() {
    var target = document.getElementById('target');
    var file = document.querySelector("#foto").files[0];
    var reader = new FileReader();
    reader.onloadend = function() { 
        target.src = reader.result; 
    };
    if (file) { 
        reader.readAsDataURL(file); 
    } else { 
        target.src = "images/produtos/sem-foto.jpg"; 
    }
  }
</script>

<script type="text/javascript">
  // ==============================================
  // FUNÇÕES CORRIGIDAS PARA AÇÕES NA TABELA
  // ==============================================
  
  // Função para seleção múltipla - VERSÃO CORRIGIDA
  function selecionar(id) {
      console.log("🔘 Selecionar chamado para ID:", id);
      
      // Obter o checkbox
      var checkbox = $('#seletor-' + id);
      var isChecked = checkbox.prop('checked');
      console.log("Checkbox marcado?", isChecked);
      
      // Obter valores atuais
      var ids_atual = $('#ids').val();
      console.log("IDs atuais (antes):", ids_atual);
      
      // Converter string em array (remover vazios)
      var ids_array = [];
      if (ids_atual !== '' && ids_atual !== '0') {
          ids_array = ids_atual.split(',').filter(function(item) {
              return item !== '' && item !== '0';
          });
      }
      console.log("IDs array (antes):", ids_array);
      
      if (isChecked) {
          // Adicionar ID ao array se não existir
          if (!ids_array.includes(id.toString())) {
              ids_array.push(id.toString());
              console.log("✅ Adicionado ID:", id);
          }
      } else {
          // Remover ID do array
          ids_array = ids_array.filter(function(item) {
              return item !== id.toString();
          });
          console.log("❌ Removido ID:", id);
      }
      
      // Atualizar campo hidden
      var novo_valor = ids_array.join(',');
      $('#ids').val(novo_valor);
      console.log("IDs novos (depois):", novo_valor);
      
      // Mostrar/ocultar botão deletar selecionados
      if (ids_array.length > 0) {
          $('#btn-deletar').removeClass('d-none');
          console.log("📌 Mostrando botão deletar - Total selecionados:", ids_array.length);
      } else {
          $('#btn-deletar').addClass('d-none');
          console.log("📌 Ocultando botão deletar");
      }
      
      // Atualizar checkbox "Selecionar Todos"
      atualizarSelecionarTodos();
  }
  
  // Função para atualizar o estado do checkbox "Selecionar Todos"
  function atualizarSelecionarTodos() {
      var totalCheckboxes = $('input[type="checkbox"][id^="seletor-"]').length;
      var totalSelecionados = $('input[type="checkbox"][id^="seletor-"]:checked').length;
      
      var selecionarTodosCheckbox = $('#selecionar-todos');
      if (selecionarTodosCheckbox.length > 0) {
          selecionarTodosCheckbox.prop('checked', totalSelecionados === totalCheckboxes);
          selecionarTodosCheckbox.prop('indeterminate', totalSelecionados > 0 && totalSelecionados < totalCheckboxes);
      }
  }
  
  // Função para selecionar/deselecionar todos os produtos
  function selecionarTodos() {
      var selecionarTodosCheckbox = $('#selecionar-todos');
      var isChecked = selecionarTodosCheckbox.prop('checked');
      
      console.log("🔘 'Selecionar Todos' clicado:", isChecked);
      
      // Array para armazenar todos os IDs
      var todosIds = [];
      
      // Percorrer todos os checkboxes individuais
      $('input[type="checkbox"][id^="seletor-"]').each(function() {
          var checkboxId = $(this).attr('id');
          var id = checkboxId.replace('seletor-', '');
          
          $(this).prop('checked', isChecked);
          
          if (isChecked) {
              todosIds.push(id);
          }
      });
      
      // Atualizar campo hidden
      if (isChecked) {
          $('#ids').val(todosIds.join(','));
          $('#btn-deletar').removeClass('d-none');
          console.log("✅ Todos selecionados - Total:", todosIds.length);
      } else {
          $('#ids').val('');
          $('#btn-deletar').addClass('d-none');
          console.log("❌ Nenhum selecionado");
      }
  }
  
  // Adicionar checkbox "Selecionar Todos" à tabela
  function adicionarSelecionarTodos() {
      // Verificar se já existe o checkbox "Selecionar Todos"
      if ($('#selecionar-todos').length === 0) {
          // Adicionar o checkbox no cabeçalho da tabela
          var thSelecionar = $('thead tr th:first-child');
          if (thSelecionar.length > 0 && !thSelecionar.find('#selecionar-todos').length) {
              thSelecionar.html('<input type="checkbox" id="selecionar-todos" onclick="selecionarTodos()" title="Selecionar Todos">');
              
              // Adicionar evento de clique
              $('#selecionar-todos').on('click', function() {
                  selecionarTodos();
              });
          }
      }
  }
  
  // Função para deletar selecionados - VERSÃO CORRIGIDA
  function deletarSel() {
      var ids = $('#ids').val();
      
      // Limpar IDs vazios
      var ids_array = ids.split(',').filter(function(id) {
          return id !== '' && id !== '0';
      });
      
      if (ids_array.length === 0) {
          mostrarMensagem('Selecione pelo menos um produto!', 'warning');
          return;
      }
      
      var cont = ids_array.length;
      var ids_limpos = ids_array.join(',');
      
      // Obter nomes dos produtos para mensagem
      var nomes_produtos = [];
      ids_array.forEach(function(id) {
          var nome = '';
          var linha = $('#seletor-' + id).closest('tr');
          if (linha.length > 0) {
              nome = linha.find('td:nth-child(2)').text().replace('(Promoção)', '').trim();
          }
          nomes_produtos.push(nome || 'Produto ' + id);
      });
      
      var mensagem_confirmacao = 'Deseja realmente excluir os ' + cont + ' produtos selecionados?\n\n';
      mensagem_confirmacao += nomes_produtos.slice(0, 5).join('\n');
      if (nomes_produtos.length > 5) {
          mensagem_confirmacao += '\n... e mais ' + (nomes_produtos.length - 5) + ' produtos';
      }
      
      if (confirm(mensagem_confirmacao)) {
          $.ajax({
              url: 'paginas/' + pag + "/excluir-sel.php",
              method: 'POST',
              data: { ids: ids_limpos },
              dataType: "text",
              beforeSend: function() {
                  $('#btn-deletar').html('<i class="fe fe-trash-2"></i> Excluindo...').prop('disabled', true);
              },
              success: function(mensagem) {
                  console.log("Resposta exclusão múltipla:", mensagem);
                  if (mensagem.trim() === "Excluído com Sucesso") {
                      buscar();
                      $('#ids').val('');
                      $('#btn-deletar').addClass('d-none');
                      mostrarMensagem('Produtos excluídos com sucesso!', 'success');
                  } else {
                      mostrarMensagem('Erro: ' + mensagem, 'danger');
                  }
                  $('#btn-deletar').html('<i class="fe fe-trash-2"></i> Deletar Selecionados').prop('disabled', false);
              },
              error: function(xhr, status, error) {
                  console.error("Erro exclusão múltipla:", error);
                  mostrarMensagem('Erro: ' + error, 'danger');
                  $('#btn-deletar').html('<i class="fe fe-trash-2"></i> Deletar Selecionados').prop('disabled', false);
              }
          });
      }
  }
  
  // Função para ativar/desativar produto
  function ativar(id, acao) {
      var acaoTexto = acao == '1' ? 'ativar' : 'desativar';
      
      // Obter o nome do produto da linha da tabela
      var linha = $('#seletor-' + id).closest('tr');
      var nomeProduto = linha.find('td:nth-child(2)').text().replace('(Promoção)', '').trim();
      
      if (confirm('Deseja ' + acaoTexto + ' o produto "' + nomeProduto + '"?')) {
          $.ajax({
              url: 'paginas/' + pag + "/mudar-status.php",
              method: 'POST',
              data: { 
                  id: id, 
                  acao: acao 
              },
              dataType: "text",
              beforeSend: function() {
                  // Mostrar indicador de carregamento
                  var btn = $('a[onclick*="ativar(\'' + id + '\'"]');
                  btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
              },
              success: function(mensagem) {
                  console.log("Resposta mudar-status:", mensagem);
                  if (mensagem.trim() == "Alterado com Sucesso") {
                      buscar();
                      mostrarMensagem('Produto ' + acaoTexto + 'do com sucesso!', 'success');
                  } else {
                      mostrarMensagem('Erro: ' + mensagem, 'danger');
                  }
              },
              error: function(xhr, status, error) {
                  console.error("Erro ativar:", error);
                  mostrarMensagem('Erro: ' + error, 'danger');
              }
          });
      }
  }
  
  // Função para mostrar mensagens
  function mostrarMensagem(texto, tipo) {
      // Criar elemento de mensagem
      var mensagemDiv = $('<div class="alert alert-' + tipo + ' alert-dismissible fade show" style="position: fixed; top: 70px; right: 20px; z-index: 9999; min-width: 300px;"></div>')
          .html('<button type="button" class="btn-close" data-bs-dismiss="alert"></button><i class="fa fa-info-circle me-2"></i>' + texto);
      
      // Adicionar ao corpo
      $('body').append(mensagemDiv);
      
      // Auto-remover após 3 segundos
      setTimeout(function() {
          mensagemDiv.alert('close');
          setTimeout(function() {
              mensagemDiv.remove();
          }, 500);
      }, 3000);
  }
  
  // Função para excluir produto
  function excluir(id) {
      console.log("🔄 Função excluir chamada com ID:", id);
      
      // Verificar se o ID é válido
      if (!id || id === '' || id === 'undefined') {
          console.error("❌ ID inválido:", id);
          mostrarMensagem('Erro: ID do produto inválido', 'danger');
          return;
      }
      
      // Obter o nome do produto
      var linha = $('#seletor-' + id).closest('tr');
      if (linha.length === 0) {
          console.error("❌ Linha da tabela não encontrada para ID:", id);
          mostrarMensagem('Erro: Produto não encontrado na tabela', 'danger');
          return;
      }
      
      var nomeProduto = linha.find('td:nth-child(2)').text().replace('(Promoção)', '').trim();
      console.log("Nome do produto:", nomeProduto);
      
      if (confirm('Deseja realmente excluir o produto "' + nomeProduto + '"?')) {
          console.log("✅ Usuário confirmou exclusão");
          
          // Mostrar loading
          var btn = $('a[onclick*="excluir(\'' + id + '\'"]');
          var originalHtml = btn.html();
          btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
          
          // Enviar como FormData para garantir o envio correto
          var formData = new FormData();
          formData.append('id', id);
          formData.append('nome', nomeProduto);
          
          $.ajax({
              url: 'paginas/' + pag + "/excluir-item.php",
              method: 'POST',
              data: formData,
              processData: false,
              contentType: false,
              dataType: "text",
              success: function(mensagem) {
                  console.log("✅ Resposta do servidor:", mensagem);
                  console.log("Resposta trim:", mensagem.trim());
                  
                  if (mensagem.trim() === "Excluído com Sucesso") {
                      // Remover a linha da tabela imediatamente
                      linha.fadeOut(300, function() {
                          $(this).remove();
                          // Atualizar contador
                          var total = $('#tabela tbody tr').length;
                          $('.alert-info strong').text(total);
                      });
                      
                      mostrarMensagem('Produto excluído com sucesso!', 'success');
                  } else {
                      mostrarMensagem('Erro: ' + mensagem, 'danger');
                  }
                  
                  // Restaurar botão
                  btn.html(originalHtml).prop('disabled', false);
              },
              error: function(xhr, status, error) {
                  console.error("❌ Erro AJAX completo:", error);
                  mostrarMensagem('Erro na conexão: ' + error, 'danger');
                  
                  // Restaurar botão
                  btn.html(originalHtml).prop('disabled', false);
              }
          });
      } else {
          console.log("❌ Usuário cancelou exclusão");
      }
  }
  
  // Função para listar produtos
  function listar(busca = '') {
      $.ajax({
          url: 'paginas/' + pag + "/listar.php",
          method: 'POST',
          data: { busca: busca },
          dataType: "html",
          beforeSend: function() {
              // Mostrar loader
              $("#listar").html(`
                  <div class="text-center py-5">
                      <div class="spinner-border text-primary" role="status">
                          <span class="visually-hidden">Carregando...</span>
                      </div>
                      <p class="text-muted mt-2">Carregando produtos...</p>
                  </div>
              `);
          },
          success: function(result) {
              $("#listar").html(result);
              
              // Adicionar checkbox "Selecionar Todos" se não existir
              setTimeout(function() {
                  adicionarSelecionarTodos();
              }, 100);
              
              // Re-inicializar Select2 se necessário
              if ($('.sel7').length > 0) {
                  $('.sel7').select2();
              }
          },
          error: function(xhr, status, error) {
              $("#listar").html(`
                  <div class="alert alert-danger">
                      <i class="fa fa-exclamation-triangle"></i> 
                      Erro ao carregar produtos: ${error}
                      <button class="btn btn-sm btn-outline-primary mt-2" onclick="buscar()">
                          Tentar novamente
                      </button>
                  </div>
              `);
          }
      });
  }
  
  // Função buscar
  function buscar() { 
      var busca = $('#busca').val(); 
      listar(busca); 
      // Resetar seleção múltipla
      $('#ids').val('');
      $('#btn-deletar').addClass('d-none');
  }
  
  // Event delegation para checkboxes dinâmicos
  $(document).on('change', 'input[type="checkbox"][id^="seletor-"]', function() {
      var id = $(this).attr('id').replace('seletor-', '');
      selecionar(id);
  });
</script>

<script type="text/javascript">
  // ✅ FUNÇÃO MELHORADA DE CÁLCULO
  function calcularPrecos() {
      function parseBRL(value) {
          if (!value) return 0;
          // Remove R$, pontos e converte vírgula para ponto
          return parseFloat(value.toString()
              .replace('R$', '')
              .replace(/\./g, '')
              .replace(',', '.')
              .trim()) || 0;
      }

      const vc = parseBRL($('#valor_compra').val());
      const ipi = parseFloat($('#percentual_ipi').val()) || 0;
      const custo = parseFloat($('#percentual_custo_nfe').val()) || 0;
      const margem = parseFloat($('#percentual_margem_lucro').val()) || 0;

      const custoIpi = vc * (ipi / 100);
      const custoNFe = vc * (custo / 100);
      const custoUnitario = vc + custoIpi + custoNFe;
      const valorVenda = custoUnitario * (1 + margem / 100);

      // Formatar para exibição
      const formatter = new Intl.NumberFormat('pt-BR', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
      });

      $('#custo_unitario').val(formatter.format(custoUnitario));
      $('#valor_venda_calculado').val(formatter.format(valorVenda));
      
      // Atualizar valor_compra formatado
      if (vc > 0) {
          $('#valor_compra').val(formatter.format(vc));
      }
  }

  // ✅ EVENTO PARA FORMATAÇÃO AUTOMÁTICA
  $(document).on('blur', '#valor_compra, #val_promocional', function() {
      let value = $(this).val();
      if (value) {
          let num = parseFloat(value.replace(/\./g, '').replace(',', '.'));
          if (!isNaN(num)) {
              $(this).val(num.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
          }
      }
  });

  // ✅ ATUALIZAR CÁLCULO EM TEMPO REAL
  $('#valor_compra, #percentual_ipi, #percentual_custo_nfe, #percentual_margem_lucro').on('input', function() {
      setTimeout(calcularPrecos, 100);
  });
</script>

<!-- SCRIPT PRINCIPAL DO FORMULÁRIO -->
<script type="text/javascript">
  $(document).ready(function() {
    // Inicializar cálculo
    setTimeout(calcularPrecos, 500);
    
    // Configurar envio do formulário
    $("#form_produtos").submit(function(event) {
        event.preventDefault();
        
        console.log("=== DEBUG: Dados do formulário ===");
        console.log("ID do produto:", $('#id').val());
        console.log("Nome do produto:", $('#nome').val());
        console.log("=== FIM DEBUG ===");
        
        var formData = new FormData(this);

        // Validação de percentuais
        const margem = parseFloat(document.getElementById('percentual_margem_lucro').value) || 0;
        const custo_nfe = parseFloat(document.getElementById('percentual_custo_nfe').value) || 0;
        const ipi = parseFloat(document.getElementById('percentual_ipi').value) || 0;

        if (margem > 1000 || custo_nfe > 1000 || ipi > 1000) {
          $('#mensagem').text('Erro: Percentuais não podem ultrapassar 1000%').removeClass().addClass('alert alert-danger').removeClass('d-none');
          return;
        }

        // Validar nome
        const nome = $('#nome').val().trim();
        if (!nome) {
          $('#mensagem').text('Erro: O nome do produto é obrigatório').removeClass().addClass('alert alert-danger').removeClass('d-none');
          return;
        }

        // Validar valor de compra
        const valor_compra = $('#valor_compra').val();
        if (!valor_compra || parseFloat(valor_compra.replace(',', '.')) <= 0) {
          $('#mensagem').text('Erro: O valor de compra deve ser maior que zero').removeClass().addClass('alert alert-danger').removeClass('d-none');
          return;
        }

        $('#mensagem').text('Salvando...').removeClass().addClass('alert alert-info').removeClass('d-none');
        $('#btn_salvar').hide();
        $('#btn_carregando').removeClass('d-none');

        $.ajax({
          url: 'paginas/' + pag + '/salvar.php',
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(mensagem) {
            console.log("Resposta do servidor:", mensagem);
            $('#mensagem').removeClass();
            if (mensagem.trim() === "Salvo com Sucesso") {
              $('#mensagem').addClass('alert alert-success').text(mensagem);
              setTimeout(function() {
                $('#modalForm').modal('hide');
                buscar();
                $('#mensagem').addClass('d-none').text('');
              }, 1500);
            } else {
              $('#mensagem').addClass('alert alert-danger').text(mensagem);
            }
            $('#btn_salvar').show();
            $('#btn_carregando').addClass('d-none');
          },
          error: function(xhr, status, error) {
            console.error('Erro AJAX:', error);
            $('#mensagem').addClass('alert alert-danger').text('Erro na requisição: ' + error);
            $('#btn_salvar').show();
            $('#btn_carregando').addClass('d-none');
          }
        });
    });
  });

  // Função para abrir modal de inserção
  function inserir() {
    // Limpar o formulário
    $('#form_produtos')[0].reset();
    $('#id').val(''); // ✅ GARANTIR QUE O ID SEJA LIMPO
    $('#id_produto').val('');
    $('#target').attr('src', 'images/produtos/sem-foto.jpg');
    $('#titulo_inserir').text('Novo Produto');
    
    // Resetar valores calculados
    $('#custo_unitario').val('');
    $('#valor_venda_calculado').val('');
    
    // Resetar valores padrão
    $('#percentual_margem_lucro').val('30.00');
    $('#percentual_ipi').val('0.00');
    $('#percentual_custo_nfe').val('0.00');
    $('#tem_estoque').val('Sim');
    $('#preparado').val('Não');
    $('#estoque').val('0');
    $('#fornecedor').val('');
    $('#nivel_estoque').val('0');
    $('#val_promocional').val('');
    
    // Limpar mensagens
    $('#mensagem').addClass('d-none').text('');
    
    // Mostrar modal
    var myModal = new bootstrap.Modal(document.getElementById('modalForm'));
    myModal.show();
    
    // Calcular preços
    setTimeout(calcularPrecos, 500);
  }

  // ✅ CORREÇÃO: Função editar
  function editar(id, nome, categoria, descricao, codigo_referencia, valor_compra, 
                ipi_percentual, custo_nfe_percentual, margem_lucro_percentual, 
                valor_promocional, nivel_estoque, estoque, tem_estoque, 
                fornecedor, preparado, foto) {
    
    console.log("🚀 Editando produto ID:", id);
    
    // ✅ GARANTIR QUE O ID SEJA PREENCHIDO CORRETAMENTE
    $('#id').val(id);
    $('#id_produto').val(id); // Backup
    
    // Converter valores boolean para string
    var tem_estoque_text = (tem_estoque == 1 || tem_estoque === '1' || tem_estoque === 'Sim') ? 'Sim' : 'Não';
    var preparado_text = (preparado == 1 || preparado === '1' || preparado === 'Sim') ? 'Sim' : 'Não';
    
    // Preencher formulário
    $('#nome').val(nome || '');
    $('#categoria').val(categoria || '');
    $('#descricao').val(descricao || '');
    $('#codigo_referencia').val(codigo_referencia || '');
    
    // ✅ CORRIGIR: Formatar valor_compra corretamente
    var valor_compra_num = parseFloat(valor_compra) || 0;
    $('#valor_compra').val(valor_compra_num.toLocaleString('pt-BR', {minimumFractionDigits: 2}));
    
    // Percentuais
    $('#percentual_ipi').val(ipi_percentual || '0.00');
    $('#percentual_custo_nfe').val(custo_nfe_percentual || '0.00');
    $('#percentual_margem_lucro').val(margem_lucro_percentual || '30.00');
    
    // ✅ CORRIGIR: Formatar valor_promocional
    var valor_promocional_num = parseFloat(valor_promocional) || 0;
    $('#val_promocional').val(valor_promocional_num > 0 ? 
        valor_promocional_num.toLocaleString('pt-BR', {minimumFractionDigits: 2}) : '');
    
    // Estoque
    $('#nivel_estoque').val(nivel_estoque || '0');
    $('#estoque').val(estoque || '0');
    $('#tem_estoque').val(tem_estoque_text);
    $('#preparado').val(preparado_text);
    $('#fornecedor').val(fornecedor || '');
    
    // Foto
    if (foto && foto !== 'sem-foto.jpg' && foto !== '') {
        $('#target').attr('src', 'images/produtos/' + foto);
    } else {
        $('#target').attr('src', 'images/produtos/sem-foto.jpg');
    }
    
    // Limpar mensagens
    $('#mensagem').addClass('d-none').text('');
    
    // Calcular automaticamente
    setTimeout(function() {
        calcularPrecos();
        
        // ✅ ATUALIZAR TÍTULO CORRETAMENTE
        $('#titulo_inserir').text('Editar Produto: ' + nome);
        
        // Mostrar modal
        var myModal = new bootstrap.Modal(document.getElementById('modalForm'));
        myModal.show();
    }, 300);
  }

  // ✅ CORREÇÃO: Função dados
  function dados(id, nome, categoria, valor_compraF, valor_vendaF, estoque, foto, 
                nivel_estoque, tem_estoque, preparado, valor_promocionalF, 
                ipi_percentual, custo_nfe_percentual, margem_lucro_percentual, codigo_referencia) {
    
    // Buscar descrição e fornecedor do DOM
    var descricao = $('#descricao_' + id).val() || '';
    var fornecedor_id = $('#fornecedor_id_' + id).val() || '';
    
    // Buscar nome do fornecedor via AJAX
    $.ajax({
        url: 'paginas/produtos/buscar-fornecedor.php',
        method: 'POST',
        data: { id: fornecedor_id },
        async: false,
        success: function(fornecedor_nome) {
            if (fornecedor_nome && fornecedor_nome !== '') {
                $('#fornecedor_dados').text(fornecedor_nome);
            } else {
                $('#fornecedor_dados').text('---');
            }
        },
        error: function() {
            $('#fornecedor_dados').text('---');
        }
    });
    
    // Calcular custo unitário
    var valor_compra_num = parseFloat(valor_compraF.replace('R$ ', '').replace('.', '').replace(',', '.')) || 0;
    var ipi_num = parseFloat(ipi_percentual) || 0;
    var custo_nfe_num = parseFloat(custo_nfe_percentual) || 0;
    var custo_unitario = valor_compra_num + (valor_compra_num * (ipi_num / 100)) + (valor_compra_num * (custo_nfe_num / 100));
    
    // Preencher dados
    $('#nome_dados').text(nome);
    $('#categoria_dados').text(categoria);
    $('#descricao_dados').text(descricao || '---');
    $('#codigo_referencia_dados').text(codigo_referencia || '---');
    $('#valor_compra_dados').text(valor_compraF ? valor_compraF.replace('R$ ', '') : '0,00');
    $('#ipi_dados').text(ipi_percentual || '0.00');
    $('#custo_nfe_dados').text(custo_nfe_percentual || '0.00');
    $('#margem_dados').text(margem_lucro_percentual || '30.00');
    $('#custo_unitario_dados').text(custo_unitario.toLocaleString('pt-BR', {minimumFractionDigits: 2}));
    $('#valor_venda_calculado_dados').text(valor_vendaF ? valor_vendaF.replace('R$ ', '') : '0,00');
    $('#val_promocional_dados').text(valor_promocionalF ? valor_promocionalF.replace('R$ ', '') : '0,00');
    $('#nivel_estoque_dados').text(nivel_estoque || '0');
    $('#estoque_dados').text(estoque || '0');
    $('#tem_estoque_dados').text(tem_estoque || 'Sim');
    $('#preparado_dados').text(preparado || 'Não');
    
    // Foto
    if (foto && foto !== 'sem-foto.jpg') {
        $('#target_mostrar').attr('src', 'images/produtos/' + foto);
    } else {
        $('#target_mostrar').attr('src', 'images/produtos/sem-foto.jpg');
    }
    
    // Mostrar modal
    var myModal = new bootstrap.Modal(document.getElementById('modalDados'));
    myModal.show();
  }
</script>

<!-- FUNÇÕES PARA MODAIS -->
<script type="text/javascript">
  function abrirSaida(id, nome, estoque) {
    $('#id_saida').val(id);
    $('#nome_saida').text(nome);
    $('#estoque_saida').val(estoque);
    $('#quantidade_saida').val('');
    $('#motivo_saida').val('');
    $('#mensagem-saida').html('');
    
    var myModal = new bootstrap.Modal(document.getElementById('modalSaida'));
    myModal.show();
  }

  function abrirEntrada(id, nome, estoque) {
    $('#id_entrada').val(id);
    $('#nome_entrada').text(nome);
    $('#estoque_entrada').val(estoque);
    $('#quantidade_entrada').val('');
    $('#motivo_entrada').val('');
    $('#mensagem-entrada').html('');
    
    var myModal = new bootstrap.Modal(document.getElementById('modalEntrada'));
    myModal.show();
  }

  // Funções auxiliares para compatibilidade
  function saida(id, nome, estoque) {
    abrirSaida(id, nome, estoque);
  }

  function entrada(id, nome, estoque) {
    abrirEntrada(id, nome, estoque);
  }

  function grades(id, nome, categoria) {
    alert('Funcionalidade de Grades em desenvolvimento para: ' + nome);
  }

  function variacoes(id, nome, categoria) {
    alert('Funcionalidade de Variações em desenvolvimento para: ' + nome);
  }
</script>
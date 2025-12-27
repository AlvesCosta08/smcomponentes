<?php 
require_once("../../../conexao.php");
$tabela = 'marketing';

$query = $pdo->query("SELECT * FROM clientes where telefone != '' and telefone is not null");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_clientes = @count($res);

$query = $pdo->query("SELECT * FROM $tabela ORDER BY id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML
	<small>
	<table class="table table-hover table-bordered text-nowrap border-bottom dt-responsive" id="tabela">
	<thead> 
	<tr> 
	<th>Título</th>	
	<th class="esc">Útimo Envio</th> 
	<th class="esc">Tipo Envio</th> 	
	<th class="esc">Envios Pendentes</th> 	
	<th class="esc">Imagem</th> 
	<th class="esc">Áudio</th>	
	<th class="esc">Arquivo</th>	
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
	$id = $res[$i]['id'];
	$titulo = $res[$i]['titulo'];	
	$mensagem = $res[$i]['mensagem'];
	$item1 = $res[$i]['item1'];
	$item2 = $res[$i]['item2'];
	$item3 = $res[$i]['item3'];
	$item4 = $res[$i]['item4'];
	$item5 = $res[$i]['item5'];
	$item6 = $res[$i]['item6'];
	$item7 = $res[$i]['item7'];
	$item8 = $res[$i]['item8'];

	$item9 = $res[$i]['item9'];
	$item10 = $res[$i]['item10'];
	$item11 = $res[$i]['item11'];
	$item12 = $res[$i]['item12'];
	$item13 = $res[$i]['item13'];
	$item14 = $res[$i]['item14'];
	$item15 = $res[$i]['item15'];
	$item16 = $res[$i]['item16'];
	$item17 = $res[$i]['item17'];
	$item18 = $res[$i]['item18'];
	$item19 = $res[$i]['item19'];
	$item20 = $res[$i]['item20'];

	$conclusao = $res[$i]['conclusao'];
	$conclusao2 = $res[$i]['conclusao2'];
	$conclusao3 = $res[$i]['conclusao3'];
	$conclusao4 = $res[$i]['conclusao4'];
	$arquivo = $res[$i]['arquivo'];
	$audio = $res[$i]['audio'];
	$data_envio = $res[$i]['data_envio'];
	$data = $res[$i]['data'];
	$envios = $res[$i]['envios'];
	$forma_envio = $res[$i]['forma_envio'];	

	$link = $res[$i]['link'];
	$video = $res[$i]['video'];

	$link2 = $res[$i]['link2'];
	$conclusao5 = $res[$i]['conclusao5'];

	$documento = $res[$i]['documento'];


	$data_envioF = implode('/', array_reverse(@explode('-', $data_envio)));
	$dataF = implode('/', array_reverse(@explode('-', $data)));

	$ocultar_audio = 'ocultar';
	if($audio != ""){
		$ocultar_audio = '';
	}

	$ocultar_foto = 'ocultar';
	if($arquivo != "sem-foto.jpg"){
		$ocultar_foto = '';
	}

	if($forma_envio == ""){
		$forma_envio = "Todos";
	}

	$ocultar_doc = 'ocultar';
	if($documento != "sem-foto.jpg"){
		$ocultar_doc = '';
	}

	$ocultar_reg = '';
	 $tituloF = mb_strimwidth($titulo, 0, 18, "...");


		//extensão do arquivo
$ext = pathinfo($documento, PATHINFO_EXTENSION);
if($ext == 'pdf'){
	$tumb_arquivo = 'pdf.png';
}else if($ext == 'rar' || $ext == 'zip'){
	$tumb_arquivo = 'rar.png';
}else{
	$tumb_arquivo = $documento;
}


$query2 = $pdo->query("SELECT * FROM disparos where campanha = '$id' ORDER BY id desc");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$total_envios = @count($res2);


$query2 = $pdo->query("SELECT * FROM disparos where campanha = '$id' ORDER BY id desc limit 1");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$hora_ultimo_envio = @$res2[0]['hora'];

echo <<<HTML
<tr class="{}">
<td>
{$tituloF}
</td>
<td class="esc">{$hora_ultimo_envio} <span class="{$ocultar_reg} text-primary"><small></small></span></td>
<td class="esc">{$forma_envio}</td>
<td class="esc">{$total_envios}</td>
<td class="esc"><img class="{$ocultar_foto}" src="images/marketing/{$arquivo}" width="27px" height="27px" class="mr-2">

<div class="dropdown" style="display: inline-block;">                      
	<a class="{$ocultar_foto}" title="Excluir Imagem" href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><big><i class="fa fa-close text-danger"></i></big></a>

		<div  class="dropdown-menu tx-13">
			<div class="dropdown-item-text botao_excluir_listar">
			<p>Confirmar Exclusão da Foto? <a href="#" onclick="excluirImagem('{$id}')"><span class="text-danger">Sim</span></a></p>
			</div>
		</div>
</div>

</td>
<td class="esc">

<audio controls="controls" class="{$ocultar_audio}" style="height:25px; width:180px">
<source src="images/marketing/{$audio}" type="audio/mp3" />
</audio>

<div class="dropdown" style="display: inline-block;">                      
	<a class="{$ocultar_audio}" title="Excluir Audio" href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><big><i class="fa fa-close text-danger"></i></big></a>

		<div  class="dropdown-menu tx-13">
			<div class="dropdown-item-text botao_excluir_listar">
			<p>Confirmar Exclusão do áudio? <a href="#" onclick="excluirAudio('{$id}')"><span class="text-danger">Sim</span></a></p>
			</div>
		</div>
</div>


</td>


<td class="esc"><a class="{$ocultar_doc}" href="images/marketing/{$documento}" target="_blank"><img src="images/marketing/{$tumb_arquivo}" width="25"></a>


<div class="dropdown" style="display: inline-block;">                      
	<a class="{$ocultar_doc}" title="Excluir Arquivo" href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><big><i class="fa fa-close text-danger"></i></big></a>

		<div  class="dropdown-menu tx-13">
			<div class="dropdown-item-text botao_excluir_listar">
			<p>Confirmar Exclusão do arquivo? <a href="#" onclick="excluirDoc('{$id}')"><span class="text-danger">Sim</span></a></p>
			</div>
		</div>
</div>


</td>

<td>
		<big><a class="btn btn-info-light btn-sm" href="#" onclick="editar('{$id}','{$titulo}', '{$mensagem}', '{$item1}', '{$item2}', '{$item3}', '{$item4}', '{$item5}', '{$item6}', '{$item7}', '{$item8}', '{$conclusao}', '{$arquivo}', '{$audio}', '{$item9}', '{$item10}', '{$item11}', '{$item12}', '{$item13}', '{$item14}', '{$item15}', '{$item16}', '{$item17}', '{$item18}', '{$item19}', '{$item20}', '{$link}', '{$video}', '{$conclusao2}', '{$conclusao3}', '{$conclusao4}', '{$link2}', '{$conclusao5}' , '{$tumb_arquivo}')" title="Editar Dados"><i class="fa fa-edit "></i></a></big>

		<big><a class="btn btn-secondary-light btn-sm" href="#" onclick="mostrar('{$titulo}', '{$mensagem}', '{$item1}', '{$item2}', '{$item3}', '{$item4}', '{$item5}', '{$item6}', '{$item7}', '{$item8}', '{$conclusao}', '{$arquivo}', '{$audio}', '{$dataF}', '{$data_envioF}', '{$envios}', '{$item9}', '{$item10}', '{$item11}', '{$item12}', '{$item13}', '{$item14}', '{$item15}', '{$item16}', '{$item17}', '{$item18}', '{$item19}', '{$item20}', '{$link}', '{$video}', '{$conclusao2}', '{$conclusao3}', '{$conclusao4}', '{$link2}', '{$conclusao5}', '{$tumb_arquivo}')" title="Ver Dados"><i class="fa fa-info-circle "></i></a></big>



	<big><a href="#" class="btn btn-danger-light btn-sm" onclick="excluir('{$id}')" title="Excluir"><i class="fa fa-trash-can text-danger"></i></a></big>


		<big><a class="btn btn-success-light btn-sm" href="#" onclick="disparar('{$id}','{$titulo}', '{$mensagem}', '{$item1}', '{$item2}', '{$item3}', '{$item4}', '{$item5}', '{$item6}', '{$item7}', '{$item8}', '{$conclusao}', '{$arquivo}', '{$audio}', '{$item9}', '{$item10}', '{$item11}', '{$item12}', '{$item13}', '{$item14}', '{$item15}', '{$item16}', '{$item17}', '{$item18}', '{$item19}', '{$item20}', '{$link}', '{$video}', '{$conclusao2}', '{$conclusao3}', '{$conclusao4}', '{$link2}', '{$conclusao5}', '{$tumb_arquivo}')" title="Enviar Disparos"><i class="fa fa-sign-out"></i></a></big>

		
		


		<big><a href="#" class="btn btn-danger-light btn-sm" onclick="pararModal('{$id}')" title="Para Campanha"><i class="fa fa-ban text-danger"></i></a></big>

		
	
		</td>
</tr>
HTML;

}

echo <<<HTML
</tbody>
<small><div align="center" id="mensagem-excluir"></div></small>
</table>
</small>
HTML;


}else{
	echo '<small>Não possui nenhum registro Cadastrado!</small>';
}

?>

<script type="text/javascript">
	$(document).ready( function () {
    $('#tabela').DataTable({
    		"ordering": false,
			"stateSave": true
    	});
    $('#tabela_filter label input').focus();
} );
</script>



<script type="text/javascript">
	function editar(id, titulo, mensagem, item1, item2, item3, item4, item5, item6, item7, item8, conclusao, arquivo, audio, item9, item10, item11, item12, item13, item14, item15, item16, item17, item18, item19, item20, link, video, conclusao2, conclusao3, conclusao4, link2, conclusao5, documento){
		$('#id').val(id);
		$('#titulo').val(titulo);
		$('#mensagem_input').val(mensagem);
		$('#item1').val(item1);		
		$('#item2').val(item2);
		$('#item3').val(item3);
		$('#item4').val(item4);
		$('#item5').val(item5);
		$('#item6').val(item6);
		$('#item7').val(item7);
		$('#item8').val(item8);

		$('#item9').val(item9);
		$('#item10').val(item10);
		$('#item11').val(item11);
		$('#item12').val(item12);
		$('#item13').val(item13);
		$('#item14').val(item14);
		$('#item15').val(item15);
		$('#item16').val(item16);
		$('#item17').val(item17);
		$('#item18').val(item18);
		$('#item19').val(item19);
		$('#item20').val(item20);

		$('#link').val(link);
		$('#video').val(video);

		$('#link2').val(link2);
		$('#conclusao5').val(conclusao5);

		$('#conclusao').val(conclusao);
		$('#conclusao2').val(conclusao2);
		$('#conclusao3').val(conclusao3);
		$('#conclusao4').val(conclusao4);
		
		$('#audio').val('');

						
		$('#titulo_inserir').text('Editar Registro');
		$('#modalForm').modal('show');
		$('#foto').val('');
		$('#target').attr('src','images/marketing/' + arquivo);
		$('#target_documento').attr('src','images/marketing/' + documento);
	}

	function limparCampos(){
		$('#id').val('');
		$('#titulo').val('');
		$('#mensagem_input').val('');
		$('#item1').val('');
		$('#item2').val('');
		$('#item3').val('');
		$('#item4').val('');
		$('#item5').val('');
		$('#item6').val('');
		$('#item7').val('');
		$('#item8').val('');

		$('#item9').val('');
		$('#item10').val('');
		$('#item11').val('');
		$('#item12').val('');
		$('#item13').val('');
		$('#item14').val('');
		$('#item15').val('');
		$('#item16').val('');
		$('#item17').val('');
		$('#item18').val('');
		$('#item19').val('');
		$('#item20').val('');

		$('#link').val('');
		$('#video').val('');

		$('#link2').val('');
		$('#conclusao5').val('');

		$('#conclusao').val('');
		$('#conclusao2').val('');
		$('#conclusao3').val('');
		$('#conclusao4').val('');		
		$('#foto').val('');
		$('#audio').val('');
		$('#target').attr('src','images/marketing/sem-foto.jpg');
		$('#target_documento').attr('src','images/marketing/sem-foto.jpg');
	}
</script>


<script type="text/javascript">
	function mostrar(titulo, mensagem, item1, item2, item3, item4, item5, item6, item7, item8, conclusao, arquivo, audio, dataF, data_envioF, envios, item9, item10, item11, item12, item13, item14, item15, item16, item17, item18, item19, item20, link, video, conclusao2, conclusao3, conclusao4, link2, conclusao5, documento){

		$('#item1_dad').show();
		$('#item2_dad').show();
		$('#item3_dad').show();
		$('#item4_dad').show();
		$('#item5_dad').show();
		$('#item6_dad').show();
		$('#item7_dad').show();
		$('#item8_dad').show();
		$('#item9_dad').show();
		$('#item10_dad').show();
		$('#item11_dad').show();
		$('#item12_dad').show();
		$('#item13_dad').show();
		$('#item14_dad').show();
		$('#item15_dad').show();
		$('#item16_dad').show();
		$('#item17_dad').show();
		$('#item18_dad').show();
		$('#item19_dad').show();
		$('#item20_dad').show();

		$('#area_link_dados').show();
		$('#area_video_dados').show();
		$('#area_conclusao3').show();
		$('#area_conclusao4').show();
		$('#area_conclusao5').show();

		if(conclusao == "" && link == ""){
			$('#area_link_dados').hide();
		}

		if(conclusao2 == "" && video == ""){
			$('#area_video_dados').hide();
		}

		if(conclusao3 == ""){
			$('#area_conclusao3').hide();
		}

		if(conclusao4 == ""){
			$('#area_conclusao4').hide();
		}

		if(conclusao5 == "" && link2 == ""){
			$('#area_conclusao5').hide();
		}


		if(item1 == ""){
			$('#item1_dad').hide();
		}

		if(item2 == ""){
			$('#item2_dad').hide();
		}

		if(item3 == ""){
			$('#item3_dad').hide();
		}

		if(item4 == ""){
			$('#item4_dad').hide();
		}

		if(item5 == ""){
			$('#item5_dad').hide();
		}

		if(item6 == ""){
			$('#item6_dad').hide();
		}

		if(item7 == ""){
			$('#item7_dad').hide();
		}

		if(item8 == ""){
			$('#item8_dad').hide();
		}

		if(item9 == ""){
			$('#item9_dad').hide();
		}

		if(item10 == ""){
			$('#item10_dad').hide();
		}

		if(item11 == ""){
			$('#item11_dad').hide();
		}

		if(item12 == ""){
			$('#item12_dad').hide();
		}

		if(item13 == ""){
			$('#item13_dad').hide();
		}

		if(item14 == ""){
			$('#item14_dad').hide();
		}

		if(item15 == ""){
			$('#item15_dad').hide();
		}

		if(item16 == ""){
			$('#item16_dad').hide();
		}

		if(item17 == ""){
			$('#item17_dad').hide();
		}

		if(item18 == ""){
			$('#item18_dad').hide();
		}

		if(item19 == ""){
			$('#item19_dad').hide();
		}

		if(item20 == ""){
			$('#item20_dad').hide();
		}



		$('#nome_dados').text(titulo);

		$('#titulo_dados').text(titulo);
		$('#mensagem_dados').text(mensagem);
		$('#item1_dados').text(item1);
		$('#item2_dados').text(item2);
		$('#item3_dados').text(item3);
		$('#item4_dados').text(item4);
		$('#item5_dados').text(item5);
		$('#item6_dados').text(item6);
		$('#item7_dados').text(item7);
		$('#item8_dados').text(item8);

		$('#item9_dados').text(item9);
		$('#item10_dados').text(item10);
		$('#item11_dados').text(item11);
		$('#item12_dados').text(item12);
		$('#item13_dados').text(item13);
		$('#item14_dados').text(item14);
		$('#item15_dados').text(item15);
		$('#item16_dados').text(item16);
		$('#item17_dados').text(item17);
		$('#item18_dados').text(item18);
		$('#item19_dados').text(item19);
		$('#item20_dados').text(item20);

		$('#link_dados').text(link);
		$('#video_dados').text(video);

		$('#link2_dados').text(link2);
		$('#conclusao5_dados').text(conclusao5);

		$('#conclusao_dados').text(conclusao);	
		$('#conclusao2_dados').text(conclusao2);	
		$('#conclusao3_dados').text(conclusao3);	
		$('#conclusao4_dados').text(conclusao4);	

		$('#data_dados').text(dataF);
		$('#data_envio_dados').text(data_envioF);
		$('#envios').text(envios);	
				
		$('#target_dados').attr('src','images/marketing/' + arquivo);
		$('#audio_dados').attr('src','images/marketing/' + audio);
		$('#target_documento_dados').attr('src','images/marketing/' + documento);

		if(arquivo == 'sem-foto.jpg'){
			$('#target_dados_div').hide();
		}else{
			$('#target_dados_div').show();
		}

		if(audio == ''){
			$('#audio_dados_div').hide();
		}else{
			$('#audio_dados_div').show();
		}

		if(documento == 'sem-foto.jpg'){
			$('#target_documento_dados_div').hide();
		}else{
			$('#target_documento_dados_div').show();
		}

		$('#modalDados').modal('show');
	}
</script>



<script type="text/javascript">
	function disparar(id, titulo, mensagem, item1, item2, item3, item4, item5, item6, item7, item8, conclusao, arquivo, audio, item9, item10, item11, item12, item13, item14, item15, item16, item17, item18, item19, item20, link, video, conclusao2, conclusao3, conclusao4, link2, conclusao5, documento){


		$('#item1_dis').show();
		$('#item2_dis').show();
		$('#item3_dis').show();
		$('#item4_dis').show();
		$('#item5_dis').show();
		$('#item6_dis').show();
		$('#item7_dis').show();
		$('#item8_dis').show();

		$('#item9_dis').show();
		$('#item10_dis').show();
		$('#item11_dis').show();
		$('#item12_dis').show();
		$('#item13_dis').show();
		$('#item14_dis').show();
		$('#item15_dis').show();
		$('#item16_dis').show();
		$('#item17_dis').show();
		$('#item18_dis').show();
		$('#item19_dis').show();
		$('#item20_dis').show();

		$('#area_link_disparar').show();
		$('#area_video_disparar').show();
		$('#area_conclusao3_disparar').show();
		$('#area_conclusao4_disparar').show();
		$('#area_conclusao5_disparar').show();

		if(conclusao == "" && link == ""){
			$('#area_link_disparar').hide();
		}

		if(conclusao2 == "" && video == ""){
			$('#area_video_disparar').hide();
		}

		if(conclusao3 == ""){
			$('#area_conclusao3_disparar').hide();
		}

		if(conclusao4 == ""){
			$('#area_conclusao4_disparar').hide();
		}

		if(conclusao5 == "" && link2 == ""){
			$('#area_conclusao5_disparar').hide();
		}


		if(item1 == ""){
			$('#item1_dis').hide();
		}

		if(item2 == ""){
			$('#item2_dis').hide();
		}

		if(item3 == ""){
			$('#item3_dis').hide();
		}

		if(item4 == ""){
			$('#item4_dis').hide();
		}

		if(item5 == ""){
			$('#item5_dis').hide();
		}

		if(item6 == ""){
			$('#item6_dis').hide();
		}

		if(item7 == ""){
			$('#item7_dis').hide();
		}

		if(item8 == ""){
			$('#item8_dis').hide();
		}


		if(item9 == ""){
			$('#item9_dis').hide();
		}

		if(item10 == ""){
			$('#item10_dis').hide();
		}

		if(item11 == ""){
			$('#item11_dis').hide();
		}

		if(item12 == ""){
			$('#item12_dis').hide();
		}

		if(item13 == ""){
			$('#item13_dis').hide();
		}

		if(item14 == ""){
			$('#item14_dis').hide();
		}

		if(item15 == ""){
			$('#item15_dis').hide();
		}

		if(item16 == ""){
			$('#item16_dis').hide();
		}

		if(item17 == ""){
			$('#item17_dis').hide();
		}

		if(item18 == ""){
			$('#item18_dis').hide();
		}

		if(item19 == ""){
			$('#item19_dis').hide();
		}

		if(item20 == ""){
			$('#item20_dis').hide();
		}



		$('#nome_entrada').text(titulo);		
		$('#id_entrada').val(id);	

		$('#total_clientes').text("Alterar Opção de Teste: 0");	

		if(item1 == ""){
			$('#itens_disparar').hide();
		}else{
			$('#itens_disparar').show();
		}


		



		$('#titulo_disparar').text(titulo);
		$('#mensagem_disparar').text(mensagem);
		$('#item1_disparar').text(item1);
		$('#item2_disparar').text(item2);
		$('#item3_disparar').text(item3);
		$('#item4_disparar').text(item4);
		$('#item5_disparar').text(item5);
		$('#item6_disparar').text(item6);
		$('#item7_disparar').text(item7);
		$('#item8_disparar').text(item8);

		$('#item9_disparar').text(item9);
		$('#item10_disparar').text(item10);
		$('#item11_disparar').text(item11);
		$('#item12_disparar').text(item12);
		$('#item13_disparar').text(item13);
		$('#item14_disparar').text(item14);
		$('#item15_disparar').text(item15);
		$('#item16_disparar').text(item16);
		$('#item17_disparar').text(item17);
		$('#item18_disparar').text(item18);
		$('#item19_disparar').text(item19);
		$('#item20_disparar').text(item20);

		$('#link_disparar').text(link);
		$('#video_disparar').text(video);

		$('#link2_disparar').text(link2);
		$('#conclusao5_disparar').text(conclusao5);

		$('#conclusao_disparar').text(conclusao);	
		$('#conclusao2_disparar').text(conclusao2);	
		$('#conclusao3_disparar').text(conclusao3);	
		$('#conclusao4_disparar').text(conclusao4);	


		$('#clientes').val('Teste');	
		
				
		$('#target_disparar').attr('src','images/marketing/' + arquivo);
		$('#audio_disparar').attr('src','images/marketing/' + audio);
		$('#target_documento_disparar').attr('src','images/marketing/' + documento);

		if(arquivo == 'sem-foto.jpg'){
			$('#target_disparar_div').hide();
		}else{
			$('#target_disparar_div').show();
		}

		if(audio == ''){
			$('#audio_disparar_div').hide();
		}else{
			$('#audio_disparar_div').show();
		}

		if(documento == 'sem-foto.jpg'){
			$('#target_documento_disparar_div').hide();
		}else{
			$('#target_documento_disparar_div').show();
		}

		$('#modalEntrada').modal('show');
	}
</script>


<script type="text/javascript">
	

	function excluirImagem(id){
    $.ajax({
        url: 'paginas/' + pag + "/excluir_imagem.php",
        method: 'POST',
        data: {id},
        dataType: "text",

        success: function (mensagem) {            
            if (mensagem.trim() == "Excluído com Sucesso") {                
                listar();                
            } else {
                    $('#mensagem-excluir').addClass('text-danger')
                    $('#mensagem-excluir').text(mensagem)
                }

        },      

    });
}

function excluirAudio(id){
    $.ajax({
        url: 'paginas/' + pag + "/excluir_audio.php",
        method: 'POST',
        data: {id},
        dataType: "text",

        success: function (mensagem) {            
            if (mensagem.trim() == "Excluído com Sucesso") {                
                listar();                
            } else {
                    $('#mensagem-excluir').addClass('text-danger')
                    $('#mensagem-excluir').text(mensagem)
                }

        },      

    });
}


function excluirDoc(id){
    $.ajax({
        url: 'paginas/' + pag + "/excluir_doc.php",
        method: 'POST',
        data: {id},
        dataType: "text",

        success: function (mensagem) {            
            if (mensagem.trim() == "Excluído com Sucesso") {                
                listar();                
            } else {
                    $('#mensagem-excluir').addClass('text-danger')
                    $('#mensagem-excluir').text(mensagem)
                }

        },      

    });
}



function pararModal(id) {
    //$('#mensagem-excluir').text('Excluindo...')

    $('body').removeClass('timer-alert');
    Swal.fire({
        title: "Parar os Disparos?",
        text: "Você irá parar todos os disparos dessa campanha!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33', // Cor do botão de confirmação (vermelho)
        cancelButtonColor: '#3085d6', // Cor do botão de cancelamento (azul)
        confirmButtonText: "Sim, Parar!",
        cancelButtonText: "Cancelar",
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            pararDisparo(id);			

        }
    });


};


</script>



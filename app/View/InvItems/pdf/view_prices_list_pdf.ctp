<span style="font-size: 30px; font-weight: bold">IMEXPORT</span><span style="font-weight: bold">SRL</span>
<div style="height: 2px; background-color: black"></div>
<?php 
	if($valuePrice == 'CIF' || $valuePrice == 'VENTA'){
		$currency = ' (Bs.)';
	}else{
		$currency = ' ($us.)';
	}
?> 
<table style="width:100%">
	<tr>
		<td align="center"><span class="report-title"><?php echo 'Lista de Precios Actuales';?></span></td>
	</tr>
</table>
<br>

<p><span class="report-title">Marca: </span><?php echo $valueBrand;?></p>

<p><span class="report-title">Tipo de Precio: </span><?php echo $valuePrice;?></p>

<p><span class="report-title">Fecha: </span><?php echo date("d/m/Y");?></p>

<br>

<table class="report-table" border="1" bordercolor="red" style="border-collapse:collapse;">
							<thead>
								<tr>
									<th style="width:20%">Codigo</th>
									<th style="width:60%">Nombre</th>
									<th style="width:20%">Precio<?php echo $currency; ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								for($i=0; $i<count($itemsByBrand); $i++){
									echo '<tr >';
										echo '<td style="width:20%">'.$itemsByBrand[$i]['InvItem']['code'].'</td>';
										echo '<td style="width:60%" align="left">'.$itemsByBrand[$i]['InvItem']['name'].'</td>';
										echo '<td style="width:20%" align="center">'.$itemsByBrand[$i]['InvItem']['lastPrice'].'</td>';
									echo '</tr>';								
								} 
								?>
							</tbody>
						</table>

<style type="text/css">
	.report-title{
		font-weight: bold
	}
	.report-table{
		width: 100%
	}
</style>

<span style="font-size: 25px; font-weight: bold">IMEXPORT</span><span style="font-weight: bold">SRL</span>
<hr style="height: 2px; color: #000; background-color: #000;">
<div style="font-size: 20px; font-weight: bold; text-align:center; text-decoration: underline;">HISTORICO DE PRECIOS</div>
<br>
<?php
$currencyAbbr = 'Bs';
if ($data["currency"] == "DOLARES") {
    $currencyAbbr = '$us';
}
?>

<table class="report-table" border="0" style="border-collapse:collapse; width:100%;">
    <thead>
        <tr style="text-align:center">
            <th style="width:25%">Fecha Inicio:</th>
            <th style="width:25%">Fecha Fin:</th>
            <th style="width:25%">Moneda:</th>
			<th style="width:25%">Tipo de Precio:</th>
        </tr>
    </thead>
    <tbody>
        <tr style="text-align:center">
            <td><?php echo $data['startDate']; ?></td>
            <td><?php echo $data['finishDate']; ?></td>
            <td><?php echo $data['currency']; ?></td>
			<td><?php echo $data['priceTypeName']; ?></td>
        </tr>
    </tbody>
</table>

<hr style="height: 1px; color: #444; background-color: #444;">

<table class="report-table" border="0" style="border-collapse:collapse; width:100%;">
	<tr>
		<td><span style="font-weight:bold;">Marca: </span><?php echo $data['brandName']; ?></td>
	</tr>
</table>
<table class="report-table" border="1" style="border-collapse:collapse; width:100%;">
<?php if($pricesByItem != null){ ?>	
	<?php foreach ($pricesByItem as $priceByItem) { ?>	
		<tr>
			<th >Producto</th>
			<th >Descripci&oacute;n</th>
			<?php $priceCount = 0; ?>
		<?php for($i = 0; $i < count($priceByItem['prices']); $i++){ $ii = $i + 1; ?>
			<th colspan="2" ><?php echo date("d/m/Y", strtotime($priceByItem['prices'][$i]['date'])) ?> </th>
			<?php if($ii % 4 == 0 && $ii != count($priceByItem['prices'])){ //sets the number of cols used by row ?>
			<tr>
			<td style="padding-left: 10px;"><?php echo $priceByItem["InvItem"]["code"]; ?></td>
			<td style="padding-left: 10px;"><?php echo $priceByItem["InvItem"]["name"]; ?></td>
			<?php for($j = 0; $j < count($priceByItem['prices']); $j++){ $jj = $j + 1; ?>
				<td style="text-align: center;"><?php echo $priceByItem['prices'][$j]['price'].$currencyAbbr ?> </td>
				<?php if($priceByItem['prices'][$j]['increment'] > 0){$priceColor=' color: green;';}elseif($priceByItem['prices'][$j]['increment'] < 0){$priceColor=' color: red;';}else{$priceColor='';} ?>
					<td style="text-align: center; <?php echo $priceColor; ?>"><?php echo $priceByItem['prices'][$j]['increment'].'%'; ?> </td>
				<?php ?>	
				<?php $priceCount = $priceCount + 1; ?>
				<?php if($jj % 4 == 0 && $jj != count($priceByItem['prices'])){ //sets the number of cols used by row ?>
					<?php break; ?>
				<?php } ?>
			<?php } ?>	
				</tr>					
				<tr>
					<th >Producto</th>
					<th >Descripci&oacute;n</th>
			<?php } ?>
					
		<?php } ?>	
		</tr>
		<tr>
			<td style="padding-left: 10px;"><?php echo $priceByItem["InvItem"]["code"]; ?></td>
			<td style="padding-left: 10px;"><?php echo $priceByItem["InvItem"]["name"]; ?></td>
			<?php for($k = $priceCount; $k < count($priceByItem['prices']); $k++){  ?>
					<td style="text-align: center;"><?php echo $priceByItem['prices'][$k]['price'].$currencyAbbr; ?> </td>
					<?php if($priceByItem['prices'][$k]['increment'] > 0){$priceColor=' color: green;';}elseif($priceByItem['prices'][$k]['increment'] < 0){$priceColor=' color: red;';}else{$priceColor='';} ?>
					<td style="text-align: center; <?php echo $priceColor; ?>"><?php echo $priceByItem['prices'][$k]['increment'].'%'; ?> </td>
			<?php } ?>		
		</tr>
		<tr style="height:7px; border: 0;" colspan="<?php echo count($priceByItem['prices'])*2+2; ?>" ></tr>
	<?php } ?>	
<?php }else{ ?>
		<div style="font-size: 20px; font-weight: bold; text-align:center;">NO EXISTEN PRECIOS PARA MOSTRAR</div>
<?php } ?>
</table>
<br>



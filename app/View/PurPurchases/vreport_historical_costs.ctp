<span style="font-size: 25px; font-weight: bold">IMEXPORT</span><span style="font-weight: bold">SRL</span>
<hr style="height: 2px; color: #000; background-color: #000;">
<div style="font-size: 20px; font-weight: bold; text-align:center; text-decoration: underline;">HISTORICO DE COSTOS</div>
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
        </tr>
    </thead>
    <tbody>
        <tr style="text-align:center">
            <td><?php echo $data['startDate']; ?></td>
            <td><?php echo $data['finishDate']; ?></td>
            <td><?php echo $data['currency']; ?></td>
        </tr>
    </tbody>
</table>

<hr style="height: 1px; color: #444; background-color: #444;">

<table class="report-table" border="1" style="border-collapse:collapse; width:100%;">
<?php if($pricesByCost != null){ ?>	
	<?php foreach ($pricesByCost as $priceByCost) { ?>	
		<tr>
			<th >Costo</th>
			<?php $priceCount = 0; ?>
		<?php for($i = 0; $i < count($priceByCost['prices']); $i++){ $ii = $i + 1; ?>
			<th colspan="2" ><?php echo date("d/m/Y", strtotime($priceByCost['prices'][$i]['date'])) ?> </th>
			<?php if($ii % 4 == 0 && $ii != count($priceByCost['prices'])){ //sets the number of cols used by row ?>
			<tr>
			<td style="padding-left: 10px;"><?php echo $priceByCost["InvPriceType"]["name"]; ?></td>
			<?php for($j = 0; $j < count($priceByCost['prices']); $j++){ $jj = $j + 1; ?>
				<td style="text-align: center;"><?php echo $priceByCost['prices'][$j]['amount'].$currencyAbbr ?> </td>
				<?php if($priceByCost['prices'][$j]['increment'] > 0){$priceColor=' color: green;';}elseif($priceByCost['prices'][$j]['increment'] < 0){$priceColor=' color: red;';}else{$priceColor='';} ?>
					<td style="text-align: center; <?php echo $priceColor; ?>"><?php echo $priceByCost['prices'][$j]['increment'].'%'; ?> </td>
				<?php ?>	
				<?php $priceCount = $priceCount + 1; ?>
				<?php if($jj % 4 == 0 && $jj != count($priceByCost['prices'])){ //sets the number of cols used by row ?>
					<?php break; ?>
				<?php } ?>
			<?php } ?>	
				</tr>					
				<tr>
					<th >Costo</th>
			<?php } ?>
					
		<?php } ?>	
		</tr>
		<tr>
			<td style="padding-left: 10px;"><?php echo $priceByCost["InvPriceType"]["name"]; ?></td>
			<?php for($k = $priceCount; $k < count($priceByCost['prices']); $k++){  ?>
					<td style="text-align: center;"><?php echo $priceByCost['prices'][$k]['amount'].$currencyAbbr; ?> </td>
					<?php if($priceByCost['prices'][$k]['increment'] > 0){$priceColor=' color: green;';}elseif($priceByCost['prices'][$k]['increment'] < 0){$priceColor=' color: red;';}else{$priceColor='';} ?>
					<td style="text-align: center; <?php echo $priceColor; ?>"><?php echo $priceByCost['prices'][$k]['increment'].'%'; ?> </td>
			<?php } ?>		
		</tr>
		<tr style="height:7px; border: 0;" colspan="<?php echo count($priceByCost['prices'])*2+2; ?>" ></tr>
	<?php } ?>	
<?php }else{ ?>
		<div style="font-size: 20px; font-weight: bold; text-align:center;">NO EXISTEN PRECIOS PARA MOSTRAR</div>
<?php } ?>
</table>
<br>



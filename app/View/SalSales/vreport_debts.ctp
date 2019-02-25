<span style="font-size: 25px; font-weight: bold">IMEXPORT</span><span style="font-weight: bold">SRL</span>
<hr style="height: 2px; color: #000; background-color: #000;">
<?php if($debtsByCustomer != null){ ?>
<div style="font-size: 20px; font-weight: bold; text-align:center; text-decoration: underline;">DEUDAS</div>
<br>




<hr style="height: 1px; color: #444; background-color: #444;">

<table class="report-table" border="1" style="border-collapse:collapse; width:100%;">
    <thead>
        <tr>
            <th>Cliente</th>
            <th>Nro. de Nota de Remisión</th>
			<th>Monto Deuda (Bs)</th>
        </tr>	
    </thead>
	<?php $all = 0;?>	
    <?php foreach ($debtsByCustomer as $debtByCustomer) { ?>	
        <tr>
            <td style="padding-left: 10px;"><span style="font-weight:bold;"><?php echo $debtByCustomer["name"]; ?></span></td>
			<td style="padding-left: 10px;"></td>
			<td style="text-align: center;"><span style="font-weight:bold;"><?php echo $debtByCustomer["total"]; ?></span></td>
        </tr>
		<?php for($i = 0; $i < count($debtByCustomer)-2; $i++) { ?>	
			<tr>
				<td style="padding-left: 10px;"></td>
				<td style="text-align: center;"><?php echo $debtByCustomer[$i][0]; ?></td>
				<td style="text-align: center;"><?php echo $debtByCustomer[$i][1] - $debtByCustomer[$i][2]; ?></td>
			</tr>
		<?php } ?>	
		<tr>
			<td style="padding-left: 10px;"></td>
			<td style="padding-left: 10px;"></td>
			<td style="padding-left: 10px;"></td>
		</tr>	
		<?php $all += $debtByCustomer["total"]; ?>	
	<?php } ?>
    <tr>
		<td style="padding-left: 10px;"></td>
		<td style="text-align: center;"><span style="font-weight:bold;">Monto Total Adeudado: </span></td>
		<td style="text-align: center;"><span style="font-weight:bold;"><?php echo $all; ?></span></td>
	</tr>
</table>
<?php } else {?>
	<div style="font-size: 20px; font-weight: bold; text-align:center; ">NO EXISTEN DEUDAS</div>
<?php } ?>
<br>

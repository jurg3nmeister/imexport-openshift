<?php echo $this->Html->script('modules/InvSuppliers', FALSE); ?>
<div class="span12">

	<div class="widget-box">
		<div class="widget-content nopadding">
			<?php
//			echo $this->Html->link('Cancelar', array('action' => 'index'), array('class' => 'btn'));
			$url = array("action" => "index");
			$parameters = $this->passedArgs;
			
			echo $this->Html->link('<i class=" icon-arrow-left"></i> Volver', array_merge($url, $parameters), array('class' => 'btn', 'escape' => false)) . ' ';
			echo $this->BootstrapForm->submit('Guardar Cambios', array('id' => 'saveSupplier', 'class' => 'btn btn-primary', 'div' => false));
			echo '<span id="boxProcessing"></span>';
			echo '<br><div id="boxMessage"></div>';
			?>
		</div>
	</div>

	<div class="widget-box">
		<div class="widget-title">
			<span class="icon">
				<i class="icon-edit"></i>								
			</span>
			<h5>Proveedor</h5>			
		</div>
		<?php echo $this->BootstrapForm->create('InvSupplier', array('class' => 'form-horizontal')); ?>
		<?php
		echo $this->BootstrapForm->input('idSupplier', array(
			'type' => 'hidden',
			'id' => 'txtIdSupplier'
			,'value'=>$idSupplier
		));
		echo $this->BootstrapForm->input('name', array(
			'label' => "Nombre Compañia"
			,'id' => 'txtNameSupplier'
			,'value'=> $supplier[0]['InvSupplier']['name']
		));
//		echo $this->BootstrapForm->input('idEmployee', array(
//			'type' => 'hidden'
//			,'id' => 'txtIdEmployee'
//			,'value'=>$employees[0]['SalEmployee']['id']
//		));
//		echo $this->BootstrapForm->input('employee_name', array(
//			'label' => "Responsable:"
//			,'id' => 'txtNameEmployee'
//			,'value'=> $employees[0]['SalEmployee']['name']
//		));	
//		echo $this->BootstrapForm->input('idTaxNumber', array(
//			'type' => 'hidden',
//			'id' => 'txtIdTaxNumber'
//			,'value'=>$taxNumbers[0]['SalTaxNumber']['id']
//		));
//		echo $this->BootstrapForm->input('nit', array(
//			'label' => "Nombre Factura:"
//			,'id' => 'txtNameTaxNumber'
//			,'value'=> $taxNumbers[0]['SalTaxNumber']['name']
//		));
//		echo $this->BootstrapForm->input('nit', array(
//			'label' => "NIT/CI:"
//			,'id' => 'txtNitTaxNumber'
//			,'value'=> $taxNumbers[0]['SalTaxNumber']['nit']
//		));
		echo $this->BootstrapForm->input('address', array(
			'label' => "Dirección:"
//			, 'placeholder' => 'Dirección, ciudad, (pais)'
			,'id' => 'txtAddressSupplier'
			,'value'=> $supplier[0]['InvSupplier']['address']
		));
		echo $this->BootstrapForm->input('area', array(
			'label' => "Zona/Barrio/Otro:"
//			, 'placeholder' => 'Dirección, ciudad, (pais)'
			,'id' => 'txtAreaSupplier'
			,'value'=> $supplier[0]['InvSupplier']['area']
		));
		echo $this->BootstrapForm->input('location', array(
			'label' => "Ciudad:"
//			, 'placeholder' => 'Dirección, ciudad, (pais)'
			,'id' => 'txtLocationSupplier'
			,'value'=> $supplier[0]['InvSupplier']['location']
		));
		echo $this->BootstrapForm->input('country', array(
			'label' => "Pais:"
//			, 'placeholder' => 'Dirección, ciudad, (pais)'
			,'id' => 'cbxCountrySupplier'
			,'value'=> $supplier[0]['InvSupplier']['country']
			,'type'=>'select'
			,'options'=>array( 
            '  ' => 'Seleccione un pais', 
            '--' => 'Ninguno', 
            'AF' => 'Afganistan', 
            'AL' => 'Albania', 
            'DZ' => 'Algeria', 
            'AS' => 'Samoa Americana', 
            'AD' => 'Andorra',  
            'AO' => 'Angola', 
            'AI' => 'Anguila', 
            'AQ' => 'Antartida', 
            'AG' => 'Antigua y Barbuda',  
            'AR' => 'Argentina',  
            'AM' => 'Armenia',  
            'AW' => 'Aruba',  
            'AU' => 'Australia',  
            'AT' => 'Austria',  
            'AZ' => 'Azerbaiyán', 
            'BS' => 'Bahamas',  
            'BH' => 'Bahrein',  
            'BD' => 'Bangladesh', 
            'BB' => 'Barbados', 
            'BY' => 'Bielorrusia',  
            'BE' => 'Bélgica',  
            'BZ' => 'Belice', 
            'BJ' => 'Benín',  
            'BM' => 'Bermudas',  
            'BT' => 'Bután', 
            'BO' => 'Bolivia',  
            'BA' => 'Bosnia-Herzegovina', 
            'BW' => 'Botswana', 
            'BV' => 'Bouvet Island',  
            'BR' => 'Brasil', 
            'IO' => 'British Indian Ocean Territory', 
            'BN' => 'Brunei Darussalam',  
            'BG' => 'Bulgaria', 
            'BF' => 'Burkina Faso', 
            'BI' => 'Burundi',  
            'KH' => 'Camboya', 
            'CM' => 'Camerún', 
            'CA' => 'Canadá', 
            'CV' => 'Cabo Verde', 
            'KY' => 'Islas Cayman', 
            'CF' => 'Republica Centroafricana ', 
            'TD' => 'Chad', 
            'CL' => 'Chile',  
            'CN' => 'China', 
            'CX' => 'Isla de Navidad',     
            'CC' => 'Isla Cocos',  
            'CO' => 'Colombia', 
            'KM' => 'Comoroes',  
            'CG' => 'Congo, República del',  
            'CD' => 'Congo, República Democrática del',  
            'CK' => 'Islas Cook', 
            'CR' => 'Costa Rica', 
            'CI' => 'Costa de Marfil',  
            'HR' => 'Croacia', 
            'CU' => 'Cuba', 
            'CY' => 'Chipre', 
            'CZ' => 'Republica Checa', 
            'DK' => 'Dinamarca',  
            'DJ' => 'Djibouti', 
            'DM' => 'Dominica', 
            'DO' => 'Dominicana, Republica', 
            'TP' => 'Timor Oriental', 
            'EC' => 'Ecuador',  
            'EG' => 'Egipto',  
            'SV' => 'El Salvador',  
            'GQ' => 'Guinea Ecuatorial',  
            'ER' => 'Eritrea',  
            'EE' => 'Estonia',  
            'ET' => 'Etiopía', 
            'FK' => 'Islas Malvinas',  
            'FO' => 'Islas Feroe',  
            'FJ' => 'Fiyi', 
            'FI' => 'Finlandia', 
            'FR' => 'Francia', 
            'FX' => 'France, Metropolitan', 
            'GF' => 'Guayana Francesa',  
            'PF' => 'Polinesia Francesa', 
            'TF' => 'Tierras Australes y Antárticas Francesas',  
            'GA' => 'Gabón',  
            'GM' => 'Gambia', 
            'GE' => 'Georgia',  
            'DE' => 'Alemania',  
            'GH' => 'Ghana',  
            'GI' => 'Gibraltar',  
            'GR' => 'Grecia', 
            'GL' => 'Groenlandia',  
            'GD' => 'Granada',  
            'GP' => 'Guadalupe', 
            'GU' => 'Guam', 
            'GT' => 'Guatemala',  
            'GN' => 'Guinea, República', 
            'GW' => 'Guinea Bissau',  
            'GY' => 'Guyana', 
            'HT' => 'Haiti',  
            'HM' => 'Heard and Mc Donald Islands',  
            'VA' => 'Santa Sede (Ciudad del Vaticano)',  
            'HN' => 'Honduras', 
            'HK' => 'Hong Kong',  
            'HU' => 'Hungria',  
            'IS' => 'Islandia',  
            'IN' => 'India',  
            'ID' => 'Indonesia',  
            'IR' => 'Iran', 
            'IQ' => 'Iraq', 
            'IE' => 'Irlanda',  
            'IL' => 'Israel', 
            'IT' => 'Italia',  
            'JM' => 'Jamaica',  
            'JP' => 'Japón', 
            'JO' => 'Jordania', 
            'KZ' => 'Kazajstán', 
            'KE' => 'Kenia',  
            'KI' => 'Kiribati', 
            'KP' => 'Corea del Norte', 
            'KR' => 'Corea del Sur', 
            'KW' => 'Kuwait', 
            'KG' => 'Kirguistán', 
            'LA' => 'Laos', 
            'LV' => 'Letonia', 
            'LB' => 'Libano', 
            'LS' => 'Lesotho',  
            'LR' => 'Liberia',  
            'LY' => 'Libia', 
            'LI' => 'Liechtenstein',  
            'LT' => 'Lituania', 
            'LU' => 'Luxemburgo', 
            'MO' => 'Macao',  
            'MK' => 'Macedonia', 
            'MG' => 'Madagascar', 
            'MW' => 'Malawi', 
            'MY' => 'Malasia', 
            'MV' => 'Maldivas', 
            'ML' => 'Malí', 
            'MT' => 'Malta', 
            'MH' => 'Islas Marshall', 
            'MQ' => 'Martinica', 
            'MR' => 'Mauritania', 
            'MU' => 'Mauricio', 
            'YT' => 'Mayotte',  
            'MX' => 'México', 
            'FM' => 'Micronesia, Estados Federados de', 
            'MD' => 'Moldavia', 
            'MC' => 'Monaco', 
            'MN' => 'Mongolia', 
            'MS' => 'Montserrat', 
            'MA' => 'Marruecos', 
            'MZ' => 'Mozambique', 
            'MM' => 'Myanmar', 
            'NA' => 'Namibia', 
            'NR' => 'Nauru',  
            'NP' => 'Nepal',  
            'NL' => 'Paises Bajos, Holanda', 
            'AN' => 'Antillas Holandesas', 
            'NC' => 'Nueva Caledonia', 
            'NZ' => 'Nueva Zelanda',  
            'NI' => 'Nicaragua',  
            'NE' => 'Niger',  
            'NG' => 'Nigeria',  
            'NU' => 'Niue', 
            'NF' => 'Norfolk Island', 
            'MP' => 'Marianas del Norte', 
            'NO' => 'Noruega', 
            'OM' => 'Omán', 
            'PK' => 'Pakistán', 
            'PW' => 'Palau', 
            'PA' => 'Panamá', 
            'PG' => 'Papúa-Nueva Guinea', 
            'PY' => 'Paraguay', 
            'PE' => 'Perú', 
            'PH' => 'Filipinas', 
            'PN' => 'Isla Pitcairn', 
            'PL' => 'Polonia', 
            'PT' => 'Portugal', 
            'PR' => 'Puerto Rico', 
            'QA' => 'Qatar', 
            'RE' => 'Reunión', 
            'RO' => 'Rumania', 
            'RU' => 'Rusa, Federación', 
            'RW' => 'Ruanda', 
            'KN' => 'San Cristobal y Nevis',  
            'LC' => 'Santa Lucía',  
            'VC' => 'San Vincente y Granadinas', 
            'WS' => 'Samoa',  
            'SM' => 'San Marino', 
            'ST' => 'Santo Tomé y Príncipe', 
            'SA' => 'Arabia Saudita', 
            'SN' => 'Senegal', 
            'SC' => 'Seychelles', 
            'SL' => 'Sierra Leona', 
            'SG' => 'Singapur',  
            'SK' => 'Eslovaquia', 
            'SI' => 'Eslovenia', 
            'SB' => 'Islas Salomón', 
            'SO' => 'Somalia',  
            'ZA' => 'Sudáfrica', 
            'GS' => 'South Georgia and the South Sandwich Islands', 
            'ES' => 'España', 
            'LK' => 'Sri Lanka', 
            'SH' => 'St. Helena', 
            'PM' => 'St. Pierre and Miquelon',  
            'SD' => 'Sudán',  
            'SR' => 'Surinam', 
            'SJ' => 'Svalbard and Jan Mayen Islands', 
            'SZ' => 'Swazilandia',  
            'SE' => 'Suecia', 
            'CH' => 'Suiza',  
            'SY' => 'Siria', 
            'TW' => 'Taiwan', 
            'TJ' => 'Tadjikistan', 
            'TZ' => 'Tanzania', 
            'TH' => 'Tailandia', 
            'TG' => 'Togo', 
            'TK' => 'Tokelau', 
            'TO' => 'Tonga',  
            'TT' => 'Trinidad y Tobago',  
            'TN' => 'Túnez',  
            'TR' => 'Turquía', 
            'TM' => 'Turkmenistan', 
            'TC' => 'Islas Turcas y Caicos', 
            'TV' => 'Tuvalu', 
            'UG' => 'Uganda', 
            'UA' => 'Ucrania', 
            'AE' => 'Emiratos Árabes Unidos', 
            'GB' => 'Reino Unido', 
            'US' => 'Estado Unidos', 
            'UM' => 'United States Minor Outlying Islands', 
            'UY' => 'Uruguay',  
            'UZ' => 'Uzbekistán', 
            'VU' => 'Vanuatu',  
            'VE' => 'Venezuela', 
            'VN' => 'Vietnam', 
            'VG' => 'Islas Virgenes Britanicas', 
            'VI' => 'Islas Virgenes Americanas',  
            'WF' => 'Wallis y Futuna',  
            'EH' => 'Sáhara Occidental', 
            'YE' => 'Yemen',  
            'YU' => 'Yugoslavia', 
            'ZM' => 'Zambia', 
            'ZW' => 'Zimbabwe'             
            )
			,'class'=>'span3'
		));
		echo $this->BootstrapForm->input('phone', array(
			'label' => "Teléfono/Fax:"
			,'id' => 'txtPhoneSupplier'
			,'value'=> $supplier[0]['InvSupplier']['phone']
		));
		echo $this->BootstrapForm->input('email', array(
			'label' => "Correo Electrónico:"
			,'id' => 'txtEmailSupplier'
			,'value'=> $supplier[0]['InvSupplier']['email']
		));
		echo $this->BootstrapForm->input('website', array(
			'label' => "Sitio Web:"
			,'id' => 'txtWebsiteSupplier'
			,'value'=> $supplier[0]['InvSupplier']['website']
		));
//		echo $this->BootstrapForm->end();
		?>
		<?php echo $this->BootstrapForm->end(); ?>



		<div class="widget-box">
			<div class="widget-title">
				<ul class="nav nav-tabs">
					<li class="active"><a data-toggle="tab" href="#tab1">Contactos</a></li>
				</ul>
			</div>
			<div class="widget-content tab-content">
				<div id="tab1" class="tab-pane active">
					<?php
					echo $this->BootstrapForm->create('InvSupplierContact', array('class' => 'form-inline'));
					echo $this->BootstrapForm->input('idSupplierContact', array('placeholder' => "id", 'class' => 'span1', 'id' => 'txtIdSupplierContact', 'type' => 'hidden'));
					echo $this->BootstrapForm->input('nameSupplierContact', array('placeholder' => "Nombre", 'class' => 'span3', 'id' => 'txtNameSupplierContact'));
					echo $this->BootstrapForm->input('titleSupplierContact', array('placeholder' => "Cargo", 'class' => 'span3', 'id' => 'txtTitleSupplierContact'));
					echo $this->BootstrapForm->input('phoneSupplierContact', array('placeholder' => "Telefono", 'class' => 'span2', 'id' => 'txtPhoneSupplierContact'));
					echo $this->BootstrapForm->input('emailSupplierContact', array('placeholder' => "Correo electrónico", 'class' => 'span2', 'id' => 'txtEmailSupplierContact'));
					echo $this->BootstrapForm->submit('<i class="icon-plus icon-white"></i> ', array('id' => 'btnAddSupplierContact', 'class' => 'btn btn-primary', 'div' => false, 'title' => 'Nuevo Empleado'));
					echo $this->BootstrapForm->submit('Guardar', array('id' => 'btnEditSupplierContact', 'class' => 'btn btn-primary', 'div' => false, 'style' => 'display:none;'));
					echo $this->BootstrapForm->submit('Cancelar', array('id' => 'btnCancelSupplierContact', 'class' => 'btn btn-cancel', 'div' => false, 'style' => 'display:none;'));
					echo $this->BootstrapForm->end();
					echo '<span id="boxProcessingSupplierContact"></span>';
					echo '<div id="boxMessageSupplierContact"></div>';
					?>
					<table class="table table-striped table-bordered table-hover" id="tblSupplierContacts">
						<thead>
							<tr>
								<th>#</th>
								<th>Nombre</th>
								<th>Cargo</th>
								<th>Telefono</th>
								<th>Correo Electrónico</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($contacts as $keyContact => $contact){?>
							<tr id="rowContact<?php echo $contact['InvSupplierContact']['id'];?>">
								<td  style="text-align: center;"><span class="spaNumber"><?php echo ($keyContact + 1);?></span> <input type="hidden" value="<?php echo $contact['InvSupplierContact']['id'];?>" class="spaIdContact"></td>
								<td><span class="spaNameContact"><?php echo $contact['InvSupplierContact']['name'];?></span></td>
								<td><span class="spaTitleContact"><?php echo $contact['InvSupplierContact']['job_title'];?></span></td>
								<td><span class="spaPhoneContact"><?php echo $contact['InvSupplierContact']['phone'];?></span></td>
								<td><span class="spaEmailContact"><?php echo $contact['InvSupplierContact']['email'];?></span></td>
								<td>
									<?php
									echo $this->Html->link('<i class="icon-pencil icon-white"></i>', array('action' => 'vsave'), array('class' => 'btn btn-primary btnRowEditContact', 'escape' => false, 'title' => 'Editar'));
									echo ' ' . $this->Html->link('<i class="icon-trash icon-white"></i>', array('action' => 'vsave'), array('class' => 'btn btn-danger btnRowDeleteContact', 'escape' => false, 'title' => 'Eliminar'));
									?>
								</td>
							</tr>
							<?php }?>
						</tbody>
					</table>		
					
				</div>
			</div>
		</div> 


	</div>
</div>
<?php echo $this->BootstrapForm->end(); ?>



<?php 
class AppSchema extends CakeSchema {

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $adm_actions = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'adm_controller_id' => array('type' => 'integer', 'null' => false),
		'name' => array('type' => 'string', 'null' => false, 'length' => 60),
		'description' => array('type' => 'string', 'null' => false, 'length' => 60),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'uc_admactions_controllerid_name' => array('unique' => true, 'column' => array('adm_controller_id', 'name'))
		),
		'tableParameters' => array()
	);

	public $adm_areas = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 50),
		'parent_area' => array('type' => 'integer', 'null' => false),
		'period' => array('type' => 'string', 'null' => false, 'length' => 8),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'uc_admareas_name_period' => array('unique' => true, 'column' => array('name', 'period'))
		),
		'tableParameters' => array()
	);

	public $adm_controllers = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'adm_module_id' => array('type' => 'integer', 'null' => false),
		'name' => array('type' => 'string', 'null' => false, 'length' => 60),
		'initials' => array('type' => 'string', 'null' => false, 'length' => 5),
		'description' => array('type' => 'string', 'null' => false, 'length' => 60),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'uc_admcontrollers_name' => array('unique' => true, 'column' => 'name')
		),
		'tableParameters' => array()
	);

	public $adm_error_messages = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'adm_module_id' => array('type' => 'integer', 'null' => false),
		'code' => array('type' => 'string', 'null' => true, 'length' => 20),
		'description' => array('type' => 'string', 'null' => true, 'length' => 180),
		'reason' => array('type' => 'string', 'null' => true, 'length' => 350),
		'course_to_follow' => array('type' => 'string', 'null' => true, 'length' => 350),
		'origin' => array('type' => 'string', 'null' => true, 'length' => 100),
		'comments' => array('type' => 'string', 'null' => true, 'length' => 350),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $adm_exchange_rates = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'currency' => array('type' => 'integer', 'null' => false),
		'value' => array('type' => 'float', 'null' => false),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'date' => array('type' => 'date', 'null' => false),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $adm_menus = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'adm_module_id' => array('type' => 'integer', 'null' => false),
		'adm_action_id' => array('type' => 'integer', 'null' => true),
		'name' => array('type' => 'string', 'null' => false, 'length' => 60),
		'order_menu' => array('type' => 'integer', 'null' => false),
		'parent_node' => array('type' => 'integer', 'null' => true),
		'inside' => array('type' => 'integer', 'null' => true),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'icon' => array('type' => 'string', 'null' => true, 'length' => 30),
		'phone' => array('type' => 'text', 'null' => true, 'length' => 1),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'uc_admmenus_actionid_name' => array('unique' => true, 'column' => array('adm_action_id', 'name'))
		),
		'tableParameters' => array()
	);

	public $adm_modules = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 15),
		'initials' => array('type' => 'string', 'null' => false, 'length' => 5),
		'description' => array('type' => 'string', 'null' => false, 'length' => 60),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'uc_admmodules_initials' => array('unique' => true, 'column' => 'initials')
		),
		'tableParameters' => array()
	);

	public $adm_parameter_details = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'adm_parameter_id' => array('type' => 'integer', 'null' => false),
		'par_int1' => array('type' => 'integer', 'null' => true),
		'par_int2' => array('type' => 'integer', 'null' => true),
		'par_char1' => array('type' => 'text', 'null' => true),
		'par_char2' => array('type' => 'text', 'null' => true),
		'par_num1' => array('type' => 'float', 'null' => true),
		'par_num2' => array('type' => 'float', 'null' => true),
		'par_bool1' => array('type' => 'boolean', 'null' => true),
		'par_bool2' => array('type' => 'boolean', 'null' => true),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $adm_parameters = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 50),
		'description' => array('type' => 'string', 'null' => false),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $adm_periods = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 8),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'uc_admperiods_name' => array('unique' => true, 'column' => 'name')
		),
		'tableParameters' => array()
	);

	public $adm_profiles = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'adm_user_id' => array('type' => 'integer', 'null' => false),
		'first_name' => array('type' => 'string', 'null' => false, 'length' => 60),
		'last_name1' => array('type' => 'string', 'null' => false, 'length' => 50),
		'last_name2' => array('type' => 'string', 'null' => false, 'length' => 50),
		'birthdate' => array('type' => 'date', 'null' => false),
		'birthplace' => array('type' => 'string', 'null' => true, 'length' => 160),
		'address' => array('type' => 'string', 'null' => true, 'length' => 160),
		'di_number' => array('type' => 'integer', 'null' => false),
		'di_place' => array('type' => 'string', 'null' => false, 'length' => 80),
		'email' => array('type' => 'string', 'null' => false, 'length' => 60),
		'phone' => array('type' => 'string', 'null' => true, 'length' => 60),
		'job' => array('type' => 'string', 'null' => false, 'length' => 60),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'location' => array('type' => 'string', 'null' => true, 'length' => 300),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'uc_admprofiles_dinumber' => array('unique' => true, 'column' => 'di_number')
		),
		'tableParameters' => array()
	);

	public $adm_roles = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 15),
		'description' => array('type' => 'string', 'null' => false, 'length' => 60),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'uc_admroles_name' => array('unique' => true, 'column' => 'name')
		),
		'tableParameters' => array()
	);

	public $adm_roles_actions = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'adm_role_id' => array('type' => 'integer', 'null' => false),
		'adm_action_id' => array('type' => 'integer', 'null' => false),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'uc_admrolesactions_roleid_actionid' => array('unique' => true, 'column' => array('adm_role_id', 'adm_action_id'))
		),
		'tableParameters' => array()
	);

	public $adm_roles_menus = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'adm_role_id' => array('type' => 'integer', 'null' => false),
		'adm_menu_id' => array('type' => 'integer', 'null' => false),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'uc_admrolesmenus_roleid_menuid' => array('unique' => true, 'column' => array('adm_role_id', 'adm_menu_id'))
		),
		'tableParameters' => array()
	);

	public $adm_roles_transactions = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'adm_role_id' => array('type' => 'integer', 'null' => false),
		'adm_transaction_id' => array('type' => 'integer', 'null' => false),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'string', 'null' => true, 'length' => 30),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'uc_admrolestransactions_roleid_transactionid' => array('unique' => true, 'column' => array('adm_role_id', 'adm_transaction_id'))
		),
		'tableParameters' => array()
	);

	public $adm_states = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'adm_controller_id' => array('type' => 'integer', 'null' => false),
		'name' => array('type' => 'string', 'null' => false, 'length' => 30),
		'description' => array('type' => 'string', 'null' => false, 'length' => 60),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'uc_admstates_controllerid_name' => array('unique' => true, 'column' => array('adm_controller_id', 'name'))
		),
		'tableParameters' => array()
	);

	public $adm_transactions = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'adm_controller_id' => array('type' => 'integer', 'null' => false),
		'name' => array('type' => 'string', 'null' => false, 'length' => 15),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'uc_admtransactions_controllerid_name_sentence' => array('unique' => true, 'column' => array('adm_controller_id', 'name'))
		),
		'tableParameters' => array()
	);

	public $adm_transitions = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'adm_state_id' => array('type' => 'integer', 'null' => false),
		'adm_transaction_id' => array('type' => 'integer', 'null' => false),
		'adm_final_state_id' => array('type' => 'integer', 'null' => false),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'uc_admtransitions_stateid_transactionid_finalstateid' => array('unique' => true, 'column' => array('adm_state_id', 'adm_transaction_id', 'adm_final_state_id'))
		),
		'tableParameters' => array()
	);

	public $adm_user_logs = array(
		'id' => array('type' => 'biginteger', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'tipo' => array('type' => 'string', 'null' => false, 'length' => 30),
		'success' => array('type' => 'integer', 'null' => false, 'default' => '1'),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $adm_user_restrictions = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'adm_user_id' => array('type' => 'integer', 'null' => false),
		'adm_role_id' => array('type' => 'integer', 'null' => false),
		'adm_area_id' => array('type' => 'integer', 'null' => false),
		'selected' => array('type' => 'integer', 'null' => false),
		'active' => array('type' => 'integer', 'null' => false),
		'active_date' => array('type' => 'datetime', 'null' => false),
		'period' => array('type' => 'string', 'null' => false, 'length' => 8),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'uc_admuserrestrictions_userid_roleid_areaid_period' => array('unique' => true, 'column' => array('adm_user_id', 'adm_role_id', 'adm_area_id', 'period'))
		),
		'tableParameters' => array()
	);

	public $adm_users = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'login' => array('type' => 'string', 'null' => false, 'length' => 15),
		'password' => array('type' => 'string', 'null' => false, 'length' => 256),
		'active' => array('type' => 'integer', 'null' => false),
		'active_date' => array('type' => 'datetime', 'null' => false),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'uc_admusers_login' => array('unique' => true, 'column' => 'login')
		),
		'tableParameters' => array()
	);

	public $inv_brands = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 80),
		'description' => array('type' => 'string', 'null' => false, 'length' => 300),
		'country_source' => array('type' => 'string', 'null' => false, 'length' => 120),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'color' => array('type' => 'string', 'null' => true, 'length' => 7),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $inv_categories = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 80),
		'description' => array('type' => 'string', 'null' => false, 'length' => 400),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'color' => array('type' => 'string', 'null' => true, 'length' => 7),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $inv_items = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'inv_brand_id' => array('type' => 'integer', 'null' => false),
		'inv_category_id' => array('type' => 'integer', 'null' => false),
		'code' => array('type' => 'string', 'null' => false, 'length' => 100),
		'name' => array('type' => 'string', 'null' => false, 'length' => 80),
		'description' => array('type' => 'string', 'null' => false, 'length' => 600),
		'picture' => array('type' => 'string', 'null' => true, 'length' => 250),
		'min_quantity' => array('type' => 'integer', 'null' => true),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'stock' => array('type' => 'integer', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $inv_items_suppliers = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'inv_supplier_id' => array('type' => 'integer', 'null' => false),
		'inv_item_id' => array('type' => 'integer', 'null' => false),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $inv_movement_details = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'inv_item_id' => array('type' => 'integer', 'null' => false),
		'inv_movement_id' => array('type' => 'integer', 'null' => false),
		'quantity' => array('type' => 'integer', 'null' => false),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'cif_price' => array('type' => 'float', 'null' => true),
		'ex_cif_price' => array('type' => 'float', 'null' => true),
		'ex_fob_price' => array('type' => 'float', 'null' => true),
		'ex_sale_price' => array('type' => 'float', 'null' => true),
		'fob_price' => array('type' => 'float', 'null' => true),
		'sale_price' => array('type' => 'float', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $inv_movement_types = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 60),
		'status' => array('type' => 'string', 'null' => false, 'length' => 20),
		'document' => array('type' => 'integer', 'null' => false),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $inv_movements = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'inv_warehouse_id' => array('type' => 'integer', 'null' => false),
		'inv_movement_type_id' => array('type' => 'integer', 'null' => false),
		'document_code' => array('type' => 'string', 'null' => false, 'length' => 60),
		'code' => array('type' => 'string', 'null' => false, 'length' => 100),
		'date' => array('type' => 'datetime', 'null' => false),
		'description' => array('type' => 'string', 'null' => false, 'length' => 600),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'ex_rate' => array('type' => 'float', 'null' => true),
		'type' => array('type' => 'integer', 'null' => true),
		'note_code' => array('type' => 'string', 'null' => true, 'length' => 100),
		'clone' => array('type' => 'string', 'null' => true, 'length' => 100),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $inv_price_types = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 60),
		'description' => array('type' => 'string', 'null' => false, 'length' => 250),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $inv_prices = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'inv_item_id' => array('type' => 'integer', 'null' => false),
		'inv_price_type_id' => array('type' => 'integer', 'null' => false),
		'price' => array('type' => 'float', 'null' => false),
		'description' => array('type' => 'string', 'null' => false),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'date' => array('type' => 'datetime', 'null' => true),
		'ex_price' => array('type' => 'float', 'null' => true),
		'ex_rate' => array('type' => 'float', 'null' => true),
		'location' => array('type' => 'string', 'null' => true, 'length' => 300),
		'code' => array('type' => 'string', 'null' => true, 'length' => 100),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $inv_supplier_contacts = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'inv_supplier_id' => array('type' => 'integer', 'null' => false),
		'name' => array('type' => 'string', 'null' => false, 'length' => 120),
		'phone' => array('type' => 'string', 'null' => false, 'length' => 60),
		'job_title' => array('type' => 'string', 'null' => false, 'length' => 80),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'email' => array('type' => 'string', 'null' => true, 'length' => 50),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $inv_suppliers = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 100),
		'location' => array('type' => 'string', 'null' => true, 'length' => 300),
		'phone' => array('type' => 'string', 'null' => false, 'length' => 60),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'area' => array('type' => 'string', 'null' => true, 'length' => 100),
		'country' => array('type' => 'string', 'null' => true, 'length' => 100),
		'email' => array('type' => 'string', 'null' => true, 'length' => 50),
		'website' => array('type' => 'string', 'null' => true, 'length' => 100),
		'address' => array('type' => 'string', 'null' => true, 'length' => 100),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $inv_warehouses = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 200),
		'location' => array('type' => 'string', 'null' => false, 'length' => 300),
		'address' => array('type' => 'string', 'null' => false, 'length' => 100),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $pur_details = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'pur_purchase_id' => array('type' => 'integer', 'null' => false),
		'inv_item_id' => array('type' => 'integer', 'null' => false),
		'quantity' => array('type' => 'integer', 'null' => false),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => true),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'cif_price' => array('type' => 'float', 'null' => true),
		'ex_cif_price' => array('type' => 'float', 'null' => true),
		'ex_fob_price' => array('type' => 'float', 'null' => true),
		'ex_sale_price' => array('type' => 'float', 'null' => true),
		'fob_price' => array('type' => 'float', 'null' => true),
		'sale_price' => array('type' => 'float', 'null' => true),
		'inv_supplier_id' => array('type' => 'integer', 'null' => false),
		'ex_subtotal' => array('type' => 'float', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $pur_payment_types = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 60),
		'description' => array('type' => 'string', 'null' => false, 'length' => 250),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $pur_payments = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'pur_purchase_id' => array('type' => 'integer', 'null' => false),
		'pur_payment_type_id' => array('type' => 'integer', 'null' => false),
		'due_date' => array('type' => 'date', 'null' => true),
		'amount' => array('type' => 'float', 'null' => false),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'date' => array('type' => 'date', 'null' => false),
		'description' => array('type' => 'string', 'null' => true, 'length' => 600),
		'ex_amount' => array('type' => 'float', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $pur_prices = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'inv_price_type_id' => array('type' => 'integer', 'null' => false),
		'pur_purchase_id' => array('type' => 'integer', 'null' => false),
		'amount' => array('type' => 'float', 'null' => false),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'ex_amount' => array('type' => 'float', 'null' => true),
		'date' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $pur_purchases = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'code' => array('type' => 'string', 'null' => false, 'length' => 100),
		'doc_code' => array('type' => 'string', 'null' => false, 'length' => 100),
		'date' => array('type' => 'datetime', 'null' => false),
		'description' => array('type' => 'string', 'null' => false, 'length' => 600),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'CREATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => true),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'note_code' => array('type' => 'string', 'null' => true, 'length' => 100),
		'ex_rate' => array('type' => 'float', 'null' => true),
		'discount' => array('type' => 'float', 'null' => false),
		'inv_warehouse_id' => array('type' => 'integer', 'null' => false, 'default' => '2'),
		'discount_type' => array('type' => 'string', 'null' => true, 'length' => 20),
		'location' => array('type' => 'string', 'null' => true, 'length' => 300),
		'paid' => array('type' => 'boolean', 'null' => true),
		'clone' => array('type' => 'string', 'null' => true, 'length' => 100),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $sal_customers = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 100),
		'address' => array('type' => 'string', 'null' => true, 'length' => 300),
		'phone' => array('type' => 'string', 'null' => true, 'length' => 80),
		'location' => array('type' => 'string', 'null' => true, 'length' => 300),
		'email' => array('type' => 'string', 'null' => true, 'length' => 50),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'description' => array('type' => 'string', 'null' => true, 'length' => 600),
		'area' => array('type' => 'string', 'null' => true, 'length' => 300),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $sal_details = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'sal_sale_id' => array('type' => 'integer', 'null' => false),
		'inv_item_id' => array('type' => 'integer', 'null' => false),
		'quantity' => array('type' => 'integer', 'null' => false),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => true),
		'date_created' => array('type' => 'datetime', 'null' => true, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'inv_warehouse_id' => array('type' => 'integer', 'null' => false),
		'cif_price' => array('type' => 'float', 'null' => true),
		'ex_cif_price' => array('type' => 'float', 'null' => true),
		'ex_fob_price' => array('type' => 'float', 'null' => true),
		'ex_sale_price' => array('type' => 'float', 'null' => true),
		'fob_price' => array('type' => 'float', 'null' => true),
		'sale_price' => array('type' => 'float', 'null' => true),
		'backorder' => array('type' => 'integer', 'null' => true, 'default' => '0'),
		'approved' => array('type' => 'integer', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $sal_employees = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'sal_customer_id' => array('type' => 'integer', 'null' => false),
		'name' => array('type' => 'string', 'null' => false, 'length' => 300),
		'phone' => array('type' => 'string', 'null' => true, 'length' => 80),
		'email' => array('type' => 'string', 'null' => true, 'length' => 50),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $sal_invoices = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'sal_code' => array('type' => 'string', 'null' => false, 'length' => 100),
		'invoice_number' => array('type' => 'string', 'null' => true, 'length' => 100),
		'date' => array('type' => 'datetime', 'null' => true),
		'name' => array('type' => 'string', 'null' => true, 'length' => 80),
		'nit' => array('type' => 'string', 'null' => true, 'length' => 100),
		'total' => array('type' => 'float', 'null' => true),
		'description' => array('type' => 'string', 'null' => true, 'length' => 600),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $sal_payment_types = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 60),
		'description' => array('type' => 'string', 'null' => true, 'length' => 250),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $sal_payments = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'sal_payment_type_id' => array('type' => 'integer', 'null' => false),
		'sal_sale_id' => array('type' => 'integer', 'null' => false),
		'description' => array('type' => 'string', 'null' => true, 'length' => 250),
		'amount' => array('type' => 'float', 'null' => false),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => true),
		'date_created' => array('type' => 'datetime', 'null' => true, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'date' => array('type' => 'date', 'null' => false),
		'due_date' => array('type' => 'date', 'null' => true),
		'ex_amount' => array('type' => 'float', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $sal_sales = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'sal_employee_id' => array('type' => 'integer', 'null' => false),
		'sal_tax_number_id' => array('type' => 'integer', 'null' => false),
		'code' => array('type' => 'string', 'null' => false, 'length' => 100),
		'doc_code' => array('type' => 'string', 'null' => false, 'length' => 100),
		'date' => array('type' => 'datetime', 'null' => false),
		'description' => array('type' => 'string', 'null' => true, 'length' => 600),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => true),
		'date_created' => array('type' => 'datetime', 'null' => true, 'default' => 'now()'),
		'modifier' => array('type' => 'integer', 'null' => true),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'salesman_id' => array('type' => 'integer', 'null' => true),
		'note_code' => array('type' => 'string', 'null' => true, 'length' => 100),
		'ex_rate' => array('type' => 'float', 'null' => true),
		'discount' => array('type' => 'float', 'null' => false),
		'invoice' => array('type' => 'boolean', 'null' => true),
		'reserve' => array('type' => 'boolean', 'null' => true, 'default' => false),
		'deliver' => array('type' => 'boolean', 'null' => true),
		'discount_type' => array('type' => 'string', 'null' => true, 'length' => 20),
		'invoice_percent' => array('type' => 'float', 'null' => true),
		'location' => array('type' => 'string', 'null' => true, 'length' => 300),
		'paid' => array('type' => 'boolean', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $sal_tax_numbers = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'sal_customer_id' => array('type' => 'integer', 'null' => false),
		'nit' => array('type' => 'string', 'null' => false, 'length' => 100),
		'name' => array('type' => 'string', 'null' => true, 'length' => 80),
		'lc_state' => array('type' => 'string', 'null' => false, 'default' => 'ELABORATED', 'length' => 30),
		'lc_transaction' => array('type' => 'string', 'null' => false, 'default' => 'CREATE', 'length' => 30),
		'creator' => array('type' => 'integer', 'null' => false),
		'date_created' => array('type' => 'datetime', 'null' => false, 'default' => 'now()'),
		'date_modified' => array('type' => 'datetime', 'null' => true),
		'modifier' => array('type' => 'integer', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

}

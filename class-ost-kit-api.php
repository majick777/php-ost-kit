<?php

// =========================
// === OST KIT alpha API ===
// === PHP Wrapper Class ===
// =========================
// ===== Version 1.0.0 =====
// =========================
//
// Usage Note: define constants before instantiating and/or arguments while instantiating.
// ---------------------------------------------------------------------------------------
// Constant				Class Arg	Req/Opt		Format			Default
// ---------------------------------------------------------------------------------------
// -------------------- endpoint	REQUIRED	string			n/a
// -------------------- format		Optional	'json'/'array'	'array'
// === API Details ===
// OST_KIT_KEY			api_key		REQUIRED	string			n/a
// OST_KIT_SECRET		api_secret	REQUIRED	string			n/a
// OST_KIT_URL			api_url		Optional	URL				'https://playgroundapi.ost.com' (sandbox)
// OST_KIT_NETID		network_id	Optional 	number			1409 (test network?)
// === Connection ===
// OST_KIT_PORT			port		Optional	number			false
// OST_KIT_CONNECTTIME	connecttime	Optional	number			30
// OST_KIT_TIMEOUT		timeout		Optional	number			15
// === Debugging ===
// OST_KIT_DEBUG_LOG 	debug_log	Optional	boolean			true
// OST_KIT_DEBUG_PATH 	debug_path	Optional	string			/class-file-path/debug.log
// ---------------------------------------------------------------------------------------

// Important OST Kit Alpha Note for /transaction-types/execute endpoint!
// "On a successful acknowledgement the transaction_uuid must be queried on /transaction-types/status
// for completion of the transaction."


// ---------------------------
// === OST Kit Query Class ===
// ---------------------------
if (!class_exists('OST_Query')) {
 class OST_Query {

	// ---------------
	// Class Variables
	// ---------------

	// API Key
	// -------
	private $api_key = null;

	// API Secret
	// ----------
	private $api_secret = null;

	// API URL
	// -------
	protected $api_url = 'https://playgroundapi.ost.com';

	// Network ID
	// ----------
	protected $network_id = null;

	// Connection Port
	// ---------------
	public $port = false;

	// Connection Timeout
	// ------------------
	public $connecttime = 30;

	// Response Timeout
	// ----------------
	public $timeout = 15;

	// Result Format
	// -------------
	public $result_format = 'array';

	// Debug Switchmode
	// ----------------
	public $debug_log = true;

	// Debug Log Filepath
	// ------------------
	public $debug_path = false;

	// Errors Switch
	// -------------
	public $error = false;


	// === Class Constructor ===
	// -------------------------
	function __construct($args) {

		// check for required endpoint argument
		// ------------------------------------
		if (!isset($args['endpoint'])) {return false;}
		$endpoint = $args['endpoint'];

		// maybe set Result Format
		// -----------------------
		// (optional, default 'array')
		if (isset($args['format'])) {
			$args['format'] = strtolower($args['format']);
			if ($args['format'] == 'json') {$this->result_format = 'json';}
			elseif ($args['format'] != 'array') {
				$this->debug_log("Failed! Invalid API Result Format requested.", $args['format']);
				return false;
			}
		}

		// === API Details ===
		// -------------------

		// set API Key (required)
		// ----------------------
		if (isset($args['api_key'])) {$this->api_key = $args['api_key'];}
		elseif (defined('OST_KIT_KEY')) {$this->api_key = OST_KIT_KEY;}
		else {
			$this->debug_log("Error! Class Instantiated without an API Key!", $args);
			throw new \Exception("Error! OST Kit API Class onstantiated without an API Key!");
			return false;
		}

		// set API Secret (required)
		// -------------------------
		if (isset($args['api_secret'])) {$this->api_secret = $args['api_secret'];}
		elseif (defined('OST_KIT_SECRET')) {$this->api_secret = OST_KIT_SECRET;}
		else {
			$this->debug_log("Error! Class Instantiated without an API Secret!", $args);
			throw new \Exception("Error! OST Kit API Class instantiated without an API Secret!");
			return false;
		}

		// maybe set API URL
		// -----------------
		// (optional, default 'https://playgroundapi.ost.com'
		if (isset($args['api_url'])) {$this->api_url = $args['api_url'];}
		elseif (defined('OST_KIT_URL')) {$this->api_url = OST_KIT_URL;}


		// maybe set Network ID
		// --------------------
		// (optional, default 1409)
		if (isset($args['network_id'])) {$this->network_id = $args['network_id'];}
		elseif (defined('OST_KIT_NETID')) {$this->api_url = OST_KIT_NETID;}

		// === Connection ===
		// ------------------

		// maybe set Outgoing Connection Port
		// ----------------------------------
		if (isset($args['port'])) {$this->port = $args['port'];}
		elseif (defined('OST_KIT_PORT')) {$this->port = ost_kit_PORT;}

		// maybe set Connection Timeout
		// ----------------------------
		if (isset($args['connecttime'])) {$this->connecttime = $args['connecttime'];}
		elseif (defined('OST_KIT_CONNECTTIME')) {$this->connecttime = OST_KIT_CONNECTTIME;}

		// maybe set Request Timeout
		// -------------------------
		if (isset($args['timeout'])) {$this->timeout = $args['timeout'];}
		elseif (defined('OST_KIT_CONNECTTIME')) {$this->timeout = OST_KIT_CONNECTTIME;}


		// === Debugging ===
		// -----------------

		// maybe set Debug Log Switch
		// --------------------------
		// (optional, default true)
		if (isset($args['debug_log'])) {
			if (!$args['debug_log']) {$this->debug_log = false;}
		} elseif (defined('OST_KIT_DEBUG_LOG')) {
			if (!OST_KIT_DEBUG_LOG) {$this->debug_log = false;}
		}

		// maybe set Debug Filepath
		// ------------------------
		// (optional, default to class file path)
		if (isset($args['debug_path']) && $args['debug_path']) {
			if (is_dir(dirname($args['debug_path']))) {$this->debug_path = $args['debug_path'];}
			else {$this->debug_log("Warning: Debug File Path does not exist:", $args['debug_path']);}
		} elseif (defined('OST_KIT_DEBUG_PATH') && OST_KIT_DEBUG_PATH) {
			if (is_dir(dirname(OST_KIT_DEBUG_PATH))) {$this->debug_path = OST_KIT_DEBUG_PATH;}
			else {$this->debug_log("Warning: Debug File Path does not exist:", OST_KIT_DEBUG_PATH);}
		} else {$this->debug_path = dirname(__FILE__).'/debug.log';}


		# ------------------#
		# OST KIT ENDPOINTS #
		# ------------------#
		# /users/create
		# /users/edit
		# /users/list
		# /users/airdrop/drop
		# /users/airdrop/status
		# /transaction-types/create
		# /transaction-types/edit
		# /transaction-types/list
		# /transaction-types/execute
		# /transaction-types/status

		// Switch Endpoint to Send Query
		// -----------------------------
		// (with friendly name support)
		switch ($endpoint) {

			// USERS
			// -----
			case '/users/create':
			case 'create_user':
			case 'user_create':
				# required argument: name
				return $this->create_user($args);
			case '/users/edit':
			case 'edit_user':
			case 'user_edit':
				# required argument: name
				return $this->edit_user($args);
			case '/users/list':
			case 'user_list':
			case 'users_list':
			case 'list_users':
				# required arguments: page_no
				# optional arguments: filter (all/never_airdropped), order_by(creation_time/name), order(asc/desc)
				return $this->list_users($args);
			case '/users/balance':
			case 'token_balance':
				# required argument: uuid
				return $this->token_balance($args);

			// AIRDROPS
			// --------
			case '/users/airdrop/drop':
			case 'airdrop_drop':
				# required arguments: amount
				# optional arguments: list_type (all/never_airdropped)
				return $this->airdrop_drop($args);
			case '/users/airdrop/status':
			case 'airdrop_status':
				# required arguments: airdrop_uuid
				return $this->airdrop_status($args);

			// TRANSACTION TYPES
			// -----------------
			case '/transaction-types/create':
			case 'create_transaction_type':
				# required arguments: name, kind, currency_type, currency_value
				# special argument: commission_percent (only for user-to-user kind)
				return $this->create_transaction_type($args);
			case '/transaction-types/edit':
			case 'edit_transaction_type':
				# required arguments: client_transaction_id
				# optional arguments: name, kind, currency_type, currency_value
				# special optional argument: commission_percent (only for user-to-user kind)
				return $this->edit_transaction_type($args);
			case '/transaction-types/list':
			case 'list_transaction_types':
				# no arguments
				return $this->list_transaction_types();

			// TRANSACTIONS
			// ------------
			case '/transaction-types/execute':
			case 'transaction_execute':
			case 'execute_transaction':
				# required arguments: from_uuid, to_uuid, transaction_kind
				return $this->transaction_execute($args);
			case '/transaction-types/status':
			case 'transaction_status':
			case 'status_transaction':
				# required arguments: transaction_uuids (array!)
				return $this->transaction_status($args);
		}

		// oops, no endpoint match was found
		$this->debug_log("Error! No API Endpoint Match Found.", $args['endpoint']);
		throw new \Exception("Error! No API Endpoint Match Found.");
		return false;

	}

	// -------------
	// Debug Logging
	// -------------
	function debug_log($message, $data=false) {

		// set the error switch if we had fail/error
		if ( (strstr($message, 'Failed')) || (strstr($message, 'Error')) ) {$this->error = true;}

		// maybe log message to the debug log
		if (!$this->debug_log) {return;}
		if ($data) {
			if (is_string($data)) {$message .= ' '.$data.PHP_EOL;}
			elseif (is_array($data) || is_object($data)) {
				$message .= PHP_EOL.print_r($data, true).PHP_EOL;
			}
		}
		error_log($message, 3, $this->log_path);

	}


	# ------------------
	# API USER ENDPOINTS
	# ------------------
	# /users/create
	# /users/edit
	# /users/list
	# ------------------

	// ----------------------------------
	// Validate Username/Transaction Name
	// ----------------------------------
	function validate_name($name, $action) {

		// make sure name is not empty
		$name = trim($name);
		if (empty($name)) {$this->debug_log("Failed! ".$action." name argument is empty."); return false;}

		// alphanumeric string match (with spaces allowed)
		// ? also alphanumeric with spaces for transaction type names ?
		if (!preg_match('/^[0-9a-zA-Z ]+$/i', $name)) {
			$this->debug_log("Failed! ".$action." name must be letters numbers and spaces only.", $name); return false;
		}

		// 20 character username limit
		// ? also 20 character limit for transaction type names ?
		if (strlen($name) > 20) {
			$this->debug_log("Failed! ".$action." name exceeds 20 character limit.", $name); return false;
		}

		return $name;
	}

	// -----------
	// Create User
	// -----------
	# /users/create
	function create_user($args) {

		// Username
		// --------
		$args['name'] = $this->validate_name($args['name'], 'Create Username');

		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send Create User Query to API
		// -----------------------------
		$data = $this->send_query('/users/create', array('name' => $name));
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# For api calls to /users/create the data.result_type is the string "economy_users"
		# and the key data.economy_users is an array of user objects. On successful creation of the user,
		# economy_users contains the created user as a single element.

		# User Object Attributes 	| Type		| Description
		# --------------------------+-----------+-----------------------------------------
		# name						| string	| name of the user
		# uuid						| string	| unique identifier for the user
		# total_airdropped_tokens	| number	| cumulative amount airdropped to the user
		# token_balance				| number	| balance of the user (including current airdrop budget)

	}

	// ---------
	// Edit User
	// ---------
	# /users/edit
	function edit_user($args) {

		// UUID (required)
		// ---------------
		if (!isset($args['uuid'])) {
			$this->debug_log("Failed! Edit User Endpoint requires UUID.", $args);
			return false;
		}

		// New Username
		// ------------
		$args['name'] = $this->validate_name($args['name'], 'Edit Username');

		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send Edit User Query to API
		// ---------------------------
		$data = $this->send_query('/users/create', array('uuid' => $args['uuid'], 'name' => $args['name']));
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# For api calls to /users/edit the data.result_type is the string "economy_users"
		# and the key data.economy_users is an array of user objects.
		# On successful edit of a user, economy_users contains the edited user as a single element.

		# (see User Object attributes in Create User method)

	}

	// ----------
	// List Users
	// ----------
	# /users/list
	function list_users($args) {

		# Input Parameter	| Type		| Description
		# ------------------+-----------+----------------------------
		# page_no			| number	| page number (starts from 1)
		# filter			| string	| (optional) filter to be applied on list. Possible values: 'all' or 'never_airdropped' (default)
		# order_by			| string	| (optional) order the list by 'creation_time' or 'name' (default)
		# order				| string	| (optional) order users in 'desc' (default) or 'asc' order.

		// Page Number
		// -----------
		if (!isset($args['page_no'])) {$this->debug_log("Failed! User Listing Endpoint requires page number.");}
		$page_no = abs(intval($args['page_no']));
		if ($page_no < 1) {$this->debug_log("Failed! User Listing page number must be 1 or over.");}
		else {$parameters['page_no'] = $args['page_no'];}

		// Filter, Order By, Order
		// -----------------------
		if (isset($args['filter'])) {
			if ( ($args['filter'] != 'all') && ($args['filter'] != 'never_airdropped') ) {
				$this->debug_log("Warning! User List Endpoint incorrect 'filter' parameter value.", $args['filter']);
			} else {$parameters['filter'] = $args['filter'];}
		}
		if (isset($args['order_by'])) {
			if ( ($args['order_by'] != 'creation_time') && ($args['order_by'] != 'time') ) {
				$this->debug_log("Warning! User List Endpoint incorrect 'order_by' parameter value.", $args['order_by']);
			} else {$parameters['order_by'] = $args['order_by'];}
		}
		if (isset($args['order'])) {
			if ( ($args['order'] != 'asc') && ($args['order'] != 'desc') ) {
				$this->debug_log("Warning! User List Endpoint incorrect 'order' Parameter value.", $args['order']);
			} else {$parameters['order'] = $args['order'];}
		}

		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send User List Query
		// --------------------
		$data = $this->send_query('/users/list', $parameters, 'get');
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# For api calls to /users/list the data.result_type is the string "economy_users"
		# and the key data.economy_users is an array of the returned user objects (25 users per page).
		# The field data.meta.next_page_payload contains the filter and order information
		# and the page_no number for the next page; or is empty for the last page of the list.

		# (see User Object attributes in Create User method)

	}

	// --------------
	// List All Users
	// --------------
	// loop all list_user pages
	function list_all_users($args) {

		$args['page_no'] = 1;
		$datapages = $userlist = array();
		// get all users by default (not just never_airdropped)
		if (!isset($args['filter'])) {$args['filter'] = 'all';}

		while ($args['page_no'] != '') {
			$data = $this->list_users($args);
			$page_no = '';
			if ($this->result_format == 'json') {
				$datapages[] = $data;
				$arraydata = json_decode($data, true);
				if (isset($arraydata['data']['meta']['next_page_payload']['page_no'])) {
					$page_no = $arraydata['data']['meta']['next_page_payload']['page_no'];
				}
			} else {
				if (isset($data['data']['meta']['next_page_payload']['page_no'])) {
					$page_no = $data['data']['meta']['next_page_payload']['page_no'];
				}
				unset($data['meta']);
				$datapages[] = $data;
			}
			$args['page_no'] = $page_no;
		}

		if ($this->result_format == 'json') {
			// note: currently if set to return result_format in JSON,
			// all the data responses are returned as an array of JSON strings
			// TODO: merge data as an array and convert back to JSON string later?
			return $datapages;
		} else {
			// otherwise loop all data to merge and re-index economy_users data
			$i = 0;
			foreach ($datapages as $data) {
				if (isset($data['economy_users'])) {
					$userlist[$i] = $data['economy_users']; $i++;
				}
			}
			return $userlist;
		}
	}

	// ------------------
	// User Token Balance
	// ------------------
	// Note: not an actual endpoint, uses /users/edit endpoint
	function token_balance($args) {

		// ensure we have a UUID to match with
		if ( !isset($args['uuid']) || empty($args['uuid']) ) {
			$this->debug_log("Error! Token balance requires 'uuid' parameter.", $args); return false;
		} else {$uuid = $args['uuid'];}

		// make sure we get the userlist as an array here
		$this->result_format = 'array';
		if (isset($args['name'])) {unset($args['name']);}
		$data = $this->edit_users($args);

		// ensure we have a valid data
		if ( (!$data) || (!is_array($data)) || (!isset($data['data']['economy_users']['token_balance'])) ) {
			$this->debug_log("Error! Could not retrieve user token balance.", $args); return false;
		}

		return $data['data']['economy_users']['token_balance'];

	}


	# -----------------
	# Airdrop Endpoints
	# -----------------
	# /users/airdrop/drop
	# /users/airdrop/status

	// ------------
	// Airdrop Drop
	// ------------
	# /users/airdrop/drop
	function airdrop_drop($args) {

		# Parameter			| Type		| Description
		# ------------------+-----------+----------------------------------------------
		# amount			| Float		| The amount of BT that needs to be air-dropped to the selected end-users. Example:10
		# list_type			| String	| The list type of end-users that need to be airdropped tokens. Example:all

		// Amount
		// ------
		if (!isset($args['amount'])) {
			$this->debug_log("Failed! Airdrop amount is required.", $args); return false;
		} elseif (abs(intval($args['amount'])) < 1) {
			$this->debug_log("Failed! Airdrop amount must be 1 or over.", $args['amount']); return false;
		} else {$parameters['amount'] = $args['amount'];}

		# list_type Parameters	| Type		| Description
		# ----------------------+-----------+---------------------------------------------------------------
		# all					| String	| All the end-users that have been previously airdropped tokens.
		# never_airdropped		| String	| All the end-users that have never been previously airdropped tokens.

		// List Type
		// ---------
		if (!isset($args['list_type'])) {
			$this->debug_log("Failed! Airdrop list_type parameter must be specified.", $args); return false;
		} elseif ( ($args['list_type'] != 'all') && ($args['list_type'] != 'never_airdropped') ) {
			$this->debug_log("Failed! Airdrop list_type value is incorrect.", $args['list_type']); return false;
		} else {$parameters['list_type'] = $args['list_type'];}

		// Send Airdrop Query to API
		// -------------------------
		$data = $this->send_query('/users/airdrop/drop', $parameters);
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# On calling /users/airdrop/drop the data.airdrop_uuid is a string containing the airdrop reference id,
		# that can be used to check the airdrop status using the Airdrop Status API endpoint.

	}

	// --------------
	// Airdrop Status
	// --------------
	# /users/airdrop/status
	function airdrop_status($args) {

		// Airdrop UUID
		// ------------
		if (!isset($args['airdrop_uuid'])) {
			$this->debug_log("Failed! Airdrop Status Endpoint requires an airdrop_uuid.", $args); return false;
		}

		// Send Airdrop Status Query to API
		// --------------------------------
		$data = $this->send_query('/users/airdrop/drop', array('airdrop_uuid' => $args['airdrop_uuid']), 'get');
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# On calling /users/airdrop/status the data.airdrop_uuid is a string containing the airdrop reference id.
		# data.current_status is a string containing the present status of the airdrop request.
		# data.steps_complete is an array explaining the steps which have been completed for the airdrop
		# at the specific point in time of the API request.

		# current_status
		# pending			| String	| The string to represent that airdrop is still in process.
		# failed			| String	| The string to represent that the airdrop has failed.
		# complete			| String	| The string to represent that the airdrop process is complete.

		# steps_complete
		# user_identified	 | String	| The string to represent identification of the end-user for airdropping branded tokens.
		# tokens_transferred | String	| The string to represent that the branded tokens are tranferred to the airdrop budget holder address.
		# contract_approved	 | String	| The string to represent that the airdrop budget holder address has approved the airdrop contract.
		# allocation_done	 | String	| The string to represent that the airdrop process is complete.

	}


	# ---------------------
	# TRANSACTION ENDPOINTS
	# ---------------------
	# /transaction-types/create
	# /transaction-types/edit
	# /transaction-types/list
	# /transaction-types/execute
	# /transaction-types/status

	// -----------------------
	// Create Transaction Type
	// -----------------------
	function create_transaction_type($args) {

		# Input Parameter		| Type		| Description
		# ----------------------+-----------+-----------------------------
		# name					| string	| name of the transaction type
		# kind					| string	| transaction types can be one of three kinds: "user_to_user", "company_to_user", or "user_to_company" to clearly determine whether value flows within the application or from or to the company. On user to user transfers the company can ask a transaction fee.
		# currency_type			| string	| type of currency the transaction is valued in. Possible values are "USD" (fixed) or "BT" (floating). When a transaction type is set in fiat value the equivalent amount of branded tokens are calculated on-chain over a price oracle. A transaction fails if the price point is outside of the accepted margins set by the company (API not yet exposed). For OST KIT? price points are calculated by and taken from coinmarketcap.com and published to the contract by OST.com.
		# currency_value		| float		| value of the transaction set in "USD" (min USD 0.01 , max USD 100) or branded token "BT" (min BT 0.00001, max BT 100). The transfer on-chain always occurs in branded token and for fiat value is calculated to the equivalent amount of branded tokens at the moment of transfer. If the transaction type is between users and a commission percentage is set then the commission is inclusive in this value and the complement goes to the beneficiary user.
		# commission_percent	| float		| inclusive percentage of the value that is sent to company. Possible only for "user_to_user" transaction kind. (min 0%, max 100%)

		// Transaction Type Name
		// ---------------------
		$args['name'] = $this->validate_name($args['name'], 'Create Transaction Type');

		// Transaction Kind
		// ----------------
		$kinds = array('user_to_user', 'company_to_user', 'user_to_company');
		if (!isset($args['kind'])) {
			$this->debug_log("Failed! Create Transaction Type Endpoint requires a kind value. Be nice.");
		} elseif (!in_array($args['kind'], $kinds)) {
			$this->debug_log("Failed! Create Transaction Type Endpoint incorrect kind value.", $args['kind']);
		} else {$parameters['kind'] = $args['kind'];}

		// Currency Type
		// -------------
		$currencytypes = array('USD', 'BT');
		if (!isset($args['currency_type'])) {
			$this->debug_log("Failed! Create Transaction Type Endpoint requires a currency_type value.");
		} elseif (!in_array($args['currency_type'], $currencytypes)) {
			$this->debug_log("Failed! Create Transaction Type Endpoint incorrect currency_type value.", $args['currency_type']);
		} else {$parameters['currency_type'] = $args['currency_type'];}

		// Currency Value
		// --------------
		if (!isset($args['currency_value'])) {$this->debug_log("Failed! Create Transaction Type requires currency_value.");}
		else {
			$args['currency_value'] = abs(intval($args['currency_value']));
			if ($parameters['currency_type'] == 'USD') {
				if ($args['currency_value'] < 0.01) {
					$this->debug_log("Failed! Create Transaction Type for USD currency_value must be greater than 0.01", $args['currency_value']);
				} elseif ($args['currency_value'] > 100) {
					$this->debug_log("Failed! Create Transaction Type for USD currency_value must be less than 100.", $args['currency_value']);
				} else {$parameters['currency_value'] = $args['currency_value'];}
			} elseif ($parameters['currency_type'] == 'BT') {
				if ($args['currency_value'] < 0.00001) {
					$this->debug_log("Failed! Create Transaction Type for Branded Token currency_value must be greater than 0.00001", $args['currency_value']);
				} elseif ($args['currency_value'] > 100) {
					$this->debug_log("Failed! Create Transaction Type for Branded Token currency_value must be less than 100.", $args['currency_value']);
				} else {$parameters['currency_value'] = $args['currency_value'];}

			}
		}

		// Commission Percentage
		// ---------------------
		if ($args['kind'] == 'user_to_user') {
			if (!isset($args['commission_percentage'])) {$this->debug_log("Failed! Create Transaction Type for user_to_user requires commission_percentage.");}
			else {
				$args['commission_percentage'] = abs(intval($args['commission_percentage']));
				if ($args['commission_percentage'] < 0) {
					$this->debug_log("Failed! Create Transaction Type commission_percentage must be greater than 0.", $args['commission_percentage']);
				} elseif ($args['commission_percentage'] > 100) {
					$this->debug_log("Failed! Create Transaction Type commission_percentage must be less than 100.", $args['commission_percentage']);
				} else {$parameters['commission_percentage'] = $args['commission_percentage'];}
			}
		}

		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send Create Transaction Type Query to API
		// -----------------------------------------
		$data = $this->send_query('/transaction-types/create', $parameters);
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# On calling /transaction-types/create the data.result_type is the string "transactions"
		# and the key data.transactions is an array containing the created transaction type object.

		# Transactions Object	| Type		| Description
		# ----------------------+-----------+--------------------------------------------
		# id					| number	| identifier for the created transaction type
		# client_id				| number	| identifier of the authorised client
		# name					| string	| name of the transaction type
		# kind					| string	| transaction types can be one of three kinds: "user_to_user", "company_to_user", or "user_to_company" to clearly determine whether value flows within the application or from or to the company. On user to user transfers the company can ask a transaction fee.
		# currency_type			| string	| type of currency the transaction is valued in. Possible values are "USD" (fixed) or "BT" (floating). When a transaction type is set in fiat value the equivalent amount of branded tokens are calculated on-chain over a price oracle. A transaction fails if the price point is outside of the accepted margins set by the company (API not yet exposed). For OST KIT? price points are calculated by and taken from coinmarketcap.com and published to the contract by OST.com.
		# currency_value		| float		| value of the transaction set in "USD" (min USD 0.01, max USD 100) or branded token "BT" (min BT 0.00001, max BT 100). The transfer on-chain always occurs in branded token and for fiat value is calculated to the equivalent amount of branded tokens at the moment of transfer. If the transaction type is between users and a commission percentage is set then the commission is inclusive in this value and the complement goes to the beneficiary user.
		# commission_percent	| float		| inclusive percentage of the value that is paid to the company. Possible only for "user_to_user" transaction kind. (min 0%, max 100%)
		# status				| string	| status of the create transaction-type (default: "active")
		# uts					| number	| unix timestamp in milliseconds

	}

	// ---------------------
	// Edit Transaction Type
	// ---------------------
	# /transaction-types/edit
	function edit_transaction_type($args) {

		# Input Parameter		| Type		| Description
		# ----------------------+-----------+-------------------------------------
		# client_transaction_id	| number	| mandatory id for transaction to edit (returned as id on /create or /list)
		# name					| string	| (optional) change to new name for the transaction-type
		# kind					| string	| (optional) change transaction type which can be one of three kinds: "user_to_user", "company_to_user", or "user_to_company" to clearly determine whether value flows within the application or from or to the company.
		# currency_type			| string	| (optional) change the type of currency the transaction is valued in. Possible values are "USD" (fixed) or "BT" (floating). When a transaction type is set in fiat value the equivalent amount of branded tokens are calculated on-chain over a price oracle.
		# currency_value		| float		| (optional) change the value of the transaction set in "USD" (min USD 0.01 , max USD 100) or branded token "BT" (min BT 0.00001, max BT 100). The transfer on-chain always occurs in branded token and for fiat value is calculated to the equivalent amount of branded tokens at the moment of transfer.
		# commission_percent	| float		| (optional) inclusive percentage of the value that is paid to the company. Possible only for "user_to_user" transaction kind. (min 0%, max 100%)

		// Client Transaction ID
		// ---------------------
		if (!isset($args['client_transaction_id'])) {
			$this->debug_log("Failed! Edit Transaction Type Endpoint requires client_transaction_id");
		} else {$parameters['client_transaction_id'] = $args['client_transaction_id'];}

		// New Transaction Type Name
		// -------------------------
		if (isset($args['name'])) {
			$args['name'] = $this->validate_name($args['name'], 'Edit Transaction Type');
			if ($args['name']) {$parameters['name'] = $args['name'];}
		}

		// Transaction Kind
		// ----------------
		$kinds = array('user_to_user', 'company_to_user', 'user_to_company');
		if (isset($args['kind'])) {
			$this->debug_log("Failed! Create Transaction Type Endpoint requires a kind value. Be nice.");
		} elseif (!in_array($args['kind'], $kinds)) {
			$this->debug_log("Failed! Edit Transaction Type Endpoint incorrect kind value.", $args['kind']);
		} else {$parameters['kind'] = $args['kind'];}

		// Currency Type
		// -------------
		$currencytypes = array('USD', 'BT');
		if (isset($args['currency_type'])) {
			if (!in_array($args['currency_type'], $currencytypes)) {
				$this->debug_log("Failed! Create Transaction Type Endpoint incorrect currency_type value.", $args['currency_type']);
			} else {$parameters['currency_type'] = $args['currency_type'];}
		}

		// Currency Value
		// --------------
		// Limitation Note: for currency_value to change, currency_type must be specified
		if ( (isset($args['currency_value'])) && (isset($parameters['currency_type'])) ) {
			$args['currency_value'] = abs(intval($args['currency_value']));
			if ($parameters['currency_type'] == 'USD') {
				if ($args['currency_value'] < 0.01) {
					$this->debug_log("Failed! Edit Transaction Type for USD currency_value must be greater than 0.01", $args['currency_value']);
				} elseif ($args['currency_value'] > 100) {
					$this->debug_log("Failed! Edit Transaction Type for USD currency_value must be less than 100.", $args['currency_value']);
				} else {$parameters['currency_value'] = $args['currency_value'];}
			} elseif ($parameters['currency_type'] == 'BT') {
				if ($args['currency_value'] < 0.00001) {
					$this->debug_log("Failed! Edit Transaction Type for Branded Token currency_value must be greater than 0.01", $args['currency_value']);
				} elseif ($args['currency_value'] > 100) {
					$this->debug_log("Failed! Edit Transaction Type for Branded Token currency_value must be less than 100.", $args['currency_value']);
				} else {$parameters['currency_value'] = $args['currency_value'];}
			}
		}

		// Commission Percentage
		// ---------------------
		if ($args['kind'] == 'user_to_user') {
			if (isset($args['commission_percentage'])) {
				$args['commission_percentage'] = abs(intval($args['commission_percentage']));
				if ($args['commission_percentage'] < 0) {
					$this->debug_log("Failed! Edit Transaction Type commission_percentage must be greater than 0.", $args['commission_percentage']);
				} elseif ($args['commission_percentage'] > 100) {
					$this->debug_log("Failed! Edit Transaction Type commission_percentage must be less than 100.", $args['commission_percentage']);
				} else {$parameters['commission_percentage'] = $args['commission_percentage'];}
			}
		}

		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send Edit Transaction Type Query to API
		// ---------------------------------------
		$data = $this->send_query('/transaction-types/edit', $parameters);
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# On calling /transaction-types/edit the data.result_type is the string "transactions"
		# and the key data.transactions is an array containing the edited transaction type object
		# with the parameters changed.

		# Transaction-types Object 	| Type		| Description
		# --------------------------+-----------+-------------------------------------------
		# id						| number	| identifier for the edited transaction type (identical to client_transaction_id in the request)
		# client_id					| number	| identifier of the authorised client
		# name						| string	| (optional) change to new name for the transaction-type
		# kind						| string	| (optional) change transaction type which can be one of three kinds: "user_to_user", "company_to_user", or "user_to_company" to clearly determine whether value flows within the application or from or to the company.
		# currency_type				| string	| (optional) change the type of currency the transaction is valued in. Possible values are "USD" (fixed) or "BT" (floating). When a transaction type is set in fiat value the equivalent amount of branded tokens are calculated on-chain over a price oracle.
		# currency_value			| float		| (optional) change the value of the transaction set in "USD" (min USD 0.01, max USD 100) or branded token "BT" (min BT 0.00001, max BT 100). The transfer on-chain always occurs in branded token and for fiat value is calculated to the equivalent amount of branded tokens at the moment of transfer.
		# commission_percent		| float		| (optional) inclusive percentage of the value that is paid to the company. Possible only for "user_to_user" transaction kind. (min 0%, max 100%)
		# uts						| number	| unix timestamp in milliseconds
	}

	// ----------------------
	// List Transaction Types
	// ----------------------
	# /transaction-types/list
	function list_transaction_types() {

		# note: no input parameters required

		// Send List Transaction Types Query to API
		// ----------------------------------------
		$data = $this->send_query('/transaction-types/list', array(), 'get');
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# For api calls to /transaction-types the data.result_type is the string "transactions_types"
		# and the key data.transactions_types is an array of all transaction_types objects.
		# In addition client_id, price_points, and client_tokens are returned.

		# Result Parameters		| Type		| Description
		# ----------------------+-----------+---------------------------------------------
		# client_id				| number	| identifier of the authorised client
		# result_type			| string	| type identifier "transaction_types"
		# transaction_types		| array		| array of all transaction types
		# meta					| object	| response is not paginated
		# price_points			| object	| contains the OST price point in USD
		# client_tokens			| object	| token information

		# Transaction-types Object Attributes
		# id					| number	| identifier of the client
		# client_transaction_id	| number	| identifier for the transaction type (equals id)
		# name					| string	| name of the transaction type
		# kind					| string	| transaction types can be one of three kinds: "user_to_user", "company_to_user", or "user_to_company" to clearly determine whether value flows within the application or from or to the company.
		# currency_type			| string	| type of currency the transaction is valued in. Possible values are "USD" (fixed) or "BT" (floating).
		# currency_value		| float		| value of the transaction set in "USD" (min USD 0.01, max USD 100) or branded token "BT" (min BT 0.00001, max BT 100).
		# commission_percent	| float		| inclusive percentage of the value that is paid to the company (min 0%, max 100%)
		# status				| string	| the status of the transaction type (default "active")

		# Client Tokens Object Attributes
		# client_id					| number	| identifier of the client
		# name						| string	| name of the token
		# symbol					| string	| name of the symbol
		# symbol_icon				| string	| icon reference
		# conversion_factor			| float		| conversion factor of the branded token to OST
		# _token_erc20_address		| address	| prefixed hexstring address of the branded token erc20 contract on the utility chain
		# airdrop_contract_addr		| address	| prefixed hexstring address of the airdrop / pricer contract that regulates payments of branded tokens with transaction types
		# simple_stake_contract_addr| address	| prefixed hexstring address of the simple stake contract which holds the OST? on Ethereum Ropsten testnet which has been staked to mint branded tokens

	}

	// -------------------
	// Execute Transaction
	// -------------------
	# /transaction-types/execute
	function execute_transaction($args) {

		# Input Parameter	| Type		| Description
		# ------------------+-----------+--------------------------------------------
		# from_uuid			| string	| user or company from whom to send the funds
		# to_uuid			| string	| user or company to whom to send the funds
		# transaction_kind	| string	| name of the transaction type to be executed, e.g. "Upvote"
		#					| 			| (note that the parameter is a misnomer currently)

		// From UUID
		// ---------
		if (!isset($args['from_uuid'])) {
			$this->debug("Failed! Execute Transaction Endpoint requires a from_uuid parameter.");
		} else {$parameters['from_uuid'] = $args['from_uuid'];}

		// To UUID
		// -------
		if (!isset($args['to_uuid'])) {
			$this->debug("Failed! Execute Transaction Endpoint requires a to_uuid parameter.");
		} else {$parameters['to_uuid'] = $args['to_uuid'];}

		// Transaction Type
		// ----------------
		// note: 'transaction_kind' is a misnomer, it's actually the transaction type name
		if (!isset($args['transaction_kind'])) {
			// allow for use of 'transaction_type' or 'name' arguments instead
			if (isset($args['transaction_type'])) {$parameters['transaction_kind'] = $args['transaction_type'];}
			elseif (isset($args['name'])) {$parameters['transaction_kind'] = $args['name'];}
			else {$this->debug("Failed! Execute Transaction Endpoint requires a transaction_kind parameter.");}
		} else {
			// ? could perhaps verify that the transaction type exists ?
			// however we can just allow the API itself to return this error
			$parameters['transaction_kind'] = $args['transaction_kind'];
		}

		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send Transaction Execute Query to API
		// -------------------------------------
		$data = $this->send_query('/transaction-types/execute', $parameters);
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# For api calls to /transaction-types/execute the data is an object containing the attributes described below. A success response acknowledges that the request is successfully queued and a transaction uuid is returned.

		# Data Parameters		| Type		| Description
		# ----------------------+-----------+---------------------------------------------
		# from_uuid				| string	| user or company from whom to send the funds
		# to_uuid				| string	| user or company to whom to send the funds
		# transaction_uuid		| string	| uuid of the transaction type that has been executed
		# transaction_hash		| hexstring	| initially returned as null before the transaction is formed
		# transaction_kind		| string	| name of the transaction type to be executed, e.g. "Upvote"
		#									| (note that the parameter is a misnomer currently)

		# Note!!! On a successful acknowledgement the transaction_uuid must be queried on /transaction-types/status
		# for completion of the transaction.

	}

	// ------------------
	// Transaction Status
	// ------------------
	# /transaction-types/status
	function transaction_status($transaction_uuids) {

		# Input Parameter		| Type		| Description
		# ----------------------+-----------+-----------------------------------------------------------------------
		# transaction_uuids		| string	| unique identifier for an executed transaction that is part of an array

		if (!isset($args['transaction_uuids'])) {
			$this->debug("Failed! Transaction Status Endpoint requires a transaction_uuids parameter.");
		} elseif (!is_array($args['transaction_uuids'])) {
			$this->debug("Failed! Transaction Status Endpoint requires transaction_uuids to be an array.", $args['transaction_uuids']);
		}

		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send Transaction Status Query to API
		// ------------------------------------
		$data = $this->send_query('/transaction-types/status', array('transaction_uuids' => $args['transactionuuids']), 'get');
		if ($this->result_format == 'array') {$data = json_decode($data, true);}

		// TODO: maybe standardize return data?
		// $tdata['status'] = $data['data']['transactions'][0]['status'];
		// $tdata['transaction_hash'] = $data['data']['transactions'][0]['transaction_hash'];
		// $tdata['bt_transfer_value'] = $data['data']['transactions'][0]['bt_transfer_value'];
		// $tdata['transaction_timestamp' = $data['data']['transactions'][0]['transaction_timestamp'];
		// $tdata['view_url'] = "https://view.ost.com/chain-id/".$this->network_id;
        // $tdata['view_url'] = "/transaction/".$data['data']['transactions'][0]['transaction_hash'];

		return $data;

		# For API calls to /transaction-types/status the result_type is a string "transactions",
		# that is an array containing objects each with the attributes described below,
		# which are the details of the executed transaction.

		# Response Transaction Object| Type			| Description
		# ---------------------------+--------------+---------------------------------------------
		# from_user_id				 | string		| origin user of the branded token transaction
		# to_user_id				 | string		| destination user of the branded token transaction
		# transaction_uuid			 | string		| id of the executed transaction type
		# client_token_id			 | number		| id of the branded token
		# transaction_hash			 | hexstring	| the generated transaction hash
		# status					 | string		| the execution status of the transaction type: "processing", "failed" or "complete"
		# gas_price					 | string		| value of the gas utilized for the transaction
		# transaction_timestamp		 | string		| universal time stamp value of execution of the transaction in milliseconds
		# uts						 | number		| universal time stamp value in milliseconds
		# gas_used					 | string		| (optional) hexadecimal value of the gas used to execute the tranaction
		# transaction_fee			 | string		| (optional) the value of the gas used at the gas price
		# block_number				 | number		| (optional) the block on the chain in which the transaction was included
		# bt_transfer_value			 | string		| (optional) the amount of branded tokens transferred to the destination user
		# bt_commission_amount		 | string		| (optional) the amount of branded tokens transferred to the company

	}


	# ------------------- #
	# API QUERY FUNCTIONS #
	# ------------------- #

	// -----------------------
	// Send API Query Abstract
	// -----------------------
	function send_query($endpoint, $parameters, $method='post') {
		$query = $this->build_query($endpoint, $parameters, $method);
		$data = $this->remote_query($endpoint, $query, $parameters, $method);
		return $data;
	}

	// ---------------
	// Build API Query
	// ---------------
	// Credit: TechupBusiness via https://help.ost.com/support/discussions/topics/35000005112

	/**
	 * Builds and signs the query and add all needed parameters (api_key, request_timestamp, signature)
	 *
	 * @param string $endpoint
	 * @param array $parameters
	 *
	 * @return string|string[]|null
	 */
	function build_query($endpoint, $parameters, $method) {

		$parameters['api_key'] = $this->api_key;
		$parameters['request_timestamp'] = time();
		ksort($parameters);

		foreach ($parameters as $key => $value) {
			$key = strtolower($key);
			if (is_array($value)) {
				// note: currently only needed for /transaction-types/status/ transaction_uuids parameter
				foreach ($value as $val) {$query_params[] = $key.'[]='.urlencode($val);}
			} else {$query_params[] = $key.'='.urlencode($value);}
		}

		// debug query parameters build method
		$this->debug_log("Query Params 1", $query_params);

		$query = $endpoint.'?'.implode('&', $query_params);
		$signature = $query_params['signature'] = hash_hmac('sha256', $query, $this->api_secret);

		if ($method == 'get') {return $query.'&signature='.$signature;}
		elseif ($method == 'post') {return $query_params;}

	}

	// ----------------
	// Remote API Query
	// ----------------
	function remote_query($endpoint, $query, $parameters, $method) {

		if ($method == 'post') {$url = $this->api_url.$endpoint;}
		elseif ($method == 'get') {$url = $this->api_url.$query;}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connecttime);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		if ($method == 'post') {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
		}
		if (substr($url, 0, strlen('https://')) == 'https://') {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		}
		if ($this->api_port) {curl_setopt($ch, CURLOPT_PORT, $this->api_port);}

		$contents = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$header = curl_getinfo($ch);
		$errorno = curl_errno($ch);
		$error = curl_error($ch);
		curl_close($ch); unset($ch);

		// alternative method (non-Curl)
		// if ($method == 'post') {
		// 	$encoded = http_build_query($query);
		// 	$context = stream_context_create(array(
        // 	   'http' => array(
        // 	       'method' => 'POST',
        // 	       'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        // 	       'content' => $encoded
        // 	   )
        // 	));
        // 	$response = file_get_contents($url, false, $context);
        // } elseif ($method == 'get') {
        //	$response = file_get_contents($url);
        // }

		$response = array(
			'body' => $contents,
			'httpcode' => $httpcode,
			'header' => $header,
			'errno' => $errorno,
			'error' => $error
		);

		# Result Parameters | Type		| Description
		# ------------------+-----------+----------------
		# success			| bool		| post successful
		# data				| object	| (optional) data object describing result if successful
		# err				| object	| (optional) describing error if not successful

		// check API Response for Errors
		// -----------------------------
		if ($response['errorno'] != 0) {
			$this->debug_log("API Connection Error ".$response['errorno'], $response['error']);
			$error = true;
		} elseif ($response['httpcode'] != 200) {
			if ($response['httpcode'] == 401) {
				$this->debug_log("Failed! Unauthorized API Request", json_decode($response['body'], true));
			} else {$this->debug_log("Failed! HTTP Response Code ".$response['httpcode'], json_decode($response['body'], true));}
			$error = true;
		} elseif (is_empty($response['body'])) {
			$this->debug_log("Empty API Response Body", $response['header']); $error = true;
		} else {
			$jsondata = json_decode($response['body'], true);
			if (!isset($data['success'])) {$this->debug_log("API JSON Response Corrupt", $data); $error = true;}
			if (!$data['success']) {$this->debug_log("Request Failed with Error", $data['err']);}
		}

		if (isset($error) && $error) {$this->debug_log("Query Parameters", $parameters);}

		return $data;
	}

 }
}


// ----------------------------------------
// === Shortcut Functions for API Class ===
// ----------------------------------------

global $ost_kit_args;

// -----------
// Create User
// -----------
# /users/create
if (!function_exists('ost_kit_create_user')) {
 function ost_kit_create_user($name) {
 	global $ost_kit_args;
 	$args = $ost_kit_args;
	$args['endpoint'] = 'create_user';
	$args['name'] = $name;
	return new OST_Query($args);
 }
}

// ---------
// Edit User
// ---------
# /users/edit
if (!function_exists('ost_kit_edit_user')) {
 function ost_kit_edit_user($uuid, $name) {
 	global $ost_kit_args;
 	$args = $ost_kit_args;
	$args['endpoint'] = 'edit_user';
	$args['uuid'] = $uuid;
	$args['name'] = $name;
	return new OST_Query($args);
 }
}

// ----------
// List Users
// ----------
# /users/list
if (!function_exists('ost_kit_list_users')) {
 function ost_kit_list_users($page_no, $filter=false, $order_by=false, $order=false) {
 	global $ost_kit_args;
 	$args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'list_users';
	$args['page_no'] = $page_no;
	// optional arguments
	if ($filter) {$args['filter'] = $filter;}
	if ($order_by) {$args['orderby'] = $order_by;}
	if ($order) {$args['order'] = $order;}
	return new OST_Query($args);
 }
}

// --------------
// List All Users
// --------------
// note: not an endpoint, loop pages of /users/list
if (!function_exists('ost_kit_list_all_users')) {
 function ost_kit_list_all_users($filter=false, $order_by=false, $order=false) {
 	global $ost_kit_args;
 	$args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'list_all_users';
	// optional arguments
	if ($filter) {$args['filter'] = $filter;}
	if ($order_by) {$args['orderby'] = $order_by;}
	if ($order) {$args['order'] = $order;}
	return new OST_Query($args);
 }
}

// -------------
// Token Balance
// -------------
// note: not an endpoint, uses /users/edit
if (!function_exists('ost_kit_token_balance')) {
 function ost_kit_token_balance($uuid) {
 	global $ost_kit_args;
 	$args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'token_balance';
	$args['uuid'] = $uuid;
	return new OST_Query($args);
 }
}

// ------------
// Airdrop Drop
// ------------
# /users/airdrop/drop
if (!function_exists('ost_kit_airdrop_drop')) {
 function ost_kit_airdrop_drop($amount, $list_type=false) {
 	global $ost_kit_args;
 	$args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'airdrop_drop';
	$args['amount'] = $amount;
	// optional arguments
	if ($list_type) {$args['list_type'] = $list_type;}
	return new OST_Query($args);
 }
}

// --------------
// Airdrop Status
// --------------
# /users/airdrop/status
if (!function_exists('ost_kit_airdrop_status')) {
 function ost_kit_airdrop_status($airdrop_uuid) {
 	global $ost_kit_args;
 	$args = $ost_kit_args;
	$args['endpoint'] = 'airdrop_status';
	$args['airdrop_uuid'] = $airdrop_uuid;
	return new OST_Query($args);
 }
}

// -----------------------
// Create Transaction Type
// -----------------------
# /transaction-types/create
if (!function_exists('ost_kit_create_transaction_type')) {
 function ost_kit_create_transaction_type($name, $kind, $currency_type, $currency_value, $commission_percent) {
 	global $ost_kit_args;
 	$args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'create_transaction_type';
	$args['name'] = $name;
	$args['kind'] = $kind;
	$args['currency_type'] = $currency_type;
	$args['currency_value'] = $currency_value;
	$args['commission_percent'] = $commission_percent;
	return new OST_Query($args);
 }
}

// ---------------------
// Edit Transaction Type
// ---------------------
# /transaction-types/edit
if (!function_exists('ost_kit_edit_transaction_type')) {
 function ost_kit_edit_transaction_type($client_transaction_id, $name=false, $kind=false, $currency_type=false, $currency_value=false, $commission_percent=false) {
 	global $ost_kit_args;
 	$args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'edit_transaction_type';
	$args['client_transaction_id'] = $client_transaction_id;
	// optional arguments
	if ($name) {$args['name'] = $name;}
	if ($kind) {$args['kind'] = $kind;}
	if ($currency_type) {$args['currency_time'] = $currency_type;}
	if ($currency_value) {$args['currency_value'] = $currency_value;}
	if ($commission_percent) {$args['commission_percent'] = $commission_percent;}
	if (!defined('DOING_AJAX') || !DOINGAJAX) {$args['format'] = 'array';}
	$data = new OST_Query($args);
	return $data;
 }
}

// ----------------------
// List Transaction Types
// ----------------------
# /transaction-types/list
if (!function_exists('ost_kit_list_transaction_types')) {
 function ost_kit_list_transaction_types() {
 	global $ost_kit_args;
 	$args = $ost_kit_args;
	$args['endpoint'] = 'list_transaction_types';
	return new OST_Query($args);
 }
}

// -------------------
// Execute Transaction
// -------------------
# /transaction-types/execute
if (!function_exists('ost_kit_execute_transaction')) {
 function ost_kit_execute_transaction($from_uuid, $to_uuid, $type) {
  	global $ost_kit_args;
  	$args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'execute_transaction';
	$args['from_uuid'] = $from_uuid;
	$args['to_uuid'] = $to_uuid;
	// type of transaction (key misnomer fix)
	$args['transaction_kind'] = $type;
	$data = new OST_Query($args);
 }
}

// ------------------
// Transaction Status
// ------------------
# /transaction-types/status
// note: unlike all other API parameters, $transaction_uuids is an array
if (!function_exists('ost_kit_transaction_status')) {
 function ost_kit_transaction_status($transaction_uuids) {
 	global $ost_kit_args;
 	$args = $ost_kit_args;
	$args['endpoint'] = 'transaction_status';
	$args['transaction_uuids'] = $transaction_uuids;
	return new OST_Query($args);
 }
}

// ----------------------------
// Execute Complete Transaction
// ----------------------------
// calls /transaction-types/execute/ and then /transaction-types/status to complete
// OST Kit Alpha Note: "On a successful acknowledgement the transaction_uuid must be queried on
// /transaction-types/status for completion of the transaction."

if (!function_exists('ost_kit_execute_complete_transaction')) {
 function ost_kit_execute_complete_transaction($from_uuid, $to_uuid, $type) {
	$data = ost_kit_execute_transaction($from_uuid, $to_uuid, $type);
	if ($data['success']) {
		// TODO: find out how long to sleep here?
		sleep(3);
		$transaction_uuids[] = $data['transaction_uuid'];
		$statusdata = ost_kit_transaction_status($transaction_uuids);
		if ($statusdata['success']) {$data = array_merge($data, $statusdata);}
	}
	return $data;
 }
}


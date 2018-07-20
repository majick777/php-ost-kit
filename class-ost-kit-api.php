<?php

// =========================
// === OST KIT alpha API ===
// === PHP Wrapper Class ===
// =========================
// ===== Version 110.1 =====
// == rev 1 for API 1.1.0 ==
// =========================
//
// OST KITa API PHP Wrapper Class
// Copyright (C) 2018 Tony Hayes
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <https://www.gnu.org/licenses/>.
//
//
// Usage Note: define constants before instantiating and/or arguments while instantiating.
// ---------------------------------------------------------------------------------------
// Constant	=============== Class Arg ===== Req/Opt ===	Format ========	Default Value ====
// ---------------------------------------------------------------------------------------
// ------------------------ endpoint		REQUIRED	string			n/a
// ------------------------ format			Optional	'json'/'array'	'array'
// ------------------------ per_page		Optional	numeric			10 (used when getting 'all' lists)
// === API Details ===
// OST_KIT_KEY				api_key			REQUIRED	string			n/a
// OST_KIT_SECRET			api_secret		REQUIRED	string			n/a
// OST_KIT_URL				api_url			Optional	URL				'https://sandboxapi.ost.com/v1'
// OST_KIT_CHAINID			chain_id		Optional 	number			1409 (test network blockchain)
// === Connection ===
// OST_KIT_PORT				port			Optional	number			false
// OST_KIT_CONNECTTIME		connecttime		Optional	number			30
// OST_KIT_TIMEOUT			timeout			Optional	number			15
// === Debugging ===
// OST_KIT_DEBUG_LOG 		debug_log		Optional	boolean			true
// OST_KIT_DEBUG_PATH 		debug_path		Optional	string			/class-file-path/api-debug.log
// OST_KIT_DEBUG_DISPLAY	debug_display 	Optional	boolean			false
// ---------------------------------------------------------------------------------------

# Important OST Kit Alpha Note for Transaction Execution
# Ref: https://dev.ost.com/docs/api_action_execute.html
# "We have disabled pessimistic concurrency control to ensure that no false positives are returned.
# As a result you must query /transactions/{id} for successful completion of the transaction."

# === Development TODOs ===
# - More HTTP Response Error Code Handling
# ? transaction_execute: check action kind if company UUID is not explicitly specified

# === API Test List ===
# * test all optional filters for list queries (user, airdrop, action, transaction, transfer)
# - airdrop_drop: whether API can handle both ',' and ', ' delimiting of user IDs
# - action_create: check if arbitrary_commission is a required argument for user_to_user actions
# - action_create: check decimal value accuracy level for commission_percent
# - transaction_execute: how long to sleep between transaction execute and status

# === Mini Changelog ===
# 110.1: added ledger and balance endpoints for API v1.1.0
# 100.3: added transfer endpoints and optional filters
# 100.2: cleaned public version for API v1.0.0
# 100.1: working development version for API v1.0.0
# 092.1: initial dev/public version for API v0.9.2


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
	protected $api_url = 'https://playgroundapi.ost.com/v1';

	// Network Chain ID
	// ----------------
	protected $chain_id = '1409';

	// Per Page Listing
	// ----------------
	// users per page when getting 'all' lists
	public $per_page = 100;

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

	// Debug Display
	// -------------
	public $debug_display = false;

	// Errors Switch
	// -------------
	public $error = false;

	// RESPONSE
	// --------
	public $response = false;


	// === Class Constructor ===
	// -------------------------
	function __construct($args) {

		// -----------------
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
		} elseif (defined('OST_KIT_DEBUG_PATH') && OST_KIT_DEBUG_PATH) {
			if (is_dir(dirname(OST_KIT_DEBUG_PATH))) {$this->debug_path = OST_KIT_DEBUG_PATH;}
		} else {$this->debug_path = dirname(__FILE__).'/api-debug.log';}

		// maybe set Debug Display
		// -----------------------
		if (isset($args['debug_display'])) {
			if ($args['debug_display']) {$this->debug_display = true;}
			else {$this->debug_display = false;}
		} elseif (defined('OST_KIT_DEBUG_DISPLAY')) {
			if (OST_KIT_DEBUG_DISPLAY) {$this->debug_display = true;}
			else {$this->debug_display = false;}
		}

		// maybe log/display class construct arguments
		// -------------------------------------------
		$this->debug_log("Passed Arguments: ", $args);

		// ---------------------------------
		// === Process Argument Settings ===
		// ---------------------------------

		// check for required endpoint argument
		// ------------------------------------
		if (isset($args['endpoint'])) {$endpoint = $args['endpoint'];}
		else {$this->debug_log("API Endpoint Missing. Aborting."); return false;}

		// maybe set Returned Result Format
		// --------------------------------
		// (optional, default 'array')
		if (isset($args['format'])) {
			$args['format'] = strtolower($args['format']);
			if ($args['format'] == 'json') {$this->result_format = 'json';}
			elseif ($args['format'] != 'array') {
				$this->debug_log("Failed! Invalid API Result Format requested.", $args['format']);
				throw new \Exception("Failed! Invalid API Result Format requested."); return;
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
			throw new \Exception("Error! OST Kit API Class onstantiated without an API Key!"); return;
		}

		// set API Secret (required)
		// -------------------------
		if (isset($args['api_secret'])) {$this->api_secret = $args['api_secret'];}
		elseif (defined('OST_KIT_SECRET')) {$this->api_secret = OST_KIT_SECRET;}
		else {
			$this->debug_log("Error! Class Instantiated without an API Secret!", $args);
			throw new \Exception("Error! OST Kit API Class instantiated without an API Secret!"); return;
		}

		// maybe set API URL
		// -----------------
		// (optional, default 'https://playgroundapi.ost.com'
		if (isset($args['api_url'])) {$this->api_url = $args['api_url'];}
		elseif (defined('OST_KIT_URL')) {$this->api_url = OST_KIT_URL;}


		// maybe set Network ID
		// --------------------
		// (optional, default 1409)
		if (isset($args['chain_id'])) {$this->chain_id = $args['chain_id'];}
		elseif (defined('OST_KIT_CHAINID')) {$this->api_url = OST_KIT_CHAINID;}


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
		elseif (defined('OST_KIT_TIMEOUT')) {$this->timeout = OST_KIT_TIMEOUT;}



		# ----------------------#
		# MAP OST KIT ENDPOINTS #
		# ----------------------#
		# = TOKEN =
		# token_get
		# = USERS =
		# user_create
		# user_get
		# user_edit
		# user_list
		# users_list
		# user_balance
		# = AIRDROPS =
		# airdrop_drop
		# airdrop_get
		# airdrop_list
		# airdrops_list
		# = ACTIONS =
		# action_create
		# action_get
		# action_edit
		# action_list
		# actions_list
		# = TRANSACTIONS =
		# transaction_execute
		# transaction_get
		# transaction_list
		# transactions_list
		# user_ledger
		# = TRANSFERS =
		# transfer_create
		# transfer_get
		# transfer_list

		// Switch Endpoint to Send Query
		// -----------------------------
		// (with friendly name support)
		switch ($endpoint) {

			// TOKEN
			// -----
			case '/token':
			case 'token':
			case 'get_token':
			case 'token_get':
				$this->response = $this->token_get(); return;

			// USERS
			// -----
			case '/users':
			case '/users/create':
			case 'create_user':
			case 'user_create':
				# required argument: name
				$this->response = $this->user_create($args); return;
			case '/users/edit':
			case 'edit_user':
			case 'user_edit':
				# required arguments: id/uuid, name
				$this->response = $this->user_edit($args); return;
			case '/users/get':
			case 'get_user':
			case 'user_get':
				# required argument: id/uuid
				$this->response = $this->user_get($args); return;
			case '/users/list':
			case 'list_users':
			case 'user_list':
				# required arguments: page_no
				# optional arguments: airdropped (0/1), order_by(creation_time/name), order(asc/desc), limit
				$this->response = $this->user_list($args); return;
			case '/users/listall':
			case 'list_all_users':
			case 'users_list':
				$this->response = $this->users_list($args); return;
			case '/users/balance':
			case 'token_balance':
			case 'user_balance':
				# required argument: id
				$this->response = $this->user_balance($args); return;

			// AIRDROPS
			// --------
			case '/users/airdrop/drop':
			case 'airdrop_drop':
				# required arguments: amount
				# optional arguments: airdropped (0/1), user_ids
				$this->response = $this->airdrop_drop($args); return;
			case '/users/airdrop/status':
			case 'airdrop_status':
			case 'get_airdrop':
			case 'airdrop_get':
				# required arguments: airdrop_uuid
				$this->response = $this->airdrop_get($args); return;
			case '/users/airdrop/list':
			case 'airdrop_list':
				# required arguments: page_no
				# optional arguments: order_by(creation_time/name), order(asc/desc), limit
				$this->response = $this->airdrop_list($args); return;
			case '/users/airdrop/listall':
			case 'list_all_airdrops':
			case 'airdrops_list':
				$this->response = $this->airdrops_list($args); return;

			// ACTION TYPES
			// ------------
			case '/transaction-types/create':
			case 'create_transaction_type':
			case 'create_action_type':
			case 'create_action':
			case 'action_create':
				# required arguments: name, kind, currency, arbitary_amount, arbitrary_commission
				# special optional arguments:
				# - amount (with arbitrary_amount false)
				# - commission_percent (only for user_to_user kind, arbitrary_commission false)
				$this->response = $this->create_action($args); return;
			case '/transaction-types/get':
			case 'get_transaction_type':
			case 'get_action_type':
			case 'get_action':
			case 'action_get':
				# required arguments: id
				$this->response = $this->action_get($args); return;
			case '/transaction-types/edit':
			case 'edit_transaction_type':
			case 'edit_action_type':
			case 'edit_action':
			case 'action_edit':
				# required arguments: id
				# optional arguments: name, kind, currency, arbitary_amount, arbitrary_commission
				# special optional arguments:
				# - amount (with arbitrary_amount false)
				# - commission_percent (only for user_to_user kind, arbitrary_commission false)
				$this->response = $this->action_edit($args); return;
			case '/transaction-types/list':
			case 'list_transaction_types':
			case 'list_action_types':
			case 'list_actions':
			case 'action_list':
				# required arguments: page_no
				# optional arguments: order_by(creation_time/name), order(asc/desc), limit
				$this->response = $this->action_list($args); return;
			case '/transaction-types/listall':
			case 'list_all_action_types':
			case 'list_all_actions':
			case 'actions_list':
				$this->response = $this->actions_list($args); return;


			// TRANSACTIONS
			// ------------
			case '/transaction-types/execute':
			case 'transaction_execute':
			case 'execute_transaction':
				# required arguments: from_uuid, to_uuid, action_id
				# optional arguments: amount (if arbitrary_amount), commission_percent (if arbitrary_commission)
				$this->response = $this->transaction_execute($args); return;
			case '/transaction-types/status':
			case 'transaction_status':
			case 'status_transaction':
			case 'get_transaction':
			case 'transaction_get':
				# required arguments: id
				$this->response = $this->transaction_get($args); return;
			case '/transaction-types/list':
			case 'list_transactions':
			case 'transaction_list':
				# required arguments: page_no
				# optional arguments: order(asc/desc), limit
				$this->response = $this->transaction_list($args); return;
			case 'list_all_transactions':
			case 'transactions_list':
				$this->response = $this->transactions_list($args); return;
			case 'ledger':
			case 'user_transactions':
			case 'user_ledger':
				# required arguments: id, page_no
				# optional arguments: order_by(creation_time), order(asc/desc), limit
				$this->response = $this->user_ledger($args); return;

			// TRANSFERS
			// ---------
			case '/transfers/create':
			case 'create_transfer':
			case 'transfer_create':
				# required arguments: to_address, amount
				$this->response = $this->transfer_create($args); return;
			case '/transfers/get':
			case 'get_transfer':
			case 'transfer_get':
				# required argument: id
				$this->response = $this->transfer_get($args); return;
			case '/transfers/list':
			case 'transfer_list':
				# required arguments: page_no
				# optional arguments: order(asc/desc), limit
				$this->response = $this->transfer_list($args); return;
			case 'list_all_transfers':
			case 'transfers_list':
				$this->response = $this->transfers_list($args); return;

		}

		// oops, no endpoint match was found
		$this->debug_log("Error! No API Endpoint Match Found.", $args['endpoint']);
		throw new \Exception("Error! No API Endpoint Match Found."); return;

	}

	// ----------------------------------
	// Validate Username/Transaction Name
	// ----------------------------------
	function validate_name($name, $action) {

		$name = (string)$name;

		// actually do not trim as leading or trailing space is valid
		// $name = trim($name);

		// make sure name is not empty
		if (empty($name)) {$this->debug_log("Failed! ".$action." name argument is empty."); return false;}

		// alphanumeric string match (spaces allowed)
		if (!preg_match('/^[0-9a-zA-Z ]+$/i', $name)) {
			$this->debug_log("Failed! ".$action." name must be letters numbers and spaces only.", $name); return false;
		}

		// minimum 3 characters!
		if (strlen($name) < 4) {
			$this->debug_log("Failed! ".$action." name required a minimum of 3 characters.", $name); return false;
		}

		// 20 character username limit
		// (it is also 20 character limit for transaction type names)
		if (strlen($name) > 20) {
			$this->debug_log("Failed! ".$action." name exceeds 20 character limit.", $name); return false;
		}

		return $name;
	}

	// --------------------
	// Validate Hex Address
	// --------------------
	// 100.3: added for create transfer endpoint
	function validate_address($address) {

		// check the address length is 42 (0x and 40 characters)
		if (strlen($address) != 42) {return false;}

		// require an 0x prefix
		if (substr($address, 0, 2) != '0x') {return false;}

		// now strip the 0x prefix
		$hexaddress = substr($address, 2, strlen($address));

		// check the remainder is hexadecimal
		if (!ctype_xdigit($hexaddress)) {return false;}

		// all good here
		return true;
	}

	// -------------
	// Debug Logging
	// -------------
	function debug_log($message, $data=false) {

		// set the error switch if we had fail/error
		if (is_string($message)) {
			if ( (strstr($message, 'Failed')) || (strstr($message, 'Error')) ) {$this->error = true;}
		}

		// maybe output if debug display is on
		if ($this->debug_display) {echo $message.PHP_EOL.print_r($data,true).PHP_EOL;}

		// maybe log message to the debug log
		if (!$this->debug_log) {return;}

		// convert data to string for logging
		if ($data) {
			if (is_string($data)) {$message .= ' '.$data.PHP_EOL;}
			elseif (is_array($data) || is_object($data)) {
				$message .= PHP_EOL.print_r($data, true).PHP_EOL;
			}
		}

		// maybe
		if (!$this->debug_path) {
			echo "Debugging is set to on but no valid debug path provided.";
		} else {
			error_log($message, 3, $this->debug_path);
		}

	}

	# ------------------
	# API TOKEN ENDPOINT
	# ------------------
	# /token (GET)
	function token_get() {

		$data = $this->send_query('/token', array(), 'get');
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# Parameter 			| Type				| Description
		# ----------------------+-------------------+-----------------------------------------
		# company_uuid			| string			| unique identifier of the company
		# name					| string			| name of the token
		# symbol				| string			| name of the symbol
		# symbol_icon			| string			| icon reference
		# conversion_factor		| string<float>		| conversion factor of the branded token to OST
		# token_erc20_address	| address			| prefixed hexstring address of the branded token erc20 contract on the utility chain
		# simple_stake_contract_address	 | address	| prefixed hexstring address of the simple stake contract which holds the OST? on Ethereum Ropsten testnet which has been staked to mint branded tokens
		# total_supply			| string<number>	| Total supply of Branded Tokens
		# ost_utility_balance	| array				| OSTa on utility chains with chain IDs and amounts as an array of tuples (3, amount)
		# price_points			| object			| Contains the OST price point in USD and the Branded Tokens price point in USD

	}


	# --------------
	# USER ENDPOINTS
	# --------------

	// -----------
	// Create User
	// -----------
	# /users/ (POST)
	function user_create($args) {

		// Username
		// --------
		$args['name'] = $this->validate_name($args['name'], 'Create Username');

		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send Create User Query to API
		// -----------------------------
		$data = $this->send_query('/users/', array('name' => $args['name']));
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# User Object		| Type					| Description
		# ------------------+-----------------------+-----------------------------------------
		# id				| string				| user id (uuid copy, deprecated)
		# addresses	array	| [(chain id, address)]	| e.g. [(1409, 0x21bFfb1c7910e9D0393E3f655E921FB47F70ab56)]
		# name				| string				| name of the user (not unique)
		# airdropped_tokens	| string<number>		| total amount of airdropped tokens to the user
		# token_balance		| string<number>		| current balance of the user

	}

	// --------
	// Get User
	// --------
	# /users/{id} (GET)
	function user_get($args) {

		// UUID (required)
		// ---------------
		if (isset($args['uuid'])) {$args['id'] = $args['uuid'];}
		elseif (!isset($args['id'])) {
			$this->debug_log("Failed! Get User Endpoint requires a UUID.", $args);
		}

		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send Get User Query to API
		// --------------------------
		$data = $this->send_query('/users/'.$args['id'], array(), 'get');
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# (see User Object attributes in Create User method)

	}

	// ---------
	// Edit User
	// ---------
	# /users/{id} (POST)
	function user_edit($args) {

		// UUID (required)
		// ---------------
		if (isset($args['uuid'])) {$args['id'] = $args['uuid'];}
		elseif (!isset($args['id'])) {
			$this->debug_log("Failed! Edit User Endpoint requires a UUID.", $args); return false;
		}

		// New Username
		// ------------
		$args['name'] = $this->validate_name($args['name'], 'Edit Username');

		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send Edit User Query to API
		// ---------------------------
		$data = $this->send_query('/users/'.$args['id'], array('name' => $args['name']));
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# (see User Object attributes in Create User method)

	}

	// ---------
	// Get Users
	// ---------
	// TODO: get multiple users using user_list filter_options parameter ?

	// ----------
	// List Users
	// ----------
	# /users/ (GET)
	function user_list($args) {

		# Input Parameter	| Type		| Description
		# ------------------+-----------+----------------------------
		# page_no			| number	| page number (starts from 1)
		# airdropped		| boolean	| true == users who have been airdropped tokens, false == users who have not been airdropped tokens
		# order_by			| string	| (optional) order the list by 'creation_time' or 'name' (default)
		# order				| string	| (optional) order users in 'desc' (default) or 'asc' order.
		# limit				| number	| limits the number of user objects to be sent in one request(min. 1, max. 100, default 10)

		// Page Number
		// -----------
		if (!isset($args['page_no'])) {$this->debug_log("Failed! User Listing Endpoint requires page number.");}
		$page_no = abs(intval($args['page_no']));
		if ($page_no < 1) {$this->debug_log("Failed! User Listing page number must be 1 or over.");}
		else {$parameters['page_no'] = $args['page_no'];}

		// Airdropped Filter
		// -----------------
		if (isset($args['airdropped'])) {
			if ($args['airdropped'] === true) {$parameters['airdropped'] = '1';}
			else {$parameters['airdropped'] = '0';}
		} elseif (isset($args['filter'])) {
			// make backwards compatible with string arguments passed
			if ($args['filter'] == 'never_airdropped') {$parameters['airdropped'] = '0';}
			elseif ($args['filter'] == 'airdropped') {$parameters['airdropped'] = '1';}
			elseif ($args['filter'] != 'all') {
				$this->debug_log("Warning! User List Endpoint incorrect 'filter' parameter value.", $args['filter']);
			}
		}

		// Order By
		// --------
		if (isset($args['order_by'])) {
			// bugfix for creation_time (invalid doc spec)
			if ($args['order_by'] == 'creation_time') {$args['order_by'] = 'created';}
			if ( ($args['order_by'] != 'creation_time') && ($args['order_by'] != 'time') ) {
				$this->debug_log("Warning! User List Endpoint incorrect 'order_by' parameter value.", $args['order_by']);
			} else {

				$parameters['order_by'] = $args['order_by'];
			}
		}

		// Order
		// -----
		if (isset($args['order'])) {
			if ( ($args['order'] != 'asc') && ($args['order'] != 'desc') ) {
				$this->debug_log("Warning! User List Endpoint incorrect 'order' Parameter value.", $args['order']);
			} else {$parameters['order'] = $args['order'];}
		}

		// Limit
		// -----
		if (isset($args['limit'])) {
			$limit = (int)$args['limit'];
			if ($limit > 0) {
				if ($limit > 100) {$limit = 100;}
				$parameters['limit'] = $limit;
			}
		}

		// Optional Filters
		// ----------------

		# LIST FILTER	| DESCRIPTION 	| DEFINITION
		# --------------+---------------+-----------
		# id			| user ids		| 'id="3b679b8b-b56d-48e5-bbbe-7397899c8ca6, d1c0be68-30bd-4b06-af73-7da110dc62da"'

		if (isset($args['filters']['id'])) {$params['filters']['id'] = $args['filters']['id'];}


		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send User List Query
		// --------------------
		$data = $this->send_query('/users/', $parameters, 'get');
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# For api calls to /users/ the data.result_type is the string "users"
		# and the key data.users is an array of the returned user objects
		# The field data.meta.next_page_payload contains the filter and order information
		# and the page_no number for the next page; or is empty for the last page of the list.

		# (see User Object attributes in Create User method)

	}

	// --------------
	// List All Users
	// --------------
	// loop all list_user pages
	function users_list($args) {

		return $this->list_all('users', $args);

	}

	// ------------------
	// User Token Balance
	// ------------------
	// 110.1: use new balance endpoint
	// note: now returns balance object instead of just token_balance integer
	# /balances/{id} (GET)
	function user_balance($args) {

		// ensure we have an id to match with
		if ( !isset($args['id']) || empty($args['id']) ) {
			$this->debug_log("Error! Token Balance Endpoint requires id parameter.", $args); return false;
		}

		// [deprecate] old method via user_get endpoint
		// make sure we get the userlist as an array here
		// $this->result_format = 'array';
		// $data = $this->user_get($args);
		// ensure we have a valid data
		// if ( (!$data) || (!is_array($data)) || (!isset($data['data']['user']['token_balance'])) ) {
		// 	$this->debug_log("Error! Could not retrieve user token balance.", $args); return false;
		// }
		// return $data['data']['user']['token_balance'];

		$data = $this->send_query('/balances/'.$args['id'], array(), 'get');
		return $data;

		// For api calls to /balances/{user_id} the data.result_type is the string "balance"
		// and the key data.balance has a single element of the balance object with balance details of the user.

		# PARAMETER				| TYPE			| DESCRIPTION
		# ----------------------+---------------+--------------------------------------------
		# available_balance		| string<float>	| current available balance of the user in BT (airdropped_balance + token_balance)
		# airdropped_balance	| string<float>	| current balance of tokens that were airdropped to the user in BT
		# token_balance			| string<float>	| current balance of tokens in BT that users have earned within your branded token economy by performing the respective actions you defined.

	}


	# -----------------
	# Airdrop Endpoints
	# -----------------

	// ------------
	// Airdrop Drop
	// ------------
	# /users/airdrop/drop
	function airdrop_drop($args) {

		# Parameter			| Type		| Description
		# ------------------+-----------+----------------------------------------------
		# amount			| float		| (mandatory) The amount of BT that needs to be air-dropped to the selected end-users. Example:10
		# airdropped		| boolean	| true/false. Indicates whether to airdrop tokens to end-users who have been airdropped some tokens at least once or to end-users who have never been airdropped tokens.
		# user_ids			| string	| a comma-separated list of user_ids specifies selected users in the token economy to be air-dropped tokens to.

		# airdropped | user_ids				| Expected Behaviour
		# -----------+----------------------+------------------------------------------
		# true		 | comma-separated list	| Extracts a list of all users you have been airdropped tokens at least once. Further refines the list to specific user ids passed in parameter 'user_ids'. This refined list is sent the tokens specified in the 'amount' parameter.
		# true		 | not set				| Extracts a list of all users you have been airdropped tokens at least once. This list is sent the tokens specified in the 'amount' parameter.
		# false		 | comma-separated list | Extracts a list of all users you have never been airdropped tokens further refines the list to specific user ids passed in parameter 'user_ids'. This refined list is sent the tokens specified in the 'amount' parameter.
		# false		 | not set				| Extracts a list of all users you have never been airdropped tokens. This list is sent the tokens specified in the 'amount' parameter.
		# not set	 | comma-separated list	| The list to specific user ids is sent the tokens specified in the 'amount' parameter.
		# not set	 | not set				| ALL users are sent the tokens specified in the 'amount' parameter.

		// Amount
		// ------
		if (!isset($args['amount'])) {
			$this->debug_log("Failed! Airdrop amount is required.", $args); return false;
		} elseif (abs(intval($args['amount'])) < 1) {
			$this->debug_log("Failed! Airdrop amount must be 1 or over.", $args['amount']); return false;
		} else {$parameters['amount'] = $args['amount'];}


		// Airdropped
		// ----------
		if (isset($args['airdropped'])) {
			if ($args['airdropped'] === true) {$parameters['airdropped'] = 'true';}
			else {$parameters['airdropped'] = 'false';}
		} elseif (isset($args['list_type'])) {
			// make backwards compatible with string arguments passed
			if ($args['list_type'] == 'never_airdropped') {$parameters['airdropped'] = 'false';}
			elseif ($args['list_type'] == 'airdropped') {$parameters['airdropped'] = 'true';}
			elseif ($args['list_type'] != 'all') {
				$this->debug_log("Warning! Airdrop Drop Endpoint incorrect 'list_type' parameter value.", $args['list_type']);
			}
			// otherwise the API assumes 'all' anyway
			// else {$parameters['airdropped'] = null;
		}

		// User IDs
		// --------
		// TEST: whether API can handle both ',' and ', ' delimiting ?
		if (isset($args['user_ids'])) {
			if (is_array($args['user_ids'])) {$parameters['user_ids'] = implode(',', $args['user_ids']);}
			else {$parameters['user_ids'] = $args['user_ids'];}
		}

		// Send Airdrop Query to API
		// -------------------------
		$data = $this->send_query('/airdrops/', $parameters);
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# The returned data.airdrop_uuid is a string containing the airdrop reference id,
		# that can be used to check the airdrop status using the Airdrop Status API endpoint.

	}

	// --------------
	// Airdrop Status
	// --------------
	# /airdrops/{id} (GET)
	function airdrop_get($args) {

		// Airdrop UUID
		// ------------
		if (!isset($args['id'])) {
			if (isset($args['airdrop_uuid'])) {$args['id'] == $args['airdrop_uuid'];}
			else {
				$this->debug_log("Failed! Airdrop Status Endpoint requires an airdrop id.", $args); return false;
			}
		}

		// Send Airdrop Status Query to API
		// --------------------------------
		$data = $this->send_query('/airdrops/'.$args['id'], array(), 'get');
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

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

	// -------------
	// List Airdrops
	// -------------
	# /airdrops/ (GET)
	function airdrop_list($args) {

		# Input Parameter	| Type		| Description
		# ------------------+-----------+----------------------------
		# page_no			| number	| page number (starts from 1)
		# order_by			| string	| (optional) order the list by when the airdrop was executed (default). Can only order by execution date.
		# order				| string	| orders the list in 'desc' (default). Accepts value 'asc' to order in ascending order.
		# limit				| number	| limits the number of user objects to be sent in one request(min. 1, max. 100, default 10)

		// Page Number
		// -----------
		if (!isset($args['page_no'])) {$this->debug_log("Failed! Airdrop Listing Endpoint requires page number.");}
		$page_no = abs(intval($args['page_no']));
		if ($page_no < 1) {$this->debug_log("Failed! Airdrop Listing page number must be 1 or over.");}
		else {$parameters['page_no'] = $args['page_no'];}

		// Order By
		// --------
		// note: can only order by execution date

		// Order
		// -----
		if (isset($args['order'])) {
			if ( ($args['order'] != 'asc') && ($args['order'] != 'desc') ) {
				$this->debug_log("Warning! Airdrop List Endpoint incorrect 'order' Parameter value.", $args['order']);
			} else {$parameters['order'] = $args['order'];}
		}

		// Limit
		// -----
		if (isset($args['limit'])) {
			$limit = (int)$args['limit'];
			if ($limit > 0) {
				if ($limit > 100) {$limit = 100;}
				$parameters['limit'] = $limit;
			}
		}

		// Optional Filters
		// ----------------

		# LIST FILTER		| DESCRIPTION
		# ------------------+------------
		# id				| Airdrop ids
		#					| Example: 'id="bc6dc9e1-6e62-4032-8862-6f664d8d7541, 94543988-9fa6-4d0a-8a9f-d65d345f6175"'
		# current_status	| indicates the stage at which the executed airdrop is in.
		#					| Example: 'current_status="complete, pending"'

		if (isset($args['filters']['id'])) {$params['filters']['id'] = $args['filters']['id'];}
		if (isset($args['filters']['current_status'])) {$params['filters']['current_status'] = $args['filters']['current_status'];}


		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send User List Query
		// --------------------
		$data = $this->send_query('/airdrops/', $parameters, 'get');
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

	}


	// -----------------
	// List All Airdrops
	// -----------------
	// loop all airdrop_list pages
	function airdrops_list($args) {

		return $this->list_all('airdrops', $args);

	}


	# ----------------
	# ACTION ENDPOINTS
	# ----------------

	// -------------
	// Create Action
	// -------------
	function create_action($args) {

		# Input Parameter		| Type			| Description
		# ----------------------+---------------+--------------------------------------
		# name					| string		| (mandatory) unique name of the action
		# kind					| string		| an action can be one of three kinds: "user_to_user", "company_to_user", or "user_to_company" to clearly determine whether value flows within the application or from or to the company.
		# currency				| string		| (mandatory) type of currency the action amount is specified in. Possible values are "USD" (fixed) or "BT" (floating). When an action is set in fiat the equivalent amount of branded tokens are calculated on-chain over a price oracle. For OST KIT? price points are calculated by and taken from coinmarketcap.com and published to the contract by OST.com.
		# arbitrary_amount		| boolean		| (mandatory) true/false. Indicates whether amount (described below) is set in the action, or whether it will be provided at the time of execution (i.e., when creating a transaction).
		# amount				| string<float>	| amount of the action set in "USD" (min USD 0.01 , max USD 100) or branded token "BT" (min BT 0.00001, max BT 100). The transfer on-chain always occurs in branded token and fiat value is calculated to the equivalent amount of branded tokens at the moment of transfer.
		# arbitrary_commission	| boolean		| true/false. Like 'arbitrary_amount' this attribute indicates whether commission_percent (described below) is set in the action, or whether it will be provided at the time of execution (i.e., when creating a transaction).
		# commission_percent	| string<float>	| for user_to_user action you have an option to set commission percentage. The commission is inclusive in the amount and the percentage of the amount goes to the OST partner company. Possible values (min 0%, max 100%)

		// Action Name
		// -----------
		$parameters['name'] = $this->validate_name($args['name'], 'Create Transaction Type');

		// Action Kind
		// -----------
		$kinds = array('user_to_user', 'company_to_user', 'user_to_company');
		if (!isset($args['kind'])) {
			$this->debug_log("Failed! Create Action Endpoint requires a kind value. Be nice.");
		} elseif (!in_array($args['kind'], $kinds)) {
			$this->debug_log("Failed! Create Action Endpoint incorrect kind value.", $args['kind']);
		} else {$parameters['kind'] = $args['kind'];}

		// Currency Type
		// -------------
		$currencytypes = array('USD', 'BT');
		if (!isset($args['currency'])) {
			$this->debug_log("Failed! Create Action Endpoint requires a currency value.");
		} elseif (!in_array($args['currency'], $currencytypes)) {
			$this->debug_log("Failed! Create Action Endpoint incorrect currency value.", $args['currency']);
		} else {$parameters['currency'] = $args['currency'];}

		// Arbitrary Amount Switch
		// -----------------------
		if (!isset($args['arbitrary_amount'])) {
			$this->debug_log("Failed! Create Action Endpoint requires arbitrary_amount switch value.");
		} elseif ($args['arbitrary_amount'] == 'true') {$parameters['arbitrary_amount'] = 'true';}
		elseif ($args['arbitrary_amount'] == 'false') {
			$parameters['arbitrary_amount'] = 'false';

			// Fixed Action Amount
			// -------------------
			if (!isset($args['amount'])) {$this->debug_log("Failed! Create Transaction Type requires amount.");}
			else {
				if ($parameters['currency'] == 'USD') {
					if ($args['amount'] < 0.01) {
						$this->debug_log("Failed! Create Action for USD amount must be greater than 0.01", $args['amount']);
					} elseif ($args['amount'] > 100) {
						$this->debug_log("Failed! Create Action for USD amount must be less than 100.", $args['amount']);
					} else {$parameters['amount'] = $args['amount'];}
				} elseif ($parameters['currency'] == 'BT') {
					if ($args['amount'] < 0.00001) {
						$this->debug_log("Failed! Create Action for Branded Token amount must be greater than 0.00001", $args['amount']);
					} elseif ($args['amount'] > 100) {
						$this->debug_log("Failed! Create Action for Branded Token amount must be less than 100.", $args['amount']);
					} else {$parameters['amount'] = $args['amount'];}

				}
			}
		}

		// Arbitrary Commission Switch
		// ---------------------------
		if ($args['kind'] == 'user_to_user') {
			if (!isset($args['arbitrary_commission'])) {
				// TEST: if arbitrary_commission a required argument for user_to_user actions ?
				$this->debug_log("Failed! Create Action Endpoint of kind user_to_user requires arbitrary_commission switch value.");
			} elseif ($args['arbitrary_commission'] == 'true') {

				// Arbitrary Commission
				// --------------------
				$parameters['arbitrary_commission'] = 'true';
				if (isset($args['commission_percent'])) {
					$this->debug_log("Failed! Create Action Endpoint needs arbitrary_commission OR fixed commission_percent not both.");
				}

			} elseif ($args['arbitrary_commission'] == 'false') {

				// Fixed Commission Percent
				// ------------------------
				$parameters['arbitrary_commission'] = 'false';
				if (!isset($args['commission_percent'])) {$this->debug_log("Failed! Create Action Endpoint for fixed commission requires commission_percent.");}
				else {
					// TEST: decimal value accuracy for commission_percent ?
					if ($args['commission_percent'] < 0) {
						$this->debug_log("Failed! Create Action fixed commission_percent must be greater than 0.", $args['commission_percent']);
					} elseif ($args['commission_percent'] > 100) {
						$this->debug_log("Failed! Create Transaction Type commission_percent must be less than 100.", $args['commission_percent']);
					} else {$parameters['commission_percent'] = $args['commission_percent'];}
				}

			}
		}

		// bug out if there were errors
		if ($this->errors) {return false;}


		// Send Create Transaction Type Query to API
		// -----------------------------------------
		$data = $this->send_query('/actions/', $parameters);
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# On calling /actions/ the data.result_type is the string "action"
		# and the key data.action is an array containing the created action type object,
		# with data.action.id set as the newly created action type id

	}

	// ---------------
	// Get Action Type
	// ---------------
	# /actions/{id} (GET)
	function action_get($args) {

		// Action ID (required)
		// --------------------
		if (!isset($args['id'])) {
			$this->debug_log("Failed! Get Action Endpoint requires an Action ID.", $args);
		}

		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send Get User Query to API
		// --------------------------
		$data = $this->send_query('/actions/'.$args['id'], array(), 'get');
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# (see User Object attributes in Create User method)

	}

	// ----------------
	// Edit Action Type
	// ----------------
	# /actions/{id} (POST)
	function action_edit($args) {

		# Input Parameter		| Type			| Description
		# ----------------------+---------------+--------------------------------------
		# name					| string		| (mandatory) unique name of the action
		# kind					| string		| an action can be one of three kinds: "user_to_user", "company_to_user", or "user_to_company" to clearly determine whether value flows within the application or from or to the company.
		# currency				| string		| (mandatory) type of currency the action amount is specified in. Possible values are "USD" (fixed) or "BT" (floating). When an action is set in fiat the equivalent amount of branded tokens are calculated on-chain over a price oracle. For OST KIT? price points are calculated by and taken from coinmarketcap.com and published to the contract by OST.com.
		# arbitrary_amount		| boolean		| (mandatory) true/false. Indicates whether amount (described below) is set in the action, or whether it will be provided at the time of execution (i.e., when creating a transaction).
		# amount				| string<float>	| amount of the action set in "USD" (min USD 0.01 , max USD 100) or branded token "BT" (min BT 0.00001, max BT 100). The transfer on-chain always occurs in branded token and fiat value is calculated to the equivalent amount of branded tokens at the moment of transfer.
		# arbitrary_commission	| boolean		| true/false. Like 'arbitrary_amount' this attribute indicates whether commission_percent (described below) is set in the action, or whether it will be provided at the time of execution (i.e., when creating a transaction).
		# commission_percent	| string<float>	| for user_to_user action you have an option to set commission percentage. The commission is inclusive in the amount and the percentage of the amount goes to the OST partner company. Possible values (min 0%, max 100%)

		// Action ID
		// ---------
		if (!isset($args['id'])) {
			$this->debug_log("Failed! Edit Action Type Endpoint requires action id.");
		} elseif (isset($args['client_transaction_id'])) {
			// this is a backwards compatible id key
			$parameters['id'] = $args['client_transaction_id'];
		} else {$parameters['id'] = $args['id'];}

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
			if (!in_array($args['kind'], $kinds)) {
				$this->debug_log("Failed! Edit Action Type Endpoint incorrect kind value.", $args['kind']);
			} else {$parameters['kind'] = $args['kind'];}
		}

		// Currency Type
		// -------------
		$currencytypes = array('USD', 'BT');
		if (isset($args['currency'])) {
			if (!in_array($args['currency'], $currencytypes)) {
				$this->debug_log("Failed! Create Transaction Type Endpoint incorrect currency value.", $args['currency_type']);
			} else {$parameters['currency'] = $args['currency'];}
		}

		// Arbitrary Amount Switch
		// -----------------------
		if (isset($args['arbitrary_amount'])) {
			if ($args['arbitrary_amount'] == 'true') {$parameters['arbitrary_amount'] = 'true';}
			elseif ($args['arbitrary_amount'] == 'false') {$parameters['arbitrary_amount'] = 'false';}
		}

		// Amount
		// ------
		// note: can only change amount if arbitrary amount is false (may be previously set to false)
		if ( (!isset($args['arbitrary_amount'])) || ($parameters['arbitrary_amount'] == 'false') ) {
			if (isset($args['amount'])) {
				if (isset($args['currency'])) {
					if ($parameters['currency'] == 'USD') {
						if ($args['amount'] < 0.01) {
							$this->debug_log("Failed! Edit Transaction Type for USD currency_value must be greater than 0.01", $args['amount']);
						} elseif ($args['amount'] > 100) {
							$this->debug_log("Failed! Edit Transaction Type for USD currency_value must be less than 100.", $args['amount']);
						} else {$parameters['amount'] = $args['amount'];}
					} elseif ($parameters['currency'] == 'BT') {
						if ($args['amount'] < 0.00001) {
							$this->debug_log("Failed! Edit Transaction Type for Branded Token amount must be greater than 0.01", $args['amount']);
						} elseif ($args['amount'] > 100) {
							$this->debug_log("Failed! Edit Transaction Type for Branded Token amount must be less than 100.", $args['amount']);
						} else {$parameters['amount'] = $args['amount'];}
					}
				} else {$parameters['amount'] = $args['amount'];}
			}
		}

		// RETEST: setting arbitrary commission switch fails either way!

		// Arbitrary Commission Switch
		// ---------------------------
		if (isset($args['arbitrary_commission'])) {
			if ($args['arbitrary_commission'] == 'true') {$parameters['arbitrary_commission'] = 'true';}
			elseif ($args['arbitrary_commission'] == 'false') {$parameters['arbitrary_commission'] = 'false';}
		}

		// Commission Percent
		// ------------------
		if (isset($args['commission_percent'])) {
			// can only be set if arbitrary commission is set to false (but may be previously set to false)
			if ( (!isset($args['arbitrary_commission'])) || ($parameters['arbitrary_commission'] == 'false') ) {
				if ($args['commission_percent'] < 0) {
					$this->debug_log("Failed! Edit Action Type commission_percent must be greater than 0.", $args['commission_percent']);
				} elseif ($args['commission_percent'] > 100) {
					$this->debug_log("Failed! Edit Action Type commission_percent must be less than 100.", $args['commission_percent']);
				} else {$parameters['commission_percent'] = $args['commission_percent'];}
			}
		}

		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send Edit Transaction Type Query to API
		// ---------------------------------------
		$data = $this->send_query('/actions/'.$args['id'], $parameters);
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# On calling /transaction-types/edit the data.result_type is the string "action"
		# and the key data.action is an array containing the edited transaction type object
		# with the parameters changed.

	}

	// ----------------------
	// List Transaction Types
	// ----------------------
	# /actions/ (GET)
	function action_list($args) {

		# Input Parameter	| Type		| Description
		# ------------------+-----------+----------------------------
		# page_no			| number	| page number (starts from 1)
		# order_by			| string	| order the list by when the action was created (default) . Can also order by the 'name' of the action
		# order				| string	| orders the list in 'desc' (default). Accepts value 'asc' to order in ascending order.
		# limit				| number	| limits the number of action objects to be sent in one request. Possible Values Min 1, Max 100, Default 10.

		// Page Number
		// -----------
		if (!isset($args['page_no'])) {$this->debug_log("Failed! List Actions Endpoint requires page number.");}
		$page_no = abs(intval($args['page_no']));
		if ($page_no < 1) {$this->debug_log("Failed! List Actions page number must be 1 or over.");}
		else {$parameters['page_no'] = $args['page_no'];}

		// Order By
		// --------
		if (isset($args['order_by'])) {
			if ( ($args['order_by'] != 'creation_time') && ($args['order_by'] != 'name') ) {
				$this->debug_log("Warning! List Actions Endpoint incorrect 'order_by' parameter value.", $args['order_by']);
			} else {$parameters['order_by'] = $args['order_by'];}
		}

		// Order
		// -----
		if (isset($args['order'])) {
			if ( ($args['order'] != 'asc') && ($args['order'] != 'desc') ) {
				$this->debug_log("Warning! List Actions Endpoint incorrect 'order' Parameter value.", $args['order']);
			} else {$parameters['order'] = $args['order'];}
		}

		// Limit
		// -----
		if (isset($args['limit'])) {
			$limit = (int)$args['limit'];
			if ($limit > 0) {
				if ($limit > 100) {$limit = 100;}
				$parameters['limit'] = $limit;
			}
		}

		// Optional Filters
		// ----------------

		# LIST FILTER		| DESCRIPTION
		# ------------------+-----------------------------
		# id				| Action ids
		#					| Example: 'id="20346, 20346"'
		# name				| names of the action
		# 					| Example: 'name="Like, Upvote"'
		# kind				| the kind of the action set during the creation of the action
		#					| 'kind="user_to_user"'
		# arbitrary_amount	| actions where the amount is set during creation or provided at execution
		#					| Example: 'arbitrary_amount= false'

		if (isset($args['filters']['id'])) {$params['filters']['id'] = $args['filters']['id'];}
		if (isset($args['filters']['name'])) {$params['filters']['name'] = $args['filters']['name'];}
		if (isset($args['filters']['kind'])) {$params['filters']['kind'] = $args['filters']['kind'];}
		if (isset($args['filters']['arbitrary_amount'])) {$params['filters']['arbitrary_amount'] = $args['filters']['arbitrary_amount'];}


		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send List Action Types Query to API
		// -----------------------------------
		$data = $this->send_query('/actions/', $parameters, 'get');
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# For api calls to /actions the data.result_type is the string "action"
		# and the key data.action is an array of all action objects.

	}

	// -----------------
	// List All Actions
	// -----------------
	// loop all action_list pages
	function actions_list($args) {
		return $this->list_all('actions', $args);
	}


	# ---------------------
	# TRANSACTION ENDPOINTS
	# ---------------------

	// -------------------
	// Execute Transaction
	// -------------------
	# /transactions/ (POST)
	# Important Note!!! Ref: https://dev.ost.com/docs/api_action_execute.html
	# "We have disabled pessimistic concurrency control to ensure that no false positives are returned.
	# As a result you must query /transactions/{id} for successful completion of the transaction."
	function transaction_execute($args) {

		# Input Parameter	| Type				| Description
		# ------------------+-------------------+--------------------------------------------
		# from_user_id		| string			| user or company from whom to send the funds
		# to_user_id		| string			| user or company to whom to send the funds
		# action_id			| number			| id of the action that is to be executed.
		# amount			| string<float>		| amount of the action set in "USD" (min USD 0.01 , max USD 100) or branded token "BT" (min BT 0.00001, max BT 100). amount is set at execution when parameter arbitrary_amount is set to true while defining the action specified in action_id .
		# commission_percent| string<float>		| for a user_to_user action commission percentage is set at execution when parameter arbitrary_commission is set to true while defining the action specified in action_id . The commission is inclusive in the amount and the percentage commission goes to the OST partner company. Possible values (min 0%, max 100%)

		// Action ID (required)
		// --------------------
		if (!isset($args['action_id'])) {
			$this->debug_log("Failed! Execute Transaction Endpoint requires an action_id parameter.");
		} else {$parameters['action_id'] = $args['action_id'];}

		// From UUID (required)
		// --------------------
		// note: company UUID no longer required if action kind is set to company_to_user
		if (!isset($args['from_user_id'])) {
			if (isset($args['from_uuid'])) {$parameters['from_user_id'] = $args['from_user_id'];}
			else {
				// TODO: get the action and check kind value is company_to_user
				// $args['id'] = $args['action_id'];
				// $result = new OST_Query($args);
				// if (isset($result['data']['action']['kind'])) {$kind = $result['data']['action']['kind'];}
				// if (!isset($kind) || ($kind != 'company_to_user')) {
					$this->debug_log("Failed! Execute Transaction Endpoint requires a from_user_id parameter.");
				// }
			}
		} else {$parameters['from_user_id'] = $args['from_user_id'];}

		// To UUID (required)
		// ------------------
		// note: company UUID no longer required if action kind is set to user_to_company
		if (!isset($args['to_user_id'])) {
			if (isset($args['to_uuid'])) {$parameters['to_user_id'] = $args['to_uuid'];}
			else {
				// TODO: get the action and check kind value is user_to_company
				// $args['id'] = $args['action_id'];
				// $result = new OST_Query($args);
				// if (isset($result['data']['action']['kind'])) {$kind = $result['data']['action']['kind'];}
				// if (!isset($kind) || ($kind != 'user_to_company')) {
					$this->debug_log("Failed! Execute Transaction Endpoint requires a to_uuid parameter.");
				// }
			}
		} else {$parameters['to_user_id'] = $args['to_user_id'];}

		// Amount
		// ------
		if (isset($args['amount'])) {
			// for when parameter arbitrary_amount is set to true for the action type
			// note: there is no way to set currency type here or check min/max
			// - set in "USD" (min USD 0.01 , max USD 100)
			// - or branded token "BT" (min BT 0.00001, max BT 100)
			$parameters['amount'] = $args['amount'];
		}

		// Commission Percent
		// ------------------
		if (isset($args['commission_percent'])) {
			// for when parameter arbitrary_commission is set to true for the action type
			// note; there is no way to check min/max values here (min 0%, max 100%)
			$parameters['commission_percent'] = $args['commission_percent'];
		}

		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send Transaction Execute Query to API
		// -------------------------------------
		$data = $this->send_query('/transactions/', $parameters);
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# For API calls to /transactions the result_type is a string "transaction" and the key
		# data.transaction is an object containing the attributes of the transaction.

		# Transaction Object Attributes
		# Transaction Attribute | Type			| DEFINITION
		# ----------------------+---------------+--------------------------------------------
		# id					| string		| id of the transaction
		# from_user_id			| string		| origin user of the branded token transaction.
		# to_user_id			| string		| destination user of the branded token transaction
		# transaction_hash		| hexstring		| the generated transaction hash
		# action_id				| number		| id of the action that was executed.
		# timestamp				| number		| universal time stamp value of execution of the transaction in milliseconds
		# status				| string		| the execution status of the transaction: "processing", "failed" or "complete"
		# gas_price				| string<number>| value of the gas utilized for the transaction
		# gas_used				| number		| (optional) hexadecimal value of the gas used to execute the tranaction
		# transaction_fee		| string<float> | (optional) the value of the gas used at the gas price
		# block_number			| string<number>| (optional) the block on the chain in which the transaction was included
		# amount				| string<float>	| (optional) the amount of branded tokens transferred to the destination user
		# commission_amount		| string<float>	| (optional) the amount of branded tokens transferred to the company

	}

	// ------------------
	// Transaction Status
	// ------------------
	# /transactions/{id} (GET)
	function transaction_get($args) {

		# Input Parameter		| Type		| Description
		# ----------------------+-----------+----------------------------------------------
		# id					| string	| unique identifier for an executed transaction

		if (!isset($args['id'])) {
			if (isset($args['transaction_uuid'])) {$args['id'] = $args['transaction_uuid'];}
			else {$this->debug_log("Failed! Transaction Status Endpoint requires a transaction_uuids parameter.");}
		}

		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send Transaction Status Query to API
		// ------------------------------------
		$data = $this->send_query('/transactions/'.$args['id'], array(), 'get');
		if ($this->result_format == 'array') {

			$data = json_decode($data, true);

			// maybe set OST view url for the transaction_hash
			if ($data['data']['transaction']['transaction_hash']) {
				// 100.3: set view_url as a data subkey
				$data['data']['view_url'] = "https://view.ost.com/chain-id/".$this->chain_id;
				$data['data']['view_url'] .= "/transaction/".$data['data']['transaction']['transaction_hash'];
			}
		}

		return $data;

		# For API calls to /transactions/{id} the result_type is a string "transaction"
		# and the key data.transaction is an object containing the attributes of the transaction.

	}


	// -----------------
	// List Transactions
	// -----------------
	# /transactions/ (GET)
	function transaction_list($args) {

		# Input Parameter	| Type		| Description
		# ------------------+-----------+----------------------------
		# page_no			| number	| page number (starts from 1)
		# order_by			| string	| order the list by when the transaction was created (default) . Can only be ordered by transaction creation date.
		# order				| string	| orders the list in 'desc' (default). Accepts value 'asc' to order in ascending order.
		# limit				| number	| limits the number of action objects to be sent in one request. Possible Values Min 1, Max 100, Default 10.

		// Page Number (required)
		// ----------------------
		if (!isset($args['page_no'])) {$this->debug_log("Failed! List Transactions Endpoint requires page number.");}
		$page_no = abs(intval($args['page_no']));
		if ($page_no < 1) {$this->debug_log("Failed! List Transactions page number must be 1 or over.");}
		else {$parameters['page_no'] = $args['page_no'];}

		// Order By
		// --------
		// note: can only be ordered by creation date

		// Order
		// -----
		if (isset($args['order'])) {
			if ( ($args['order'] != 'asc') && ($args['order'] != 'desc') ) {
				$this->debug_log("Warning! List Transactions Endpoint incorrect 'order' Parameter value.", $args['order']);
			} else {$parameters['order'] = $args['order'];}
		}

		// Limit
		// -----
		if (isset($args['limit'])) {
			$limit = (int)$args['limit'];
			if ($limit > 0) {
				if ($limit > 100) {$limit = 100;}
				$parameters['limit'] = $limit;
			}
		}

		// Optional Filters
		// ----------------

		# LIST FILTER	| DESCRIPTION		| EXAMPLE
		# --------------+-------------------+-------------------------------------------
		# id			| Transaction ids	| 'id="e1f95fcb-5853-453a-a9b3-d4f7a38d5beb, e7800825-fd24-4574-b7a6-06472ca1ef9d"'

		if (isset($args['filters']['id'])) {$params['filters']['id'] = $args['filters']['id'];}


		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send List Transactions Query to API
		// -----------------------------------
		$data = $this->send_query('/transactions/', $parameters, 'get');
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# For API calls to /transactions the result_type is a string "transactions",
		# that is an array containing objects each with the attributes of the executed transaction.

	}

	// --------------------
	// List All Transctions
	// --------------------
	// loop all transaction_list pages
	function transactions_list($args) {
		return $this->list_all('transactions', $args);
	}

	// -----------
	// User Ledger
	// -----------
	// 110.1: add user ledger endpoint
	// /ledger/{id}
	function user_ledger($args) {

		// User ID (required)
		// ------------------
		if (!isset($args['id'])) {
			$this->debug_log("Failed! Ledger Endpoint an id parameter.");
		}

		// Page Number (required)
		// ----------------------
		if (!isset($args['page_no'])) {$this->debug_log("Failed! User Ledger Endpoint requires page number.");}
		$page_no = abs(intval($args['page_no']));
		if ($page_no < 1) {$this->debug_log("Failed! User Ledger page number must be 1 or over.");}
		else {$parameters['page_no'] = $args['page_no'];}

		// Order By
		// --------
		// note: can only be ordered by creation date

		// Order
		// -----
		if (isset($args['order'])) {
			if ( ($args['order'] != 'asc') && ($args['order'] != 'desc') ) {
				$this->debug_log("Warning! User Ledger Endpoint incorrect 'order' Parameter value.", $args['order']);
			} else {$parameters['order'] = $args['order'];}
		}

		// Limit
		// -----
		if (isset($args['limit'])) {
			$limit = (int)$args['limit'];
			if ($limit > 0) {
				if ($limit > 100) {$limit = 100;}
				$parameters['limit'] = $limit;
			}
		}

		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send User Ledger Query to API
		// -----------------------------
		$data = $this->send_query('/ledger/'.$args['id'], array(), 'get');
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

	}

	// ----------------
	// All User Ledgers
	// ----------------
	// 110.1: get all user ledger pages
	function user_ledgers($args) {
		return $this->list_all('ledgers', $args);
	}


	# ------------------ #
	# TRANSFER ENDPOINTS #
	# ------------------ #

	// ---------------
	// Create Transfer
	// ---------------
	# /transfers/ (POST)
	function transfer_create($args) {

		# PARAMETER			| TYPE		| DEFINITION
		# ------------------+-----------+-----------------------------------------------------------
		# to_address		| hexstring	| (mandatory) public address to which to transfer OSTa Prime
		# amount			| number	| (mandatory) amount of OSTa Prime to transfer in Wei; should be between 0 and 10^20, exclusive

		if (!isset($args['to_address'])) {
			$this->debug_log("Failed! Create Transfer Endpoint requires to_address.");
		} else {
			// validate hexstring for transfer to_address
			$validate = $this->validate_address($args['to_address']);
			if ($validate) {$params['to_address'] = $args['to_address'];}
			else {$this->debug_log("Failed! Transfer Endpoint to_address is not valid!");}
		}

		if (!isset($args['amount'])) {
			$this->debug_log("Failed! Create Transfer Endpoint requires amount.");
		} else {$params['amount'] = $args['amount'];}

		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send Create Transfer Query to API
		// ---------------------------------
		$data = $this->send_query('/transfers/', $parameters, 'post');
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# For calls to /transfers, data.result_type is the string "transfer"
		# and data.transfer is an object containing the attributes of the transfer.

		# PARAMETER			| TYPE				| DEFINITION
		# ------------------+-------------------+ ----------------------------------
		# id				| string			| identifier for the transfer object
		# from_address		| string			| token economy reserve address that is controlled by OST KIT? from which OST? Prime is transferred
		# to_address		| string			| address to which to transfer OST? Prime
		# amount			| string<number>	| amount of OST? Prime to transfer in Wei
		# transaction_hash	| string			| the generated transaction hash (null, initially)
		# timestamp			| number			| epoch time in milliseconds of current time
		# status			| string			| the execution status of the transfer: "processing", "failed" or "complete"
		# gas_price			| string<number>	| value of the gas utilized for the transfer
		# gas_used			| string			| (optional) hexadecimal value of the gas used to execute the transfer (null, initially)
		# block_number		| string<number>	| (optional) the block on the chain in which the transfer was included (null, initially)
		# chain_id			| string<number>	| the identifier of the chain to which the transfer transaction was sent

	}

	// ------------
	// Get Transfer
	// ------------
	# /transfers/{$id} (GET)
	function transfer_get($args) {

		# Input Parameter		| Type		| Description
		# ----------------------+-----------+--------------------------------------------------------------
		# id					| string	|  unique identifier returned during the creation of a transfer

		if (!isset($args['id'])) {
			$this->debug_log("Failed! Get Transfer Endpoint requires an id parameter.");
		}

		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send Get Transfer Query to API
		// ------------------------------
		$data = $this->send_query('/transactions/'.$args['id'], array(), 'get');
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# For calls to /transfers/{id}, data.result_type is the string "transfer"
		# and data.transfer is an object containing the attributes of the transfer.
		# (see returned object data above for Create Transfer.)

	}

	// -------------
	// Transfer List
	// -------------
	# /transfers/ (GET)
	function transfer_list($args) {

		# Input Parameter	| Type		| Description
		# ------------------+-----------+----------------------------
		# page_no			| number	| page number (starts from 1)
		# order_by			| string	| order the list by when the transaction was created (default) . Can only be ordered by transaction creation date.
		# order				| string	| orders the list in 'desc' (default). Accepts value 'asc' to order in ascending order.
		# limit				| number	| limits the number of action objects to be sent in one request. Possible Values Min 1, Max 100, Default 10.

		// Page Number (required)
		// ----------------------
		if (!isset($args['page_no'])) {$this->debug_log("Failed! List Transfers Endpoint requires page number.");}
		$page_no = abs(intval($args['page_no']));
		if ($page_no < 1) {$this->debug_log("Failed! List Transactions page number must be 1 or over.");}
		else {$parameters['page_no'] = $args['page_no'];}

		// Order By
		// --------
		// TODO: find out order_by parameters for transfer_list ?

		// Order
		// -----
		if (isset($args['order'])) {
			if ( ($args['order'] != 'asc') && ($args['order'] != 'desc') ) {
				$this->debug_log("Warning! List Transfers Endpoint incorrect 'order' Parameter value.", $args['order']);
			} else {$parameters['order'] = $args['order'];}
		}

		// Limit
		// -----
		if (isset($args['limit'])) {
			$limit = (int)$args['limit'];
			if ($limit > 0) {
				if ($limit > 100) {$limit = 100;}
				$parameters['limit'] = $limit;
			}
		}

		// Optional Filters
		// ----------------

		# LIST FILTER	| DESCRIPTION	| EXAMPLE
		# --------------+---------------+-------------------------------------------
		# id			| Transfer ids	| 'id="2c66960e-0380-4f7b-8f41-c344d44ab3d4, cee672d6-bd9f-4f41-a18c-81b651ea9393"'

		if (isset($args['filters']['id'])) {$params['filters']['id'] = $args['filters']['id'];}


		// bug out if there were errors
		if ($this->errors) {return false;}

		// Send List Transfers Query to API
		// --------------------------------
		$data = $this->send_query('/transfers/', $parameters, 'get');
		if ($this->result_format == 'array') {$data = json_decode($data, true);}
		return $data;

		# On calling /transfers, data.result_type is the string "transfers"
		# and data.transfers is an object containing an array of transfer objects.
		# (see returned object data above for Create Transfer.)

	}

	// ------------------
	// List All Transfers
	// ------------------
	function transfers_list($args) {
		return $this->list_all('transfers', $args);
	}


	# -------------------- #
	# ABSTRACTED FUNCTIONS #
	# -------------------- #

	// ---------------------------------
	// List All Data for a List Endpoint
	// ---------------------------------
	// 100.3: common abstracted function for all list endpoints
	function list_all($endpoint, $args) {

		if (!isset($args['page_no'])) {$args['page_no'] = 1;}
		$args['limit'] = $this->per_page;
		$args['limit'] = 10;
		$actionlist = array();

		while ($args['page_no'] != '') {

			// retrieve list depending on endpoint
			if ($endpoint == 'users') {$result = $this->user_list($args);}
			elseif ($endpoint == 'airdrops') {$result = $this->airdrop_list($args);}
			elseif ($endpoint == 'actions') {$result = $this->action_list($args);}
			elseif ($endpoint == 'transactions') {$result = $this->transaction_list($args);}
			elseif ($endpoint == 'transfers') {$result = $this->transfer_list($args);}
			elseif ($endpoint == 'ledgers') {$result = $this->user_ledger($args);}

			if ($this->result_format == 'json') {$result = json_decode($result, true);}

			$success = false;
			if ($result && isset($result['success'])) {
				if ($result['success'] && ($result['data']['result_type'] == 'transactions')) {
					$data = $result['data']; $success = true;
				}
			}
			// rest and try again in case network is glitching
			if (!$success) {

				sleep(1);

				// retrieve list depending on endpoint
				if ($endpoint == 'users') {$result = $this->user_list($args);}
				elseif ($endpoint == 'airdrops') {$result = $this->airdrop_list($args);}
				elseif ($endpoint == 'actions') {$result = $this->action_list($args);}
				elseif ($endpoint == 'transactions') {$result = $this->transaction_list($args);}
				elseif ($endpoint == 'transfers') {$result = $this->transfer_list($args);}

				if ($this->result_format == 'json') {$result = json_decode($result, true);}
			}
			if ($result && isset($result['success'])) {
				if ($result['success'] && ($result['data']['result_type'] == $endpoint)) {
					$data = $result['data']; $success = true;
				}
			}

			// reset page number to maybe finish looping
			$args['page_no'] = '';

			// check for next page number in meta
			if ($success) {
				if (isset($data['meta']['next_page_payload']['page_no'])) {
					$args['page_no'] = $data['meta']['next_page_payload']['page_no'];
				}
				if (isset($data[$endpoint])) {
					foreach ($data[$endpoint] as $record) {$recordlist[] = $record;}
				}
			} else {
				$this->debug_log("Error! Could not retrieve ".$endpoint." list.", $result); return false;
			}
		}

		if ($this->result_format == 'json') {return json_encode($recordlist);}
		else {return $recordlist;}

	}


	# ------------------- #
	# API QUERY FUNCTIONS #
	# ------------------- #

	// -----------------------
	// Send API Query Abstract
	// -----------------------
	function send_query($endpoint, $parameters, $method='post') {
		$this->debug_log("Sanitized Query Parameters:", $parameters);
		$query = $this->build_query($endpoint, $parameters, $method);
		$this->debug_log("Signed API Querystring: ", $query);
		$data = $this->remote_query($endpoint, $query, $parameters, $method);
		$this->debug_log("API Response: ". $data);
		return $data;
	}

	// ---------------
	// Build API Query
	// ---------------
	// Credit: TechupBusiness via https://help.ost.com/support/discussions/topics/35000005112
	function build_query($endpoint, $parameters, $method) {

		$parameters['api_key'] = $this->api_key;
		$parameters['request_timestamp'] = time();

		// 100.3: handle optional filter parameters
		if (isset($parameters['filters'])) {
			$filters = $parameters['filters'];
			if (is_array($filters) && (count($filters) > 0)) {
				foreach ($filters as $key => $value) {
					if (is_string($value)) {$parameters[$key] = '"'.$value.'"';}
					elseif (is_array($value)) {
						$valuestring = implode(',', $value);
						$parameters[$key] = '"'.$valuestring.'"';
					}
				}
			}
			// ensure filters parameter is not set directly
			unset($parameters['filters']);
		}

		// alphabetically sort the parameters
		ksort($parameters);

        if ($method == 'post') {
	        $query = $endpoint.'?'.http_build_query($parameters, '', '&');
	        $query = str_replace('%5B%5D', '[]', $query);
	        $query = str_replace('%20', '+', $query);
	        $parameters['signature'] = hash_hmac('sha256', $query, $this->api_secret);
	        $query = http_build_query($parameters);
			return $query;
		}

		foreach ($parameters as $key => $value) {
			$key = strtolower($key);
			if (is_array($value)) {
				foreach ($value as $val) {$query_params[] = $key.'[]='.urlencode($val);}
			} else {$query_params[] = $key.'='.urlencode($value);}
		}

		// debug query parameters build method
		$this->debug_log("Query Params", $query_params);


		if ($method == 'get') {
			$query = $endpoint.'?'.implode('&', $query_params);
			$signature = hash_hmac('sha256', $query, $this->api_secret);
			$this->debug_log("Query", $query);
			return $query.'&signature='.$signature;
		} elseif ($method == 'post') {
			$query = $endpoint.'?'.implode('&', $query_params);
			$query_params['signature'] = hash_hmac('sha256', $query, $this->api_secret);
			$this->debug_log("Query", $query_params);
			$query = http_build_query($query_params);
			return $query;
		}

	}

	// ----------------
	// Remote API Query
	// ----------------
	function remote_query($endpoint, $query, $parameters, $method) {

		if ($method == 'post') {$url = $this->api_url.$endpoint;}
		elseif ($method == 'get') {$url = $this->api_url.$query;}

		// alternative POST method (non-CURL)
		if ($method == 'altpost') {
			echo $url;
			echo $query;
			$context = stream_context_create(array(
				'http' => array(
					'method' => 'POST',
					'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
					'content' => $query
				)
			));
			$response = file_get_contents($url, false, $context);

			// print_r($response);

			return $response;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connecttime);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		if ($method == 'post') {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
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

		// 100.3: sleep and retry on internal server error
		if ($httpcode == 500) {
			sleep(3); $contents = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		}

		$header = curl_getinfo($ch);
		$errorno = curl_errno($ch);
		$error = curl_error($ch);
		curl_close($ch); unset($ch);

		$response = array(
			'body' => $contents,
			'httpcode' => $httpcode,
			'header' => $header,
			'errno' => $errorno,
			'error' => $error
		);

		$debugresponse = $response;
		unset($debugresponse['body']);
		$this->debug_log("API HTTP Response", $debugresponse);
		@$responsebody = json_decode($response['body'], true);
		$this->debug_log("API Data Response", $responsebody);


		# Result Parameters | Type		| Description
		# ------------------+-----------+----------------
		# success			| bool		| post successful
		# data				| object	| (optional) data object describing result if successful
		# err				| object	| (optional) describing error if not successful

		// check API Response for Errors
		// -----------------------------
		// if ((int)$response['errorno'] !== 0) {
		// 	$this->debug_log("Error! API Connection Error ".$response['errorno'], $response['error']);
		// 	$error = true;
		// } else
		if ($response['httpcode'] != 200) {

			if ($response['httpcode'] == 401) {

				$this->debug_log("Failed! Unauthorized API Request", $responsebody);

			} else {

				// TODO: maybe check for other specific HTTP status codes
				// ref: https://dev.ost.com/docs/api_error_handling.html

				# CODE	STRING CODES			ERROR MESSAGES and CAUSE AND ACTIONABLE STEPS
				# 400	BAD_REQUEST				At least one parameter is invalid or missing. See "err.error_data" array for more details.
				#								Check the API Documentation for the endpoint to see which values are required. To prevent validation errors, ensure that parameters are of the right type.
				# 401	UNAUTHORIZED			We could not authenticate the request.
				#								Please review your credentials and authentication method	Check Authentication to understand the API signature generation steps.
				# 404	NOT_FOUND				The requested resource could not be located.
				#								Please check the information provided. The server did not find anything that matches the request URI. Either the URI is incorrect or the resource is not available. For example, in-correct 'id' passed while retrieving a user.
				# 422	INSUFFICIENT_FUNDS		The account executing the transaction or transfer does not have sufficient funds to complete the transaction or transfer.
				#								You'll need to add funds to your account or reduce the amount and send the request again.
				# 		UNPROCESSABLE_ENTITY	An error occurred while processing the request.
				#								The API cannot complete the requested action, might require interaction with processes outside of the current request OR is failing business validations thats not a 400 type of validation. Check the information provided or get in touch on help.ost.com
				# 500	INTERNAL_SERVER_ERROR	Something went wrong
				#								This is usually a temporary error, when the endpoint is temporarily having issues. Check in the gitter forums in case others are having similar issues or try again later. If the problem persists log a ticket on help.ost.com

				$this->debug_log("Failed! HTTP Response Code ".$response['httpcode'], $responsebody);
			}

		} elseif (empty($response['body'])) {

			// possible empty response if there is a connection problem
			$this->debug_log("Failed! Empty API Response Body", $response['header']);

		} else {

			// TODO: maybe check json formatting better ?
			if (!isset($data['success'])) {$this->debug_log("Failed! API JSON Response Corrupt", $response['body']);}
			// if (!$data['success']) {$this->debug_log("Request Failed with Error", $responsebody['err']);}

		}

		if ($this->error && isset($responsebody['err'])) {
			$this->debug_log("API Response Error Message", $responsebody['err']);
		}

		return $response['body'];
	}

 }
}


// ----------------------------------------
// === Shortcut Functions for API Class ===
// ----------------------------------------

global $ost_kit_args;


// -------------
// === TOKEN ===
// -------------

// -----
// Token
// -----
# /token
if (!function_exists('ost_kit_token_get')) {
 function ost_kit_token_get() {
 	global $ost_kit_args; $args = $ost_kit_args;
	$args['endpoint'] = 'token_get';
	$query = new OST_Query($args);
	return $query->response;
 }
}

// -------------
// === USERS ===
// -------------

// -----------
// Create User
// -----------
# /users/ (POST)
if (!function_exists('ost_kit_user_create')) {
 function ost_kit_user_create($name) {
 	global $ost_kit_args; $args = $ost_kit_args;
	$args['endpoint'] = 'user_create';
	$args['name'] = $name;
	$query = new OST_Query($args);
	return $query->response;
 }
}

// --------
// Get User
// --------
# /users/{id} (GET)
if (!function_exists('ost_kit_user_get')) {
 function ost_kit_user_get($id) {
 	global $ost_kit_args; $args = $ost_kit_args;
	$args['endpoint'] = 'user_get';
	$args['id'] = $id;
	$query = new OST_Query($args);
	return $query->response;
 }
}

// ---------
// Edit User
// ---------
# /users/{id} (POST)
if (!function_exists('ost_kit_user_edit')) {
 function ost_kit_user_edit($id, $name) {
 	global $ost_kit_args; $args = $ost_kit_args;
	$args['endpoint'] = 'user_edit';
	$args['id'] = $id;
	$args['name'] = $name;
	$query = new OST_Query($args);
	return $query->response;
 }
}

// ---------
// Get Users
// ---------
// TODO: get specific users via optional filters

// ----------
// List Users
// ----------
// retrieve user list page
# /users/ (GET)
if (!function_exists('ost_kit_user_list')) {
 function ost_kit_user_list($page_no=null, $airdropped=null, $order_by=null, $order=null, $limit=null, $filters=null) {
 	global $ost_kit_args; $args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'list_users';
	if (!is_null($page_no)) {$args['page_no'] = $page_no;} else {$args['page_no'] = 1;}
	// optional arguments
	if (!is_null($airdropped)) {$args['airdropped'] = $airdropped;}
	if (!is_null($order_by)) {$args['orderby'] = $order_by;}
	if (!is_null($order)) {$args['order'] = $order;}
	if (!is_null($limit)) {$args['limit'] = $limit;}
	if (!is_null($filters)) {$args['filters'] = $filters;}
	$query = new OST_Query($args);
	return $query->response;
 }
}

// --------------
// List All Users
// --------------
// retrieve full user list
if (!function_exists('ost_kit_users_list')) {
 function ost_kit_users_list($order_by=null, $order=null) {
 	set_time_limit(120);
 	global $ost_kit_args; $args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'users_list';
	// optional arguments
	if (!is_null($order_by)) {$args['orderby'] = $order_by;}
	if (!is_null($order)) {$args['order'] = $order;}
	$query = new OST_Query($args);
	return $query->response;
 }
}

// ------------------
// User Token Balance
// ------------------
// note: not an endpoint, returns balance from user_get
if (!function_exists('ost_kit_user_balance')) {
 function ost_kit_user_balance($id) {
 	global $ost_kit_args; $args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'user_balance';
	// 110.1: just use id not uuid
	$args['id'] = $id;
	$query = new OST_Query($args);
	return $query->response;
 }
}

// ----------------
// === AIRDROPS ===
// ----------------

// ------------
// Airdrop Drop
// ------------
# /airdrops/ (POST)
if (!function_exists('ost_kit_airdrop_drop')) {
 function ost_kit_airdrop_drop($amount, $airdropped=null, $user_ids=null) {
 	global $ost_kit_args; $args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'airdrop_drop';
	$args['amount'] = $amount;
	// optional arguments
	if (!is_null($airdropped)) {$args['airdropped'] = $airdropped;}
	if (!is_null($user_ids)) {$args['user_ids'] = $user_ids;}
	$query = new OST_Query($args);
	return $query->response;
 }
}

// --------------
// Airdrop Status
// --------------
# /airdrops/{id} (GET)
if (!function_exists('ost_kit_airdrop_get')) {
 function ost_kit_airdrop_get($id) {
 	global $ost_kit_args; $args = $ost_kit_args;
	$args['endpoint'] = 'airdrop_get';
	$args['id'] = $id;
	$query = new OST_Query($args);
	return $query->response;
 }
}

// -------------
// List Airdrops
// -------------
// retrieve airdrop list page
# /airdrops/ (GET)
if (!function_exists('ost_kit_airdrop_list')) {
 function ost_kit_airdrop_list($page_no=null, $order_by=null, $order=null, $limit=null, $filters=null) {
 	global $ost_kit_args; $args = $ost_kit_args;
 	// required arguments
	$args['endpoint'] = 'airdrop_list';
	if (!is_null($page_no)) {$args['page_no'] = $page_no;} else {$args['page_no'] = '1';}
	// optional arguments
	if (!is_null($order_by)) {$args['orderby'] = $order_by;}
	if (!is_null($order)) {$args['order'] = $order;}
	if (!is_null($limit)) {$args['limit'] = $limit;}
	if (!is_null($filters)) {$args['filters'] = $filters;}
	$query = new OST_Query($args);
	return $query->response;
 }
}

// -----------------
// List All Airdrops
// -----------------
// retrieve full airdrop list
if (!function_exists('ost_kit_airdrops_list')) {
 function ost_kit_airdrops_list($order_by=null, $order=null) {
 	set_time_limit(120);
 	global $ost_kit_args; $args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'airdrops_list';
	// optional arguments
	if (!is_null($order_by)) {$args['orderby'] = $order_by;}
	if (!is_null($order)) {$args['order'] = $order;}
	$query = new OST_Query($args);
	return $query->response;
 }
}

// ---------------
// === ACTIONS ===
// ---------------

// ------------------
// Create Action Type
// ------------------
# /actions (POST)
if (!function_exists('ost_kit_action_create')) {
 function ost_kit_action_create($name, $kind, $currency, $arbitrary_amount=null, $amount=null, $arbitrary_commission=null, $commission_percent=null) {
 	global $ost_kit_args; $args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'create_action';
	$args['name'] = $name;
	$args['kind'] = $kind;
	$args['currency'] = $currency;
	// optional arguments
	if (!is_null($arbitrary_amount)) {$args['arbitrary_amount'] = $arbitrary_amount;}
	if (!is_null($amount)) {$args['amount'] = $amount;}
	if (!is_null($arbitrary_commission)) {$args['arbitrary_commission'] = $arbitrary_commission;}
	if (!is_null($commission_percent)) {$args['commission_percent'] = $commission_percent;}
	$query = new OST_Query($args);
	return $query->response;
 }
}

// ---------------
// Get Action Type
// ---------------
# /actions/{id}
if (!function_exists('ost_kit_action_get')) {
 function ost_kit_action_get($id) {
 	global $ost_kit_args; $args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'action_get';
	$args['id'] = $id;
	$query = new OST_Query($args);
	return $query->response;
 }
}

// ----------------
// Edit Action Type
// ----------------
# /actions/{id} (POST)
if (!function_exists('ost_kit_action_edit')) {
 function ost_kit_action_edit($id, $name=null, $kind=null, $currency=null, $arbitrary_amount=null, $amount=null, $arbitrary_commission=null, $commission_percent=null) {
 	global $ost_kit_args; $args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'action_edit';
	$args['id'] = $id;
	// optional arguments
	if (!is_null($name)) {$args['name'] = $name;}
	if (!is_null($kind)) {$args['kind'] = $kind;}
	if (!is_null($currency)) {$args['currency'] = $currency_type;}
	if (!is_null($arbitrary_amount)) {$args['arbitrary_amount'] = $arbitrary_amount;}
	if (!is_null($amount)) {$args['amount'] = $amount;}
	if (!is_null($arbitrary_commission)) {$args['arbitrary_commission'] = $arbitrary_commission;}
	if (!is_null($commission_percent)) {$args['commission_percent'] = $commission_percent;}
	$query = new OST_Query($args);
	return $query->response;
 }
}

// -----------------
// List Action Types
// -----------------
// retrive action list page
# /actions/ (GET)
if (!function_exists('ost_kit_action_list')) {
 function ost_kit_action_list($page_no=null, $order_by=null, $order=null, $limit=null, $filters=null) {
 	global $ost_kit_args; $args = $ost_kit_args;
 	// required arguments
	$args['endpoint'] = 'action_list';
	if (!is_null($page_no)) {$args['page_no'] = $page_no;} else {$args['page_no'] = '1';}
	// optional arguments
	if (!is_null($order_by)) {$args['orderby'] = $order_by;}
	if (!is_null($order)) {$args['order'] = $order;}
	if (!is_null($limit)) {$args['limit'] = $limit;}
	if (!is_null($filters)) {$args['filters'] = $filters;}
	$query = new OST_Query($args);
	return $query->response;
 }
}

// ----------------
// List All Actions
// ----------------
// retrieves full action list
if (!function_exists('ost_kit_actions_list')) {
 function ost_kit_actions_list($order_by=null, $order=null) {
 	set_time_limit(120);
 	global $ost_kit_args; $args = $ost_kit_args;
	$args['endpoint'] = 'actions_list';
	// optional arguments
	if (!is_null($order_by)) {$args['order_by'] = $order_by;}
	if (!is_null($order)) {$args['order'] = $order;}
	$query = new OST_Query($args);
	return $query->response;
 }
}


// --------------------
// === TRANSACTIONS ===
// --------------------

// -------------------
// Execute Transaction
// -------------------
# /transactions execute (POST)
if (!function_exists('ost_kit_transaction_execute')) {
 function ost_kit_transaction_execute($from_user_id, $to_user_id, $action_id, $amount=null, $commission_percent=null) {
  	global $ost_kit_args; $args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'transaction_execute';
	$args['from_user_id'] = $from_user_id;
	$args['to_user_id'] = $to_user_id;
	$args['action_id'] = $action_id;
	// optional arguments
	if (!is_null($amount)) {$args['amount'] = $amount;}
	if (!is_null($commission_percent)) {$args['commission_percent'] = $commission_percent;}
	$query = new OST_Query($args);
	return $query->response;
 }
}

// ------------------
// Transaction Status
// ------------------
# /transactions get status (GET)
if (!function_exists('ost_kit_transaction_get')) {
 function ost_kit_transaction_get($id) {
 	global $ost_kit_args; $args = $ost_kit_args;
	$args['endpoint'] = 'transaction_get';
	$args['id'] = $id;
	$query = new OST_Query($args);
	return $query->response;
 }
}

// -----------------
// List Transactions
// -----------------
// retrive transaction list page
# /transactions/ (GET)
if (!function_exists('ost_kit_transaction_list')) {
 function ost_kit_transaction_list($page_no=null, $order_by=null, $order=null, $limit=null, $filters=null) {
 	global $ost_kit_args; $args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'transaction_list';
	if (!is_null($page_no)) {$args['page_no'] = $page_no;} else {$args['page_no'] = '1';}
	// optional arguments
	if (!is_null($order_by)) {$args['order_by'] = $order_by;}
	if (!is_null($order)) {$args['order'] = $order;}
	if (!is_null($limit)) {$args['limit'] = $limit;}
	if (!is_null($filters)) {$args['filters'] = $filters;}
	$query = new OST_Query($args);
	return $query->response;
 }
}

// --------------------
// List All Transctions
// --------------------
// retrieves full transaction list
if (!function_exists('ost_kit_transactions_list')) {
 function ost_kit_transactions_list($order_by=null, $order=null) {
 	set_time_limit(120);
 	global $ost_kit_args; $args = $ost_kit_args;
	$args['endpoint'] = 'transactions_list';
	// optional arguments
	if (!is_null($order_by)) {$args['order_by'] = $order_by;}
	if (!is_null($order)) {$args['order'] = $order;}
	$query = new OST_Query($args);
	return $query->response;
 }
}

// -----------
// User Ledger
// -----------
// 110.1: added ledger endpoint
# /ledger/{id} (GET)
if (!function_exists('ost_kit_user_ledger')) {
 function ost_kit_user_ledger($id, $page_no=null, $order_by=null, $order=null, $limit=null, $filters=null) {
 	global $ost_kit_args; $args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'user_ledger';
	$args['id'] = $id;
	if (!is_null($page_no)) {$args['page_no'] = $page_no;} else {$args['page_no'] = '1';}
	// optional arguments
	if (!is_null($order_by)) {$args['order_by'] = $order_by;}
	if (!is_null($order)) {$args['order'] = $order;}
	if (!is_null($limit)) {$args['limit'] = $limit;}
	if (!is_null($filters)) {$args['filters'] = $filters;}
	$query = new OST_Query($args);
	return $query->response;
 }
}

// ------------
// User Ledgers
// ------------
// 110.1: added multi-ledger function
if (!function_exists('ost_kit_user_ledgers')) {
 function ost_kit_user_ledgers($order_by=null, $order=null) {
 	global $ost_kit_args; $args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'user_ledgers';
	// optional arguments
	if (!is_null($order_by)) {$args['order_by'] = $order_by;}
	if (!is_null($order)) {$args['order'] = $order;}
	$query = new OST_Query($args);
	return $query->response;
 }
}

// ----------------------------
// Process Complete Transaction
// ----------------------------
// calls /transaction-types/execute/ and then /transaction-types/status to complete
# "We have disabled pessimistic concurrency control to ensure that no false positives are returned.
# As a result you must query /transactions/{id} for successful completion of the transaction."
if (!function_exists('ost_kit_transaction_process')) {
 function ost_kit_transaction_process($from_uuid, $to_uuid, $action_id, $amount=null, $commission_percent=null) {

	$result = ost_kit_transaction_execute($from_uuid, $to_uuid, $action_id, $amount, $commission_percent);

	$data = false;
	if ($result && isset($result['success'])) {
		if ($result['success']  && ($result['data']['result_type'] == 'transaction')) {

			$data = $result['data'];

			// TEST: how long to sleep here?
			// or maybe sleep short, retry, sleep long?
			sleep(5);

			$id = $data['transaction']['id'];
			$result = ost_kit_transaction_get($id);

			if ($result && isset($result['success'])) {
				if ($result['success'] && ($result['data']['result_type'] == 'transaction')) {
					$data = $result['data'];
				}
			}

		}
	}
	return $data;
 }
}


// -----------------
// === TRANSFERS ===
// -----------------
// 100.3: added transfer endpoints

// ---------------
// Create Transfer
// ---------------
# /transfers/ (POST)
if (!function_exists('ost_kit_transfer_create')) {
 function ost_kit_transfer_create($to_address, $amount) {
 	global $ost_kit_args; $args = $ost_kit_args;
	$args['endpoint'] = 'transfer_get';
	$args['toaddress'] = $to_address;
	$args['amount'] = $amount;
	$query = new OST_Query($args);
	return $query->response;
 }
}

// ------------
// Get Transfer
// ------------
# /transfers/{id} (GET)
if (!function_exists('ost_kit_transfer_get')) {
 function ost_kit_transfer_get($id) {
 	global $ost_kit_args; $args = $ost_kit_args;
	$args['endpoint'] = 'transfer_get';
	$args['id'] = $id;
	$query = new OST_Query($args);
	return $query->response;
 }
}

// -------------
// Transfer List
// -------------
// /transfers/ (GET)
if (!function_exists('ost_kit_transfer_list')) {
 function ost_kit_transfer_list($page_no=null, $order_by=null, $order=null, $limit=null, $filters=null) {
 	global $ost_kit_args; $args = $ost_kit_args;
	// required arguments
	$args['endpoint'] = 'transfer_list';
	if (!is_null($page_no)) {$args['page_no'] = $page_no;} else {$args['page_no'] = '1';}
	// optional arguments
	if (!is_null($order_by)) {$args['order_by'] = $order_by;}
	if (!is_null($order)) {$args['order'] = $order;}
	if (!is_null($limit)) {$args['limit'] = $limit;}
	if (!is_null($filters)) {$args['filters'] = $filters;}
	$query = new OST_Query($args);
	return $query->response;
 }
}

// ------------------
// List All Transfers
// ------------------
if (!function_exists('ost_kit_transfers_list')) {
 function ost_kit_transfers_list($order_by=null, $order=null) {
 	set_time_limit(120);
 	global $ost_kit_args; $args = $ost_kit_args;
	$args['endpoint'] = 'transfers_list';
	// optional arguments
	if (!is_null($order_by)) {$args['order_by'] = $order_by;}
	if (!is_null($order)) {$args['order'] = $order;}
	$query = new OST_Query($args);
	return $query->response;
 }
}

// ---------------------
// ...and that's a wrap.

# PHP Wrapper for the REST API of OST KIT
	
A PHP wrapper for the REST API of [OST KIT](https://kit.ost.com) which is currently under active development. 

This client implements version 1.1 of the [OST KIT REST API](https://dev.ost.com).

Older versions can be found on the [releases page](https://github.com/majick777/php-ost-kit/releases) and are backwards compatible with the API.

A Branded Token economy must be setup first in order to use the API, see [OST KIT](https://kit.ost.com) for more information.

## Installation

1. Download the ZIP file of the master branch and unzip locally.
2. Then simply include or require the single file `class-ost-kit-api.php` in your project.

## How to use the Client

This Class supports usage of either using defined constants, or supplying arguments while instantiating.

| Constant | Class Arg | Req/Opt | Format | Default Value |
| :------- | :-------- | :------ | :----- | :------------ |
| -        | -         | endpoint | REQUIRED | string | n/a |
| -        | -         | format | Optional | 'json'/'array' | 'array' |
| -        | -         | per_page | Optional | numeric | 10 (used when getting 'all' lists) |
| **API Details** |
| OST_KIT_KEY          | api_key | REQUIRED | string | n/a |
| OST_KIT_SECRET       | api_secret | REQUIRED | string | n/a |
| OST_KIT_URL          | api_url | Optional | URL | 'https://playboxapi.ost.com/v1.1'
| OST_KIT_CHAINID      | chain_id | Optional | number | 1409 (test network blockchain) |
| **Connection** |
| OST_KIT_PORT         | port | Optional | number | false |
| OST_KIT_CONNECTTIME  | connecttime | Optional | number | 30 |
| OST_KIT_TIMEOUT      | timeout | Optional | number | 15 |
| **Debugging** |
| OST_KIT_DEBUG_LOG    | debug_log | Optional | boolean | true |
| OST_KIT_DEBUG_PATH   | debug_path | Optional | string | /class-file-path/api-debug.log |
| OST_KIT_DEBUG_DISPLAY	| debug_display | Optional | boolean | false |

eg. 1: Define the Branded Token economy's `API key` and `API secret` constants, and provide the desired endpoint.
```php
<?php 
define('OST_KIT_KEY', '{your_ost_kit_economy_key}');
define('OST_KIT_SECRET', '{your_ost_kit_economy_secret');

require_once('class-ost-kit-api.php');
$args = array('endpoint' => 'token_get');
$ost = new OST_Query($args);
$response = $ost->response;
```

eg. 2: Instantiate the OST KIT client using your `API key`, `API secret` and desired endpoint arguments.
(Note that if variable arguments are supplied on instantiation, existing constant values will be overridden.)

```php
<?php require_once('class-ost-kit-api.php');
$args = array(
	'api_key'	=> '{your_ost_kit_economy_key}',
	'api_secret'	=> '{your_ost_kit_economy_secret}',
	'endpoint'	=> 'token_get'
);
$ost = new Ost_Query($args);
$response = $ost->response;
```

## Response Data

By default the JSON Response Data from the API is decoded into an array for ease of access.
(If the JSON return format is preferred, just set the class argument `format` to `json`)

Sample structure of different data responses can be found in the file `ost-sample-kit-data.php`
This is a collection of sample responses for all endpoints found the OST KIT API docs pages.
(To output as an array for a more visual reference, load `ost-sample-kit-data.php?display=yes`)


## Class Endpoints

Class Endpoint names map to API calls, taking required and optional arguments.
multi-GET function get ALL relevent page records and combine them to a single data collection.

| Class Endpoint       | Get/Post       | Required Arguments, (optional) |
| :------------------- | :------------- | :----------------------------- |
| token_get            | GET		| n/a |
| user_create	       | POST		| name |	
| user_get             | GET		| user_id |
| user_edit            | POST		| user_id, name |
| user_list            | GET		| (page_no), (airdropped), (order_by), (order), (limit), (filters) |
| users_list           | multi-GET	| (order_by), (order) |
| user_balance         | GET		| user_id |
| airdrop_drop         | POST		| amount, (airdropped), (user_ids) |
| airdrop_status       | GET		| airdrop_id |
| airdrop_list         | GET		| (page_no), (airdropped), (order_by), (order), (limit), (filters) |
| airdrops_list        | multi-GET	| (order_by), (order) |
| action_create        | POST		| name, kind, currency, (arb_amount), (amount), (arb_comm), (comm_percent) |
| action_get           | GET		| action_id |
| action_edit          | POST		| id, (name), (kind), (currency), (arb_amount), (amount), (arb_comm), (comm_percent) |
| action_list          | GET		| (page_no), (airdropped), (order_by), (order), (limit), (filters) |
| actions_list         | multi-GET	| (order_by), (order) |
| transaction_execute  | POST 		| from_user_id, to_user_id, action_id, (amount), (comm_percent) |
| transaction_status   | GET		| transaction_id |
| transaction_list     | GET		| (page_no), (order_by), (order), (limit), (filters) |
| transactions_list    | multi-GET	| (order_by), (order) |
| user_ledger          | GET		| user_id, (page_no), (order_by), (order), (limit), (filters) |
| user_ledgers         | multi-GET	| (order_by), (order) |
| ^transaction_process | POST		| from_user_id, to_user_id, action_id, (amount), (comm_percent) |
| transfer_create      | POST		| to_address, amount |
| transfer_get         | GET		| transfer_id |
| transfer_list        | GET		| (page_no), (order_by), (order), (limit), (filters) |
| transfers_list       | multi-GET	| (order_by), (order) |

^transaction_process runs transaction_execute followed by transaction_status to complete the transaction.


## Shortcut Functions

For convenience, all class method endpoints have matching standalone shortcut (non-class) functions. 
These are simply the endpoint (as in above table) prefixed with `ost_kit_` to make the function name.
Class parameters are set using existing constants or via setting them in the global `$ost_kit_args`.
eg. For the transaction_status endpoint simply use the function `ost_kit_transaction_status`
```php 
global $ost_kit_args;
$ost_kit_args['api_key'] = '{your_ost_kit_economy_key}';
$ost_kit_args['api_secret'] = '{your_ost_kit_economy_secret}';
$response = ost_kit_transaction_status($transaction_id);
```

The required and optional arguments for each shortcut function matches those given in the table above. eg.

```php $response = ost_kit_action_create('Vote', 'user_to_user', 'BT', false, 1, false, 1);```

Any optional argument that is not provided or set to `null` will be ignored and not set. eg.

```php $response = ost_kit_action_edit($action_id, null, null, null, null, null, 2);```


## Roadmap

Some development TODOs and further potential testing points:

* More HTTP Response Error Code Handling
* test balance and ledger list endpoints
* test transfer endpoints and transfer list endpoints
* transaction_execute: check action kind if company UUID is not explicitly provided
* test all optional filters for list queries (user, airdrop, action, transaction, transfer)
* airdrop_drop: whether API can handle both ',' and ', ' delimiting of user IDs
* action_create: check if arbitrary_commission is a required argument for user_to_user actions
* action_create: check decimal value accuracy level for commission_percent
* transaction_execute: how long to sleep between transaction execute and status

## Questions, feature requests and bug reports

If you have questions, have a great idea for the client or ran into issues using this client: 
please report them in the project's [Issues](https://github.com/majick777/php-ost-kit/issues) area.  


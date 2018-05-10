<?php

// ================================
// OST KIT API SAMPLE RESPONSE DATA
// ================================

// ---------------------
// Create User Responses
// ---------------------
# SUCCESS
$sample['create_user_success'] =
'{
  "success": true,
  "data": {
    "result_type": "economy_users",
    "economy_users": [
      {
        "id": "574b456d-5da6-4353-ad7c-9b70893e757b",
        "uuid": "574b456d-5da6-4353-ad7c-9b70893e757b",
        "name": "NAME",
        "total_airdropped_tokens": 0,
        "token_balance": 0
      }
    ],
    "meta": {
      "next_page_payload": {}
    }
  }
}';
# FAILURE
$sample['create_user_failure'] =
'{
  "success": false,
  "err": {
    "code": "companyRestFulApi(s_a_g_1:rJndQJkYG)",
    "msg": "invalid params",
    "error_data": [
      {
        "name": "Only letters, numbers and spaces allowed. (Max 20 characters)"
      }
    ]
  }
}';

// -------------------
// Edit User Responses
// -------------------
# SUCCESS
$sample['edit_user_success'] =
'{
  "success": true,
  "data": {
    "result_type": "economy_users",
    "economy_users": [
      {
        "id": "2f5f6388-fb0e-4812-929f-f37e5ebbfd50",
        "uuid": "2f5f6388-fb0e-4812-929f-f37e5ebbfd50",
        "name": "NAME",
        "total_airdropped_tokens": "0",
        "token_balance": "0"
      }
    ],
    "meta": {
      "next_page_payload": {}
    }
  }
}';
# FAILURE
$sample['edit_user_failure'] =
'{
  "success": false,
  "err": {
    "code": "companyRestFulApi(s_cu_eu_2.1:rJOpl4JtG)",
    "msg": "User not found",
    "error_data": {}
  }
}';

// --------------------
// List Users Responses
// --------------------
# SUCCESS
$sample['list_users_success'] =
'{
  "success": true,
  "data": {
    "result_type": "economy_users",
    "economy_users": [
      {
        "id": "c1e5da9b-787d-4897-aa58-742f2756c71d",
        "name": "User 1",
        "uuid": "c1e5da9b-787d-4897-aa58-742f2756c71d",
        "total_airdropped_tokens": "15",
        "token_balance": "15"
      },
      {
        "id": "461c10ea-2b6c-42e8-9fea-b997995cdf8b",
        "name": "User 25",
        "uuid": "461c10ea-2b6c-42e8-9fea-b997995cdf8b",
        "total_airdropped_tokens": "15",
        "token_balance": "15"
      }
    ],
    "meta": {
      "next_page_payload": {
        "order_by": "creation_time",
        "order": "asc",
        "filter": "all",
        "page_no": 2
      }
    }
  }
}';

// ----------------------
// Airdrop Drop Responses
// ----------------------
# SUCCESS
$sample['airdrop_drop_success'] =
'{
 "success": true,
 "data": {
   "airdrop_uuid": "cbc20092-7326-4517-b851-ec211e3ced7d"
 }
}';
# FAILED
$sample['airdrop_drop_failed'] =
'{
 "success": false,
 "err": {
   "code": "companyRestFulApi(s_am_sa_7:HypBvRPFM)",
   "msg": "Insufficient funds to airdrop users",
   "display_text": "",
   "display_heading": "",
   "error_data": [
     {
       "amount": "Available token amount is insufficient. Please mint more tokens or reduce the amount to complete the process."
     }
   ]
 },
 "data": {
 }
}';

// ------------------------
// Airdrop Status Responses
// ------------------------
# SUCCESS
$sample['airdrop_status_success'] =
'{
 "success": true,
 "data": {
   "airdrop_uuid": "5412c48e-2bec-4224-9305-56be99174f54",
   "current_status": "complete",
   "steps_complete": [
     "users_identified",
     "tokens_transfered",
     "contract_approved",
     "allocation_done"
   ]
 }
}';
# FAIL
$sample['airdrop_status_failed'] =
'"success": false,
 "err": {
   "code": "companyRestFulApi(s_am_gas_1:SJy641uFG)",
   "msg": "Invalid Airdrop Request Id.",
   "display_text": "",
   "display_heading": "",
   "error_data": {
   }
 },
 "data": {
 }
}';


// --------------------------------
// Create Transaction Type Response
// --------------------------------
# SUCCESS
$sample['transaction_type_create'] =
'{
  "success": true,
  "data": {
    "result_type": "transactions",
    "transactions": [
      {
        "id": 10170,
        "client_id": 20373,
        "name": "Upvote",
        "kind": "user_to_user",
        "currency_type": "USD",
        "currency_value": "0.2",
        "commission_percent": "0.1",
        "status": "active",
        "uts": 1520179969832
      }
    ]
  }
}';
# FAILED
$sample['transaction_type_create_failed'] =
'{
  "success": false,
  "err": {
    "code": "companyRestFulApi(s_a_g_1:rJndQJkYG)",
    "msg": "invalid params",
    "error_data": [
      {
        "name": "Transaction-types name \"Upvote\" already present."
      }
    ]
  }
}';

// ------------------------------
// Edit Transaction Type Response
// ------------------------------
# SUCCESS
$sample['transaction_types_edit_success'] =
'{
  "success": true,
  "data": {
    "result_type": "transactions",
    "transactions": [
      {
        "id": "20198",
        "client_id": 1018,
        "name": "Reward",
        "kind": "company_to_user",
        "currency_type": "BT",
        "currency_value": "0.1",
        "commission_percent": "0",
        "uts": 1520876285325
      }
    ]
  }
}';
# FAILURE
# No sample - expect Create Transaction Type Failure


// -------------------------------
// List Transaction Types Response
// -------------------------------
$sample['transaction_type_list_success'] =
'{
  "success": true,
  "data": {
    "client_id": 1018,
    "result_type": "transaction_types",
    "transaction_types": [
      {
        "id": "20216",
        "client_transaction_id": "20216",
        "name": "Upvote",
        "kind": "user_to_user",
        "currency_type": "USD",
        "currency_value": "0.20000",
        "commission_percent": "0.1",
        "status": "active"
      },
      ...
      {
        "id": "20221",
        "client_transaction_id": "20221",
        "name": "Download",
        "kind": "user_to_company",
        "currency_type": "USD",
        "currency_value": "0.10000",
        "commission_percent": "0",
        "status": "active"
      }
    ],
    "meta": {
      "next_page_payload": {}
    },
    "price_points": {
      "OST": {
        "USD": "0.197007"
      }
    },
    "client_tokens": {
      "client_id": 1018,
      "name": "ACME",
      "symbol": "ACM",
      "symbol_icon": "token_icon_6",
      "conversion_factor": "0.21326",
      "token_erc20_address": "0xEa1c45D934d287fec813C74021A5d692278bE5e9",
      "airdrop_contract_addr": "0xaA5460105E39184B5e43a925bf8Da17EED64BE68",
      "simple_stake_contract_addr": "0xf892f80567A97C54b2852316c0F2cA5eb186a0AD"
    }
  }
}';

// -----------------------------
// Execute Transaction Responses
// -----------------------------
$sample['execute_transaction_success'] =
'{
  "success": true,
  "data": {
    "transaction_uuid": "49cc3411-7ab3-4478-8fac-beeab09e3ed2",
    "transaction_hash": null,
    "from_uuid": "1b5039ea-323f-416c-9007-7fe2d068d42d",
    "to_uuid": "286d2cb9-421b-495d-8a82-034d8e2c96e2",
    "transaction_kind": "Download"
  }
}';
# FAILURE
$sample['execute_transaction_failed'] =
'{
  "success": false,
  "err": {
    "code": "companyRestFulApi(s_t_et_7:ByqHgCPKM)",
    "msg": "Invalid From user",
    "error_data": {}
  }
}';

// ----------------------------
// Transaction Status Responses
// ----------------------------
$sample['transaction_status_success'] =
'{
  "success": true,
  "data": {
    "client_tokens": {
      "30117": {
        "id": "30117",
        "client_id": 1124,
        "name": "hkedgrd 3",
        "symbol": "ghpi",
        "symbol_icon": "token_icon_4",
        "conversion_factor": "0.03085",
        "airdrop_contract_addr": "0x3afd9f2273af535c513c2a35f56aF1Fe65E1dBaA",
        "uts": 1520182157543
      }
    },
    "transaction_types": {
      "20334": {
        "id": "20334",
        "name": "Reward",
        "kind": "company_to_user",
        "currency_type": "BT",
        "currency_value": "5",
        "commission_percent": "0.00",
        "status": "active",
        "uts": 1520182157546
      }
    },
    "economy_users": {
      "ae5b9aa6-a45d-439a-bb22-027df78727a1": {
        "id": "ae5b9aa6-a45d-439a-bb22-027df78727a1",
        "uuid": "ae5b9aa6-a45d-439a-bb22-027df78727a1",
        "name": "",
        "kind": "reserve",
        "uts": 1520182157551
      },
      "91af390d-843d-44eb-b554-5ad01f874eba": {
        "id": "91af390d-843d-44eb-b554-5ad01f874eba",
        "uuid": "91af390d-843d-44eb-b554-5ad01f874eba",
        "name": "User 4",
        "kind": "user",
        "uts": 1520182157551
      }
    },
    "result_type": "transactions",
    "transactions": [
      {
        "id": "4bc71630-c131-4b8d-814a-33184d1e6fe1",
        "transaction_uuid": "4bc71630-c131-4b8d-814a-33184d1e6fe1",
        "from_user_id": "ae5b9aa6-a45d-439a-bb22-027df78727a1",
        "to_user_id": "91af390d-843d-44eb-b554-5ad01f874eba",
        "transaction_type_id": "20334",
        "client_token_id": 30117,
        "transaction_hash": "0xe945362504b20eab78b51fdc9e699686eabf3089d40ea57fe552d147ab11f1ba",
        "status": "complete",
        "gas_price": "0x12A05F200",
        "transaction_timestamp": 1520165780,
        "uts": 1520182157540,
        "gas_used": "99515",
        "transaction_fee": "0.000497575",
        "block_number": 213100,
        "bt_transfer_value": "5",
        "bt_commission_amount": "0"
      }
    ]
  }
}';




// maybe display the sample data as arrays
if ( (isset($_GET['display'])) && ($_GET['display'] == 'yes') ) {
	// decode the JSON sample data to an array
	foreach ($sample as $k => $s) {
		$sampledata[$k] = json_decode($s, true);
	}
	// display sample data
	print_r($sampledata);
}



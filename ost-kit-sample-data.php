<?php

// ================================
// OST KIT API SAMPLE RESPONSE DATA
// ================================

// -----
// Token
// -----
# SUCCESS
$sample['token_success'] =
'{
  "success": true,
  "data": {
    "result_type": "token",
    "token": {
      "company_uuid": 1028,
      "name": "Sample Token",
      "symbol": "SCO",
      "symbol_icon": "token_icon_1",
      "conversion_factor": "14.86660",
      "token_erc20_address": "0x546d41730B98a24F2dCfcdbE15637aD1939Bf38b",
      "simple_stake_contract_address": "0x54eF67a54d8b77C091B6599F1A462Ec7b4dFc648",
      "total_supply": "92701.9999941",
      "ost_utility_balance": [
        [
          "198",
          "87.982677084999999996"
        ]
      ]
    },
    "price_points": {
      "OST": {
        "USD": "0.177892"
      }
    }
  }
}';
# FAILURE
$sample['token_failure'] =
'{
  "success": false,
  "err": {
    "code": "UNAUTHORIZED",
    "msg": "We could not authenticate the request. Please review your credentials and authentication method.",
    "error_data": [ ],
    "internal_id": "a_1"
  }
}';


// ---------------------
// Create User Responses
// ---------------------
# SUCCESS
$sample['create_user_success'] =
'{
   "success": true,
   "data": {
      "result_type": "user",
      "user": {
         "id": "d9c97f83-85d5-46b5-a4fb-c73011cbd803",
         "addresses": [
            [
               "1409",
               "0x9352880A2A4c05c41eC1962980Bb1a0bA4176182"
            ]
         ],
         "name": "Alice",
         "airdropped_tokens": 0,
         "token_balance": 0
      }
   }
}';
# FAILURE
$sample['create_user_failure'] =
'{
     "success": false,
     "err": {
        "code": "invalid_request",
        "msg": "At least one parameter is invalid or missing. See err.error_data for more details.",
        "error_data": [
           {
              "parameter": "name",
              "msg": "Must be a minimum of 3 characters, a maximum of 20 characters, and can contain only letters, numbers, and spaces, along with other common sense limitations."
           }
        ],
        "internal_id": "s_a_g_2"
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
      "result_type": "users",
      "users": [
         {
            "id": "3b679b8b-b56d-48e5-bbbe-7397899c8ca6",
            "addresses": [
               [
                  "1409",
                  "0x0d6fE7995175198bd7ad4242fCa4CA8539b509c7"
               ]
            ],
            "name": "Alice",
            "airdropped_tokens": "0",
            "token_balance": "0"
         },
         {
            "id": "d1c0be68-30bd-4b06-af73-7da110dc62da",
            "addresses": [
               [
                  "1409",
                  "0x7b01d73494eb5D2B073eeafB5f8c779CE45853f1"
               ]
            ],
            "name": "Bob",
            "airdropped_tokens": "0",
            "token_balance": "0"
         }
      ],
      "meta": {
         "next_page_payload": {}
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
$sample['airdrop_get_success'] =
'{{
   "success": true,
   "data": {
      "result_type": "airdrop",
      "airdrop": {
         "id": "bc6dc9e1-6e62-4032-8862-6f664d8d7541",
         "current_status": "complete",
         "steps_complete": [
            "users_identified",
            "tokens_transfered",
            "contract_approved",
            "allocation_done"
         ]
      }
   }
}';
# FAIL
$sample['airdrop_get_failed'] =
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

// ---------------------
// Airdrop List Response
// ---------------------
// TODO...

// ----------------------
// Create Action Response
// ----------------------
# SUCCESS
$sample['action_create'] =
'{
   "success": true,
   "data": {
      "result_type": "action",
      "action": {
         "id": "20346",
         "name": "MissionComplete",
         "kind": "user_to_user",
         "currency": "BT",
         "commission_percent": "0.00"
      }
   }
}';
# FAILED
$sample['action_create_failed'] =
'{
   "success": false,
   "err": {
      "code": "invalid_request",
      "msg": "At least one parameter is invalid or missing. See err.error_data for more details.",
      "internal_id" : "companyRestFulApi(401:HJg2HK0A_f)",
      "error_data": [
         {
            "parameter": "name",
            "msg": "An action with that name already exists."
         }
      ]
   }
}';

// --------------------
// Edit Action Response
// --------------------
# SUCCESS
$sample['action_edit_success'] =
'{
   "success": true,
   "data": {
      "result_type": "action",
      "action": {
         "id": 20831,
         "name": "Collect",
         "kind": "company_to_user",
         "currency": "BT",
         "arbitrary_amount": "true",
      }
   }
}';
# FAILURE
# No sample - expect Create Transaction Type Failure


// ---------------------
// List Actions Response
// ---------------------
$sample['action_list_success'] =
'{
   "success": true,
   "data": {
      "result_type": "actions",
      "actions": [
         {
            "id": "20350",
            "name": "TWITTER HANDLE",
            "kind": "user_to_company",
            "currency": "BT",
            "amount": "100",
            "arbitrary_amount": false,
            "commission_percent": null,
            "arbitrary_commission": false
         },
         {
            "id": "20349",
            "name": "TWITTER",
            "kind": "user_to_company",
            "currency": "BT",
            "amount": "100",
            "arbitrary_amount": false,
            "commission_percent": null,
            "arbitrary_commission": false
         },
         {
            "id": "20037",
            "name": "Like",
            "kind": "user_to_user",
            "currency": "USD",
            "amount": "0.01000",
            "arbitrary_amount": false,
            "commission_percent": "12.00",
            "arbitrary_commission": false
         },
         {
            "id": "20023",
            "name": "Purchase",
            "kind": "user_to_user",
            "currency": "USD",
            "amount": "1.00000",
            "arbitrary_amount": false,
            "commission_percent": "1.00",
            "arbitrary_commission": false
         },
         {
            "id": "20021",
            "name": "HEY WORLD",
            "kind": "user_to_company",
            "currency": "BT",
            "amount": "1",
            "arbitrary_amount": false,
            "commission_percent": null,
            "arbitrary_commission": false
         }
      ],
      "meta": {
         "next_page_payload": {}
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
      "result_type": "transaction",
      "transaction": {
         "id": "7a02d0be-802d-45aa-a17b-99d5147427b8",
         "from_user_id": "f6e750a3-3c20-47b5-b3cc-fd72471efa52",
         "to_user_id": "4505bb67-16d8-48bc-8de3-e4313b172e3e",
         "transaction_hash": null,
         "action_id": "20346",
         "timestamp": 1526456925000,
         "status": "processing",
         "gas_price": "5000000000",
         "gas_used": null,
         "transaction_fee": null,
         "block_number": null,
         "amount": null,
         "commission_amount": null
      }
   }
}';
# FAILURE
$sample['execute_transaction_failed'] =
'{
   "success": false,
   "err": {
      "code": "BAD_REQUEST",
      "msg": "At least one parameter is invalid or missing. See \"err.error_data\" array for more details.",
      "error_data": [],
      "internal_id": "cm_ctt_bi_1"
   }
}';

// ----------------------------
// Transaction Status Responses
// ----------------------------
# SUCCESS
$sample['transaction_get_success'] =
'{
   "success": true,
   "data": {
      "result_type": "transaction",
      "transaction": {
         "id": "41138190-80ea-43a9-8ddb-5cb3132a8ba2",
         "from_user_id": "e58ab3d9-16d9-453c-be7f-1e010b5c1b4c",
         "to_user_id": "66e4d7a0-9fd0-4032-8e00-99c128ceffeb",
         "transaction_hash": "0x56019408cbd2b9f21d543edeb79935062bc108413ab0d283fdc3fcef52ad9db9",
         "action_id": "20023",
         "timestamp": 1524827126000,
         "status": "complete",
         "gas_price": "5000000000",
         "gas_used": 105208,
         "transaction_fee": "0.00052604",
         "block_number": "1508693",
         "amount": "4.635750605767636029",
        "commission_amount": "0.04635750605767636"
      }
   }
}';

// ----------------
// Transaction List
// ----------------
# SUCCESS
$sample['transaction_list_success'] =
'{
   "success": true,
   "data": {
      "result_type": "transactions",
      "transactions": [
         {
            "id": "fbd23dc3-edc1-41a0-ab80-90d4462274c1",
            "from_user_id": "72dc76cb-7986-4d27-ab04-1ac8e0eacac1",
            "to_user_id": "e2f14afd-dac1-4657-9cff-32be1f330263",
            "transaction_hash": "0xe7d7d4e5ea00b32e98e694a43ca918076eee19410d4db1288dd4378ffeb4ba5d",
            "action_id": "20346",
            "timestamp": 1524832672000,
            "status": "complete",
            "gas_price": "5000000000",
            "gas_used": 119621,
            "transaction_fee": "0.000598105",
            "block_number": "1511466",
            "amount": "0.463732635165484394",
            "commission_amount": "0"
         },
         {
            "id": "41138190-80ea-43a9-8ddb-5cb3132a8ba2",
            "from_user_id": "e58ab3d9-16d9-453c-be7f-1e010b5c1b4c",
            "to_user_id": "66e4d7a0-9fd0-4032-8e00-99c128ceffeb",
            "transaction_hash": "0x56019408cbd2b9f21d543edeb79935062bc108413ab0d283fdc3fcef52ad9db9",
            "action_id": "20023",
            "timestamp": 1524827126000,
            "status": "complete",
            "gas_price": "5000000000",
            "gas_used": 105208,
            "transaction_fee": "0.00052604",
            "block_number": "1508693",
            "amount": "4.635750605767636029",
            "commission_amount": "0.04635750605767636"
         },
         {
            "id": "1df0a565-b896-431d-940d-676165166d4b",
            "from_user_id": "fe486913-6476-467a-9b45-c0529b5e8221",
            "to_user_id": "aefc0347-876d-4e52-a4d2-d86f7f09d706",
            "transaction_hash": "0x78aaf1bb420578bd59b0c1785709d9c93f30ea0f0dfea1ce530b913c341e3685",
            "action_id": "20022",
            "timestamp": 1524826835000,
            "status": "complete",
            "gas_price": "5000000000",
            "gas_used": 77309,
            "transaction_fee": "0.000386545",
            "block_number": "1508547",
            "amount": "0.1",
            "commission_amount": "0"
         },
         {
            "id": "724f7067-1a6a-4d73-a0d5-7c28195ad49c",
            "from_user_id": "5640b64b-6fc7-4308-baa3-f1424b4ee5b2",
            "to_user_id": "94a70176-bd41-4972-bcac-b397748a9216",
            "transaction_hash": "0x95b2ff10d681b3c16b1d23478b7d847be101f4d8c0526a42a7e2e4e136aa5ea6",
            "action_id": "20037",
            "timestamp": 1524826835000,
            "status": "complete",
            "gas_price": "5000000000",
            "gas_used": 105016,
            "transaction_fee": "0.00052508",
            "block_number": "1508547",
            "amount": "0.046396168436565034",
            "commission_amount": "0.005567540212387804"
         },
         {
            "id": "bf73f7a2-d877-4368-a514-b30c67d02e48",
            "from_user_id": "e58ab3d9-16d9-453c-be7f-1e010b5c1b4c",
            "to_user_id": "94a70176-bd41-4972-bcac-b397748a9216",
            "transaction_hash": "0xa92217e2ddb479635cbbebe51e0fb7d1029f20dddc421bbc896c10b412eaf433",
            "action_id": "20023",
            "timestamp": 1524826823000,
            "status": "complete",
            "gas_price": "5000000000",
            "gas_used": 105208,
            "transaction_fee": "0.00052604",
            "block_number": "1508541",
            "amount": "4.639616843656503414",
            "commission_amount": "0.046396168436565034"
         }
      ],
      "meta": {
         "next_page_payload": {
            "order_by": "id",
            "order": "desc",
            "limit": 5,
            "page_no": 2
         }
      }
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



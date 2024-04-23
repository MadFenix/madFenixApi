require("dotenv").config();

const { Client, AccountId, PrivateKey, TransferTransaction, Hbar, TokenId, NftId} = require("@hashgraph/sdk");

async function sendHbar() {
    let nftId = new NftId(new TokenId(parseInt(token_id_nft_props), parseInt(token_id_nft_realm), parseInt(token_id_nft_number)), parseInt(token_id_nft_identification));
    //Create any transaction
    const sendHbar = await new TransferTransaction()
        .addNftTransfer(nftId, MY_ACCOUNT_ID, RECEIVER_ACCOUNT_ID)
        .setTransactionMemo('Queue: ' + QUEUE_HEDERA_TO_RESOLVE)
        .execute(client);

    let transactionReceipt = await sendHbar.getReceipt(client);

    if (transactionReceipt.status.toString() == 'SUCCESS') {
        console.log('SUCCESS');
    } else {
        console.log('FAIL');
    }

    console.log(QUEUE_HEDERA_TO_RESOLVE);
    console.log(sendHbar.transactionId.toString());

    process.exit();
}

let receiver_account_id = null;
let oro = null;
let queue_hedera_id = null;
let token_id_nft_props = null;
let token_id_nft_realm = null;
let token_id_nft_number = null;
let token_id_nft_identification = null;
process.argv.forEach(function (val, index, array) {
    let argumentArray = val.split('=');
    if (argumentArray.length == 2) {
        if (argumentArray[0] == 'receiver_account_id') {
            receiver_account_id = argumentArray[1];
        }
        if (argumentArray[0] == 'queue_hedera_id') {
            queue_hedera_id = argumentArray[1];
        }
        if (argumentArray[0] == 'token_props') {
            token_id_nft_props = argumentArray[1];
        }
        if (argumentArray[0] == 'token_realm') {
            token_id_nft_realm = argumentArray[1];
        }
        if (argumentArray[0] == 'token_number') {
            token_id_nft_number = argumentArray[1];
        }
        if (argumentArray[0] == 'nft_identification') {
            token_id_nft_identification = argumentArray[1];
        }
    }
});

if (receiver_account_id == null || queue_hedera_id == null || token_id_nft_props == null || token_id_nft_realm == null || token_id_nft_number == null || token_id_nft_identification == null) {
    console.log('Command must contain receiver_account_id, queue_hedera_id, token_id_nft_props, token_id_nft_realm, token_id_nft_number, token_id_nft_identification arguments.');

    process.exit();
}

// Your account ID and private key from string value
const MY_ACCOUNT_ID = AccountId.fromString(process.env.HEDERA_ACCOUNT_ID);
const MY_PRIVATE_KEY = PrivateKey.fromStringED25519(process.env.HEDERA_PRIVATE_KEY);
const RECEIVER_ACCOUNT_ID = AccountId.fromString(receiver_account_id);
const ORO_TO_SEND = parseInt(oro + '0000');
const QUEUE_HEDERA_TO_RESOLVE = queue_hedera_id;

// Pre-configured client for test network (testnet)
const client = Client.forMainnet();

//Set the operator with the account ID and private key
client.setOperator(MY_ACCOUNT_ID, MY_PRIVATE_KEY);

//Set the default maximum transaction fee (in Hbar)
client.setDefaultMaxTransactionFee(new Hbar(10));

//Set the maximum payment for queries (in Hbar)
client.setDefaultMaxQueryPayment(new Hbar(5));

sendHbar();

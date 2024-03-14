require("dotenv").config();

const { Client, AccountId, PrivateKey, TransferTransaction, Hbar, TokenId} = require("@hashgraph/sdk");

async function sendHbar() {
    let plumasTokenId = new TokenId(parseInt(TOKEN_ID_PLUMAS_PROPS), parseInt(TOKEN_ID_PLUMAS_REALM), parseInt(TOKEN_ID_PLUMAS_NUMBER));
    //Create any transaction
    const sendHbar = await new TransferTransaction()
        .addTokenTransfer(plumasTokenId, MY_ACCOUNT_ID, "-" + PLUMAS_TO_SEND) //Sending account
        .addTokenTransfer(plumasTokenId, RECEIVER_ACCOUNT_ID, PLUMAS_TO_SEND) //Receiving account
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
let plumas = null;
let queue_hedera_id = null;
process.argv.forEach(function (val, index, array) {
    let argumentArray = val.split('=');
    if (argumentArray.length == 2) {
        if (argumentArray[0] == 'receiver_account_id') {
            receiver_account_id = argumentArray[1];
        }
        if (argumentArray[0] == 'plumas') {
            plumas = argumentArray[1];
        }
        if (argumentArray[0] == 'queue_hedera_id') {
            queue_hedera_id = argumentArray[1];
        }
    }
});

if (receiver_account_id == null || plumas == null) {
    console.log('Command must contain receiver_account_id and plumas arguments.');

    process.exit();
}

// Your account ID and private key from string value
const TOKEN_ID_PLUMAS_PROPS = process.env.HEDERA_TOKEN_ID_PLUMAS_PROPS;
const TOKEN_ID_PLUMAS_REALM = process.env.HEDERA_TOKEN_ID_PLUMAS_REALM;
const TOKEN_ID_PLUMAS_NUMBER = process.env.HEDERA_TOKEN_ID_PLUMAS_NUMBER;
const MY_ACCOUNT_ID = AccountId.fromString(process.env.HEDERA_ACCOUNT_ID);
const MY_PRIVATE_KEY = PrivateKey.fromStringED25519(process.env.HEDERA_PRIVATE_KEY);
const RECEIVER_ACCOUNT_ID = AccountId.fromString(receiver_account_id);
const PLUMAS_TO_SEND = plumas;
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

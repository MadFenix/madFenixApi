require("dotenv").config();

const { Client, AccountId, PrivateKey, TransferTransaction, Hbar, TokenId} = require("@hashgraph/sdk");

async function sendHbar() {
    let oroTokenId = new TokenId(parseInt(TOKEN_ID_ORO_PROPS), parseInt(TOKEN_ID_ORO_REALM), parseInt(TOKEN_ID_ORO_NUMBER));
    //Create any transaction
    const sendHbar = await new TransferTransaction()
        .addTokenTransfer(oroTokenId, MY_ACCOUNT_ID, "-" + ORO_TO_SEND) //Sending account
        .addTokenTransfer(oroTokenId, RECEIVER_ACCOUNT_ID, ORO_TO_SEND) //Receiving account
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
process.argv.forEach(function (val, index, array) {
    let argumentArray = val.split('=');
    if (argumentArray.length == 2) {
        if (argumentArray[0] == 'receiver_account_id') {
            receiver_account_id = argumentArray[1];
        }
        if (argumentArray[0] == 'oro') {
            oro = argumentArray[1];
        }
        if (argumentArray[0] == 'queue_hedera_id') {
            queue_hedera_id = argumentArray[1];
        }
    }
});

if (receiver_account_id == null || oro == null) {
    console.log('Command must contain receiver_account_id and oro arguments.');

    process.exit();
}

// Your account ID and private key from string value
const TOKEN_ID_ORO_PROPS = process.env.HEDERA_TOKEN_ID_ORO_PROPS;
const TOKEN_ID_ORO_REALM = process.env.HEDERA_TOKEN_ID_ORO_REALM;
const TOKEN_ID_ORO_NUMBER = process.env.HEDERA_TOKEN_ID_ORO_NUMBER;
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

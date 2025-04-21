<?php


namespace App\Modules\Store\Infrastructure\Controller;

use App\Http\Controllers\Controller;
use App\Modules\Blockchain\Block\Domain\BlockchainHistorical;
use App\Modules\Blockchain\Block\Domain\NftIdentification;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Game\Season\Domain\SeasonRewardRedeemed;
use App\Modules\Store\Domain\Product;
use App\Modules\Store\Domain\ProductOrder;
use App\Modules\User\Domain\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use Stripe\Webhook;

class Api extends Controller
{
    protected function saveProductOrder($productId, $user)
    {
        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }
        $product = Product::find($productId);
        if (!$product) {
            return response()->json('Producto no encontrado.', 404);
        }
        if (empty($product->active)) {
            return response()->json('Producto no activo.', 403);
        }

        if (!empty($product->one_time_purchase) && $product->one_time_purchase == 1) {
            $productOrderOwner = ProductOrder::where('user_id', '=', $user->id)->where('product_id', '=', $product->id)->get();
            if ($productOrderOwner->count() > 0) {
                return response()->json('Ya has comprado una vez este producto.', 404);
            }
        }

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $profile->user_id;
        $memoBase = '';

        if ($product->price_oro > 0) {
            if ($profile->oro < $product->price_oro) {
                return response()->json('No tienes suficiente oro para comprar este producto.', 400);
            }

            $profile->oro -= $product->price_oro;
            $profile->save();

            $newBlockchainHistorical->piezas_de_oro_ft = -$product->price_oro;

            if ($profile->referred_code_from) {
                $profileWithSameReferredCode = Profile::where('referred_code', '=', $profile->referred_code_from)->first();
                if ($profileWithSameReferredCode) {
                    $oroToReferred = (int) ceil($product->price_oro / 10);

                    if ($oroToReferred > 1) {
                        $profileWithSameReferredCode->oro += $oroToReferred;
                        $profileSaved2 = $profileWithSameReferredCode->save();

                        $newBlockchainHistorical2 = new BlockchainHistorical();
                        $newBlockchainHistorical2->user_id = $profileWithSameReferredCode->user_id;
                        $newBlockchainHistorical2->piezas_de_oro_ft = $oroToReferred;
                        $newBlockchainHistorical2->memo = "Referred buy. User " . $profile->user_id;
                        $blockchainHistoricalSaved2 = $newBlockchainHistorical2->save();
                    }
                }
            }
        }

        if ($product->price_plumas > 0) {
            if ($profile->plumas < $product->price_plumas) {
                return response()->json('No tienes suficientes plumas para comprar este producto.', 400);
            }

            $profile->plumas -= $product->price_plumas;
            $profile->save();

            $newBlockchainHistorical->plumas = -$product->price_plumas;
        }

        if ($product->price_fiat > 0) {
            $memoBase = '. Paid ' . $product->price_fiat;
        }

        $productOrder = new ProductOrder();
        $productOrder->product_id = $productId;
        $productOrder->user_id = $user->id;
        $productOrderSaved = $productOrder->save();

        $newBlockchainHistorical->memo = "Order " . $productOrder->id . $memoBase;
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();

        if (!empty($product->one_time_purchase_global) && $productOrderSaved) {
            $product->active = 0;
            $product->save();
        }

        return ($productOrderSaved && $blockchainHistoricalSaved)? $productOrder : false;
    }

    public function addProductToOrder(Request $request) {
        $data = $request->validate(['product_id' => 'required']);

        /** @var User $user */
        $user = auth()->user();

        return ($productOrder = $this->saveProductOrder($data['product_id'], $user))
            ? response()->json($productOrder->toArray())
            : response()->json('Error al guardar el pedido de producto.', 500);
    }

    protected function getProductOrderIdFromStripePaid($sig_header)
    {
        // The library needs to be configured with your account's secret key.
        // Ensure the key is kept out of any version control system you might be using.
        $stripe = new StripeClient(env('STRIPE_ACCOUNT_SECRET'));

        // This is your Stripe CLI webhook secret for testing your endpoint locally.
        $endpoint_secret = env('STRIPE_CLIENT_WEBHOOK_SECRET');

        $payload = @file_get_contents('php://input');

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json('Parámetros inválidaos.', 404);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json('Signatura inválida.', 404);
        }

        // Handle the event
        switch ($event->type) {
            case 'invoice.paid':
                $invoice = $event->data->object;
                $user = User::where('email', '=', $invoice->customer_email)->first();
                if (!$user) {
                    return response()->json('Usuario desconocido.', 404);
                }
                $priceFiat = substr($invoice->total, 0, -2) . ',' . substr($invoice->total, -2);
                $product = Product::where('price_fiat', '=', $priceFiat)->first();
                if (!$product) {
                    return response()->json('Producto desconocido.', 404);
                }
                $productOrder = $this->saveProductOrder($product->id, $user);
                if (!$productOrder) {
                    return response()->json('El pedido no se ha podido crear.', 500);
                }
                return (int) $productOrder->id;
            default:
                return response()->json('Evento desconocido.', 404);
        }

        return false;
    }

    public function validateProductOrder(Request $request)
    {
        $sig_header = (isset($_SERVER['HTTP_STRIPE_SIGNATURE']))? $_SERVER['HTTP_STRIPE_SIGNATURE'] : null;
        if ($sig_header && !empty($sig_header)) {
            $productOrderId = $this->getProductOrderIdFromStripePaid($sig_header);

            if (gettype($productOrderId) != 'integer') {
                return response()->json('Pedido de producto no encontrado.', 404);
            }
        } else {
            $data = $request->validate(['product_order_id' => 'required']);
            $productOrderId = $data['product_order_id'];
        }

        $productOrder = ProductOrder::where('id', '=', $productOrderId)
            ->whereNull('payment_validated')
            ->first();
        if (!$productOrder) {
            return response()->json('Pedido de producto no encontrado.', 404);
        }
        $profile = Profile::where('user_id', '=', $productOrder->user_id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }
        $products = Product::where('id', '=', $productOrder->product_id)
            ->orWhere('product_parent_id', '=', $productOrder->product_id)
            ->get();
        if (!$products || $products->count() < 1) {
            return response()->json('Productos del pedido no encontrados.', 404);
        }

        $productOrder->payment_validated = true;
        $productOrderSaved = $productOrder->save();

        if ($productOrderSaved) {
            foreach ($products as $product) {
                if ($product->oro > 0) {
                    $profile->oro += $product->oro;
                    $profileSaved = $profile->save();

                    $newBlockchainHistorical = new BlockchainHistorical();
                    $newBlockchainHistorical->user_id = $profile->user_id;
                    $newBlockchainHistorical->piezas_de_oro_ft = $product->oro;
                    $newBlockchainHistorical->memo = "Order " . $productOrder->id . ", redeemed oro " . $product->oro;
                    $blockchainHistoricalSaved = $newBlockchainHistorical->save();

                    if (!($profileSaved && $blockchainHistoricalSaved)) {
                        return response()->json('Error al canjear el pedido.', 500);
                    }
                }

                if ($product->plumas > 0) {
                    $profile->plumas += $product->plumas;
                    $profileSaved = $profile->save();

                    $newBlockchainHistorical = new BlockchainHistorical();
                    $newBlockchainHistorical->user_id = $profile->user_id;
                    $newBlockchainHistorical->plumas = $product->plumas;
                    $newBlockchainHistorical->memo = "Order " . $productOrder->id . ", redeemed plumas " . $product->plumas;
                    $blockchainHistoricalSaved = $newBlockchainHistorical->save();

                    if (!($profileSaved && $blockchainHistoricalSaved)) {
                        return response()->json('Error al canjear el pedido.', 500);
                    }
                }

                if ($product->nft_id > 0) {
                    $nftIdentificationToAssociate = NftIdentification::where('nft_id', '=', $product->nft_id)
                        ->whereNull('user_id')
                        ->where('madfenix_ownership', '=', '1');
                    if (!empty($product->rarity)) {
                        $rarities = explode(',', $product->rarity);
                        $nftIdentificationToAssociate = $nftIdentificationToAssociate
                            ->where(function ($query) use($rarities) {
                                $query->where('rarity', '=', $rarities[0]);
                                foreach ($rarities as $key => $rarity) {
                                    if (empty($key)) {
                                        continue;
                                    }
                                    $query->orWhere('rarity', '=', $rarities[0]);
                                }
                            });
                    }
                    $nftIdentificationToAssociate = $nftIdentificationToAssociate
                        ->first();
                    if (!$nftIdentificationToAssociate) {
                        $product->active = 0;
                        $product->save();

                        // return paid tokens
                        $newBlockchainHistorical = new BlockchainHistorical();
                        $newBlockchainHistorical->user_id = $profile->user_id;
                        $memoBase = '';

                        if ($product->price_oro > 0) {
                            $profile->oro += $product->price_oro;
                            $profile->save();

                            $newBlockchainHistorical->piezas_de_oro_ft = $product->price_oro;

                            if ($profile->referred_code_from) {
                                $profileWithSameReferredCode = Profile::where('referred_code', '=', $profile->referred_code_from)->first();
                                if ($profileWithSameReferredCode) {
                                    $oroToReferred = (int) ceil($product->price_oro / 10);

                                    if ($oroToReferred > 1) {
                                        $profileWithSameReferredCode->oro -= $oroToReferred;
                                        $profileWithSameReferredCode->save();

                                        $newBlockchainHistorical2 = new BlockchainHistorical();
                                        $newBlockchainHistorical2->user_id = $profileWithSameReferredCode->user_id;
                                        $newBlockchainHistorical2->piezas_de_oro_ft = -$oroToReferred;
                                        $newBlockchainHistorical2->memo = "Refund Referred buy. User " . $profile->user_id;
                                        $newBlockchainHistorical2->save();
                                    }
                                }
                            }
                        }

                        if ($product->price_plumas > 0) {
                            $profile->plumas += $product->price_plumas;
                            $profile->save();

                            $newBlockchainHistorical->plumas = $product->price_plumas;
                        }

                        $newBlockchainHistorical->memo = "Refund Order " . $productOrder->id . $memoBase;
                        $newBlockchainHistorical->save();
                        // end return paid tokens

                        return response()->json('No se ha encontrado el activo digital del pedido.', 404);
                    }
                    $nftIdentificationToAssociate->madfenix_ownership = false;
                    $nftIdentificationToAssociate->user_id = $profile->user_id;
                    $nftIdentificationToAssociateSaved = $nftIdentificationToAssociate->save();

                    $newBlockchainHistorical = new BlockchainHistorical();
                    $newBlockchainHistorical->user_id = $profile->user_id;
                    $newBlockchainHistorical->nft_identification_id = $nftIdentificationToAssociate->id;
                    $newBlockchainHistorical->memo = "Order " . $productOrder->id . ", redeemed nft " . $nftIdentificationToAssociate->id;
                    $blockchainHistoricalSaved = $newBlockchainHistorical->save();

                    if (!($nftIdentificationToAssociateSaved && $blockchainHistoricalSaved)) {
                        return response()->json('Error al canjear el pedido.', 500);
                    }
                }

                if ($product->custom == 'Pase de temporada premium') {
                    $profile->season_premium = 1;
                    $profileSaved = $profile->save();

                    $newBlockchainHistorical = new BlockchainHistorical();
                    $newBlockchainHistorical->user_id = $profile->user_id;
                    $newBlockchainHistorical->memo = "Order " . $productOrder->id . ", redeemed pase de temporada premium.";
                    $blockchainHistoricalSaved = $newBlockchainHistorical->save();

                    if (!($profileSaved && $blockchainHistoricalSaved)) {
                        return response()->json('Error al canjear el pedido.', 500);
                    }
                }
            }
        }

        return $productOrderSaved
            ? response()->json('Pedido de producto guardado.')
            : response()->json('Error al guardar el perfil.', 500);
    }

    public function getStoreDetails()
    {
        $products = Product::where('active', '=', '1')->orderBy('order', 'asc')->get();

        $returnStore = new \stdClass();
        $returnStore->products = [];
        foreach ($products as $product) {
            $newProduct = (object) $product->toArray();
            $returnStore->products[] = $newProduct;
        }

        return response()->json($returnStore);
    }

    public function getLastProductOrders()
    {
        /** @var User $user */
        $user = auth()->user();
        if ($user->id != 2) {
            return response()->json('Debes ser el administrador para acceder a aquí.', 403);
        }

        $productOrders = ProductOrder::orderBy('id', 'desc')
            ->limit(20)
            ->get();

        $response = [];
        foreach ($productOrders as $productOrder) {
            $nowMinus30sec = Carbon::now();
            $nowMinus30sec->subtract('1 minute');
            if ($productOrder->created_at > $nowMinus30sec) {
                $userProfile = Profile::where('user_id', '=', $productOrder->user->id)->first();
                $response[] = '<div class="flex items-center space-x-3 justify-center" style="max-height: 200px"><div><img src="' . $userProfile->avatar . '" class="border-2 border-madfenix-gris" style="max-height: 200px" /></div><div class="grow">' . $productOrder->product->name . ' por <span style="color: #FC9208">' . $productOrder->user->name . '</span>.</div></div>';
            }
        }

        return response()->json($response);
    }
}

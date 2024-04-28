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
use Illuminate\Http\Request;

class Api extends Controller
{
    public function addProductToOrder(Request $request) {
        $data = $request->validate(['product_id' => 'required']);

        /** @var User $user */
        $user = auth()->user();

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }
        $product = Product::find($data['product_id']);
        if (!$product) {
            return response()->json('Producto no encontrado.', 404);
        }
        if ($product->price_oro > 0) {
            if ($profile->oro < $product->price_oro) {
                return response()->json('No tienes suficiente oro para comprar este producto.', 400);
            }

            $profile->oro -= $product->price_oro;
            $profile->save();
        }

        $productOrder = new ProductOrder();
        $productOrder->product_id = $data['product_id'];
        $productOrder->user_id = $user->id;
        $productOrderSaved = $productOrder->save();

        return $productOrderSaved
            ? response()->json('Pedido de producto guardado.')
            : response()->json('Error al guardar el pedido de producto.', 500);
    }

    public function validateProductOrder(Request $request)
    {
        $data = $request->validate(['product_order_id' => 'required']);

        $productOrder = ProductOrder::where('id', '=', $data['product_order_id'])
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
                    $newBlockchainHistorical->memo = "Pedido " . $productOrder->id . ", redeemed oro " . $product->oro;
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
                    $newBlockchainHistorical->memo = "Pedido " . $productOrder->id . ", redeemed plumas " . $product->plumas;
                    $blockchainHistoricalSaved = $newBlockchainHistorical->save();

                    if (!($profileSaved && $blockchainHistoricalSaved)) {
                        return response()->json('Error al canjear el pedido.', 500);
                    }
                }

                if ($product->nft_id > 0) {
                    $nftIdentificationToAssociate = NftIdentification::where('nft_id', '=', $product->nft_id)
                        ->whereNull('user_id')
                        ->where('madfenix_ownership', '=', '1')
                        ->first();
                    if (!$nftIdentificationToAssociate) {
                        return response()->json('No se ha encontrado el activo digital del pedido.', 404);
                    }
                    $nftIdentificationToAssociate->madfenix_ownership = false;
                    $nftIdentificationToAssociate->user_id = $profile->user_id;
                    $nftIdentificationToAssociateSaved = $nftIdentificationToAssociate->save();

                    $newBlockchainHistorical = new BlockchainHistorical();
                    $newBlockchainHistorical->user_id = $profile->user_id;
                    $newBlockchainHistorical->nft_identification_id = $nftIdentificationToAssociate->id;
                    $newBlockchainHistorical->memo = "Pedido " . $productOrder->id . ", redeemed nft " . $nftIdentificationToAssociate->id;
                    $blockchainHistoricalSaved = $newBlockchainHistorical->save();

                    if (!($nftIdentificationToAssociateSaved && $blockchainHistoricalSaved)) {
                        return response()->json('Error al canjear el pedido.', 500);
                    }
                }

                if ($product->custom = 'Pase de temporada premium') {
                    $profile->season_premium = 1;
                    $profileSaved = $profile->save();

                    $newBlockchainHistorical = new BlockchainHistorical();
                    $newBlockchainHistorical->user_id = $profile->user_id;
                    $newBlockchainHistorical->memo = "Pedido " . $productOrder->id . ", redeemed pase de temporada premium.";
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
        $products = Product::where('active', '=', '1')->get();

        $returnStore = new \stdClass();
        $returnStore->products = [];
        foreach ($products as $product) {
            $newProduct = (object) $product->toArray();
            $returnStore->products[] = $newProduct;
        }

        return response()->json($returnStore);
    }
}

<?php

namespace App\Actions\App;

use App\Models\User;
use App\SallaAuthService;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Just in case you want to use in-house auth.
 * You can manage receive the access token from this webhook
 *
 * @example
 * {
 *    "event": "app.store.authorize",
 *    "merchant": 1234509876,
 *    "created_at": "2021-10-07 12:31:25",
 *    "data": {
 *      "access_token": "KGsnBcNNkR2AgHnrd0U9lCIjrUiukF_-Fb8OjRiEcog.NuZv_mJaB46jA2OHaxxxx",
 *      "expires": 1634819484,
 *      "refresh_token": "fWcceFWF9eFH4yPVOCaYHy-UolnU7iJNDH-dnZwakUE.bpSNQCNjbNg6hTxxxx",
 *      "scope": "settings.read branches.read offline_access",
 *      "token_type": "bearer"
 *    }
 *  }
 */
class StoreAuthorize
{
    use AsAction;

    public function handle($event)
    {
        /** @var SallaAuthService $service */
        $service = app()->make(SallaAuthService::class);

        /*
         * Lets get the store details using the access token in the event
         */
        $storeDetails = $service->setAccessToken($event->data)->getStoreDetail();

        /**
         * We can now create a user base in the details
         */
        $user = User::query()->firstOrCreate([
            'email' => $storeDetails->getEmail(),
        ], [
            'name'     => $storeDetails->getStoreOwnerName(),
            'password' => \Illuminate\Support\Facades\Hash::make(Str::random())
        ]);

        /**
         * Lets save the tokens for used it later.
         */
        $user->token()->create([
            'merchant'      => $storeDetails->getStoreId(),
            'access_token'  => $event->data['access_token'],
            'expires_in'    => $event->data['expires'],
            'refresh_token' => $event->data['refresh_token']
        ]);

        // You can also save the store details from $storeDetails object
        // Also you can here call any api using the access token to prepare the service for the merchant.
    }
}

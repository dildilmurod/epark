<?php

namespace App\Services;

use Exception;
use Coderello\SocialGrant\Resolvers\SocialUserResolverInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Facades\Socialite;

class SocialUserResolver implements SocialUserResolverInterface
{

    /**
     * Resolve user by provider credentials.
     *
     * @param string $provider
     * @param string $accessToken
     *
     * @return Authenticatable|null
     */
    public function resolveUserByProviderCredentials(string $provider, string $accessToken): ?Authenticatable
    {
        $providerUser = null;

        try {
            if($provider == 'facebook') {
                $providerUser = Socialite::driver($provider)->with([
                    'email', 'public_profile'])->userFromToken($accessToken);
            }
            else{
                $providerUser = Socialite::driver($provider)->userFromToken($accessToken);
            }
        } catch (Exception $exception) {

        }

        if ($providerUser) {
            return (new SocialAccountsService())->findOrCreate($providerUser, $provider);
        }

        return null;
    }
}

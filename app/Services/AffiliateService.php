<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {
    }

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        if ($this->emailInUseAsMerchant($email)) {
            throw new AffiliateCreateException('The email {$email} is already in use as a merchant');
        }
        if ($this->emailInUseAsAffiliate($email)) {
            throw new AffiliateCreateException('The email {$email} is already in use as affiliate');
        }
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => '12345678',
            'type' => User::TYPE_AFFILIATE
        ]);
        if ($user) {
            $apiService = app(ApiService::class);
            $discountCode = $apiService->createDiscountCode($merchant);
            $affiliate = Affiliate::create([
                'user_id' => $user->id,
                'merchant_id' => $merchant->id,
                'commission_rate' => $commissionRate,
                'discount_code' => $discountCode['code'],
            ]);
            if ($affiliate) {
                Mail::to($email)->send(new AffiliateCreated($affiliate));
            }
            return $affiliate;
        }
    }

    public function emailInUseAsMerchant($email)
    {
        return Merchant::whereHas('user', function ($query) use ($email) {
            $query->where('email', $email);
        })->exists();
    }

    public function emailInUseAsAffiliate($email)
    {
        return Affiliate::whereHas('user', function ($query) use ($email) {
            $query->where('email', $email);
        })->exists();
    }
}

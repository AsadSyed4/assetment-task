<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Mail\PayoutMail;
use App\Models\Merchant;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * You don't need to do anything here. This is just to help
 */
class ApiService
{
    /**
     * Create a new discount code for an affiliate
     *
     * @param Merchant $merchant
     *
     * @return array{id: int, code: string}
     */
    public function createDiscountCode(Merchant $merchant): array
    {
        return [
            'id' => rand(0, 100000),
            'code' => Str::uuid()
        ];
    }

    /**
     * Send a payout to an email
     *
     * @param  string $email
     * @param  float $amount
     * @return void
     * @throws RuntimeException
     */
    public function sendPayout(string $email, float $amount)
    {
        try {
            // Your code to call the API and send the payout to the email address
            // Assuming the API call was successful
            Mail::to($email)->send(new PayoutMail($amount));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

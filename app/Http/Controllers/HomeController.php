<?php

namespace App\Http\Controllers;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    // Annual interest rate
    const INTEREST_RATE = 0.079;
    // Max credit limit per user
    const CREDIT_LIMIT = 80000;
    /**
     * Index function for home page load
     * 
     * @param $request Request;
     * 
     */
	public function index(Request $request)
    {
        $credits_table = self::getCredits();
        $active = [];

        if (empty($credits_table) || count(json_decode($credits_table, true)) == 0) {
            // no entries - send dummy data for better visual
            $credits_table = json_encode([[
                'ID' => "...",
                'credited_user' => "...",
                'remaining_amount' => "...",
                'terms' => "...",
                'mounthly_payment' => "...",
            ]]);
            $arr = [];
            Storage::put('all_credits.json', json_encode($arr));
		    return view('home', ['credits_table' => $credits_table, 'dropdown_active' => $active]);
        }

        $credits = json_decode(self::getCredits(), true);
        foreach($credits as $credit){
            if ($credit['remaining_amount'] > 0) {
                $active[] = $credit['ID'];
            }
        }
        
		return view('home', ['credits_table' => $credits_table, 'dropdown_active' => $active]);
	}

    /**
     * Load credits table
     */
    public static function getCredits()
    {
        $all_credits = Storage::get('all_credits.json');

        return $all_credits;
    }

    /**
     * Substract payment amount to a given credit ID
     * 
     */
    public function makeCreditPayment(Request $request)
    {
        $is_more = false;
        $last_payment = 0;

        $id = $request->input('id');
        $amount = $request->input('payment_amount');

        $credits = json_decode(self::getCredits(), true);
        foreach($credits as &$credit){
            if ($credit['ID'] == $id) {
                if ($credit['remaining_amount'] <= $amount) {
                    $last_payment = $credit['remaining_amount'];
                    $credit['remaining_amount'] = 0;
                    $is_more = true;
                } else {
                    $credit['remaining_amount'] = $credit['remaining_amount'] - $amount;
                }
            }
        }

        if ($is_more) {
            echo 'Payment amount was more! Payed ' . $last_payment . '.';
        } else {
            echo 'Payment succsessful!';
        }

        Storage::put('all_credits.json', json_encode($credits));
    }

    /**
     * Save new credit entry in the storage
     * 
     */
    public function createNewCreditEntry(Request $request): void
    {
        $name = $request->input('name');
        $amount = $request->input('amount');
        $terms = $request->input('terms');

        $is_valid = self::validateCredit($name, $amount, $terms); 

        if ($is_valid) {
            echo 'New entry saved!';
        } else {
            echo 'New entry NOT saved! Invalid data entered(check user limit, terms, total amount)';
        }
    }

    /**
     * Validate credit parameters
     * 
     * @param string $name     Credituser name
     * @param mixed  $amount   Credit amount in BGN
     * @param int    $terms    Credit terms to be paid back
     * 
     * @return bool
     */
    public static function validateCredit(string $name, $amount, int $terms) : bool 
    {
        $is_valid = false;
        $user_total_sum = $amount;

        $credits = json_decode(self::getCredits(), true);
        foreach($credits as $credit){
            if ($credit['credited_user'] === $name) {
                $user_total_sum += $credit['remaining_amount'];
            }
        }

        if (($terms <= 120 && $terms >= 3) && ($amount <= self::CREDIT_LIMIT && $user_total_sum <= self::CREDIT_LIMIT)) {
            self::saveCredit($name, $amount, $terms);
            return true;
        }

        return $is_valid;
    }

    /**
     * save a new credit entry
     *
     * @param string $name     Credituser name
     * @param mixed  $amount   Credit amount in BGN
     * @param int    $terms    Credit terms to be paid back
     */
    private static function saveCredit(string $name, $amount, int $terms): void
    {
        $current_data = Storage::get('all_credits.json');
        $arr = json_decode($current_data, true);
        $remaining_amount = self::calculateTotalAmountToPay($amount, $terms);
        $mounthly_payment = self::calcMonthlyPayment($remaining_amount, $terms);

        $new = [
            'ID' => self::getNewID(),
            'credited_user' => $name,
            'remaining_amount' => round($remaining_amount, 2),
            'terms' => $terms,
            'mounthly_payment' => round($mounthly_payment, 2),
        ];

        $arr[] = $new;
        $final_data = json_encode($arr);

        Storage::put('all_credits.json', $final_data);
    }

    /**
     * generates new item ID
     * 
     * @return int
     */
    public static function getNewID(): int
    {
        $current_data = Storage::get('all_credits.json');
        $arr = json_decode($current_data, true);
        $id_number = empty($arr) ? 1000001 : end($arr)['ID'] + 1;

        return $id_number;
    }

    /**
     * calculate total credit amount with interest rate applied
     * 
     * @param mixed $amount   Credit amount
     * @param int   $terms    Credit terms
     * 
     * @return float
     */
    public static function calculateTotalAmountToPay($amount, int $terms) : float
    {
        $mounthly_interest_rate = ($terms < 12) ? self::INTEREST_RATE / $terms : self::INTEREST_RATE / 12;

        $total_interest_amount = $amount * $mounthly_interest_rate * $terms;

        return $amount + $total_interest_amount;
    }

    /**
     * Calculate montly payment to a specific credit user
     * 
     * @param mixed $amount   Credit amount
     * @param int   $terms    Credit terms
     * 
     * @return float
     */
    public static function calcMonthlyPayment($amount, int $terms) : float 
    {
        return $amount / $terms;
    }
}
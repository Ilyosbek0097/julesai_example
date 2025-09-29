<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BankCashController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function getData(Request $request)
    {
        $validated = $request->validate([
            'Branch ID' => 'required',
            'Oper day' => 'required',
        ]);

        // Bu yerga o'z logikangizni qo'shishingiz mumkin.
        // Hozircha test uchun true/false qiymatini o'zgartirib turishingiz mumkin.
        $isSuccessful = true;

        if ($isSuccessful) {
            return response()->json([
                'status' => true,
                'message' => 'uspeshno',
            ]);
        } else {
            // Muvaffaqiyatsiz holat uchun javob
            return response()->json([
                'status' => false,
                'message' => 'Ne uspeshno',
                'errors' => [ // Xatoliklar ro'yxati uchun alohida maydon
                    '1. FIO',
                    '2. FIO',
                ]
            ], 400); // Xatolik holati uchun 400 status kodini qaytaramiz
        }
    }
}
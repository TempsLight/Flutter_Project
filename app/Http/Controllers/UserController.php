<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function createUser(Request $request)
    {
        //validation
        $rules = [
            'name' => 'required|string',
            'email' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
            'phone_number' => 'required|string|unique:users',
            'age' => 'required|integer',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        //create new users
        $users = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'age' => $request->age,
        ]);

        $token = $users->CreateToken('Personal Access Token')->plainTextToken;
        $response = ['user' => $users, 'token' => $token];
        return response()->json($response, 200);
    }

    public function login(Request $request)
    {
        // validate inputs
        $rules = [
            'email' => 'required',
            'password' => 'required|string'
        ];
        $request->validate($rules);
        // find user id in the table
        $user = User::where('email', $request->email)->first();
        // if user email found and password matched 
        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken('Personal Access Token')->plainTextToken;
            $response = ['user' => $user, 'token' => $token];
            return response()->json($response, 200);
        }
        $response = ['message' => 'Incorrect Email or Password'];
        return response()->json($response, 400);
    }


    public function index()
    {
        $user = User::all();
        $data = [
            'status' => 200,
            'user' => $user
        ];
        return response()->json($data, 200);
    }

    public function getAuthenticatedUser(Request $request)
    {
        $user = $request->user();

        if ($user) {
            return response()->json(['user' => $user], 200);
        } else {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
    }

    public function editUser(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            $data = [
                "status" => 422,
                "message" => $validator->messages()
            ];
            return response()->json($data, 422);
        } else {
            $user = User::find($id);

            if ($user === null) {
                return response()->json(['message' => 'User not found'], 404);
            }
    
            $user->name = $request->name;
            $user->email = $request->email;
            $user->save();

            return response()->json(['message' => 'Data Updated Successfully'], 200);
        }
    }



    public function deleteUser($id)
    {
        $user = User::find($id);

        if ($user) {
            $user->is_deleted = 1;
            $user->save();

            $data = [
                'status' => 200,
                'message' => 'User deleted successfully'
            ];
        } else {
            $data = [
                'status' => 404,
                'message' => 'User not found'
            ];
        }

        return response()->json($data, $data['status']);
    }

    public function logout(Request $request)
    {
        // Get user who requested the logout
        $user = $request->user();

        // Revoke all tokens...
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

        // Return response
        return response()->json('Logged out successfully', 200);
    }

    public function depositMoney(Request $request)
    {
        $phone_number = $request->phone_number;
        $amount = $request->amount;

        $user = User::where('phone_number', $phone_number)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        DB::beginTransaction();

        try {
            $user->balance += $amount;
            $user->save();

            $transaction = new Transaction;
            $transaction->phone_number = $phone_number;
            $transaction->amount = $amount;
            $transaction->type = 'deposit';
            $transaction->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            // Return the exception message
            return response()->json(['message' => 'An error occurred while depositing money', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Money deposited successfully'], 200);
    }

    public function sendMoney(Request $request)
    {
        // Validate the request data
        $request->validate([
            'phone_number' => 'required|exists:users,phone_number',
            'amount' => 'required|numeric'
        ]);

        // Find the sender and receiver in the database
        $sender = $request->user();

        // Check if the sender is authenticated
        if (!$sender) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $receiver = User::where('phone_number', $request->phone_number)->first();

        // Check if the receiver exists
        if (!$receiver) {
            return response()->json(['message' => 'Receiver not found'], 404);
        }

        // Check if the sender has enough balance
        if ($sender->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Perform the transaction
            $sender->balance -= $request->amount;
            $sender->save();

            $receiver->balance += $request->amount;
            $receiver->save();

            // Create a new transaction
            $transaction = new Transaction([
                'phone_number' => $sender->phone_number,
                'receiver_phone_number' => $request->phone_number, // Set the receiver_phone_number
                'amount' => $request->amount,
                'type' => 'transfer',
            ]);

            $transaction->save();

            // Commit the transaction
            DB::commit();

            return response()->json(['message' => 'Money sent successfully'], 200);
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollback();

            // Return the exception message
            return response()->json(['message' => 'Transaction failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function getTransactionLogs()
    {
        $user = auth()->user();

        // Get all transactions of the user
        $transactions = Transaction::where('phone_number', $user->phone_number)
            ->with('receiver')
            ->where('isDeleted', 0)
            ->get();

        // Get the transactions where the user is the receiver
        $receivedTransactions = $transactions->where('type', 'transfer');

        // Get the transactions where the user has deposited money
        $depositedTransactions = $transactions->where('type', 'deposit');

        // Get the transactions where the user has transferred money
        $transferredTransactions = $transactions->where('type', 'transfer');

        // Calculate the total received, deposited, and transferred money
        $receivedMoney = $receivedTransactions->sum('amount');
        $depositedMoney = $depositedTransactions->sum('amount');
        $transferredMoney = $transferredTransactions->sum('amount');

        return response()->json([
            'transactions' => $transactions,
            'receivedMoney' => $receivedMoney,
            'depositedMoney' => $depositedMoney,
            'transferredMoney' => $transferredMoney
        ], 200);
    }


    public function deleteTransaction($id)
    {
        // Find the transaction
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // Update the isDeleted column
        $transaction->isDeleted = 1;
        $transaction->save();

        return response()->json(['message' => 'Transaction deleted successfully']);
    }
}

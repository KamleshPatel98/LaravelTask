<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $subscriptions = Cache::remember('user_subscriptions', 600, function () {
        //     return Subscription::with('plan:id,name,price,billing_cycle' ,'user:id,name,email')
        //         ->select('id', 'plan_id', 'user_id', 'start_date', 'end_date', 'amount', 'status')
        //         ->orderBy('start_date', 'desc')
        //         ->when($request->status !== null, function($query) use ($request) {
        //             $query->where('status', $request->status);
        //         })
        //         ->get()
        //         ->map(function($row){
        //             $row->start_date = date('d-m-Y', strtotime($row->start_date));
        //             $row->end_date = date('d-m-Y', strtotime($row->end_date));
        //             return $row;

        //         });
        // });

        $status = $request->status; // 'Active' or null

        $cacheKey = $status === 'Active' 
            ? 'user_subscriptions_active' 
            : 'user_subscriptions_all';

        $subscriptions = Cache::remember($cacheKey, 600, function () use ($status) {
            return Subscription::with('plan:id,name,price,billing_cycle', 'user:id,name,email')
                ->select('id', 'plan_id', 'user_id', 'start_date', 'end_date', 'amount', 'status')
                ->orderBy('start_date', 'desc')
                ->when($status === 'Active', function ($query) {
                    $query->where('status', 'Active');
                })
                ->get()
                ->map(function ($row) {
                    $row->start_date = date('d-m-Y', strtotime($row->start_date));
                    $row->end_date = date('d-m-Y', strtotime($row->end_date));
                    return $row;
                });
        });
            
        return response()->json(['status'=>true, 'message'=>'Subscriptions Fetched Successfully!', 'data' => $subscriptions], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'amount' => 'required|numeric|between:0,999999.99',
        ]);
        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'error'=>'Validation Error!'], 422);
        }

        $exists = Subscription::where('user_id', $request->user_id)
            ->where('plan_id', $request->plan_id)
            ->where('status', 'Active')
            ->exists();
        if($exists){
            return response()->json(['status'=>false, 'message'=>'This Plan Subscription Already Exists!'], 422);
        }

        Subscription::create([
            'plan_id' => $request->plan_id,
            'user_id' => $request->user_id,
            'start_date' => date('Y-m-d', strtotime($request->start_date)),
            'end_date' => date('Y-m-d', strtotime($request->end_date)),
            'amount' => $request->amount,
            'status' => 'Active',
        ]);
        Cache::forget('user_subscriptions_all');
        Cache::forget('user_subscriptions_active');
        return response()->json(['status'=>true, 'message'=>'Subscription Created Successfully!'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscription $subscription)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subscription $subscription)
    {
        $validated = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'amount' => 'required|numeric|between:0,999999.99',
            'status' => 'required|in:Active,Inactive',
        ]);
        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'error'=>'Validation Error!'], 422);
        }

        $exists = Subscription::where('user_id', $request->user_id)
            ->where('plan_id', $request->plan_id)
            ->where('status', 'Active')
            ->whereNot('id', $subscription->id)
            ->exists();
        if($exists){
            return response()->json(['status'=>false, 'message'=>'This Plan Subscription Already Exists!'], 422);
        }

        Subscription::find($subscription->id)->update([
            'plan_id' => $request->plan_id,
            'user_id' => $request->user_id,
            'start_date' => date('Y-m-d', strtotime($request->start_date)),
            'end_date' => date('Y-m-d', strtotime($request->end_date)),
            'amount' => $request->amount,
            'status' => $request->status,
        ]);
        Cache::forget('user_subscriptions_all');
        Cache::forget('user_subscriptions_active');
        return response()->json(['status'=>true, 'message'=>'Subscription Updated Successfully!'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription)
    {
        //
    }
}

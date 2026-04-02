<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:plans,name',
            'price' => 'required|numeric|between:0,999999.99',
            'billing_cycle' => 'required|in:Monthly,Yearly',
            'status' => 'required|in:Active,Inactive',
        ]);
        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'error'=>'Validation Error!'], 422);
        }

        $plan = Plan::create($validated->validate());
        return response()->json(['status'=>true, 'message'=>'Plan Created Successfully!'], 200);
    }


    /**
     * Display the specified resource.
     */
    public function show(Plan $plan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:plans,name,'. $plan->id,
            'price' => 'required|numeric|between:0,999999.99',
            'billing_cycle' => 'required|in:Monthly,Yearly',
            'status' => 'required|in:Active,Inactive',
        ]);
        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'error'=>'Validation Error!'], 422);
        }

        $plan = Plan::find($plan->id)->update($validated->validate());
        return response()->json(['status'=>true, 'message'=>'Plan Updated Successfully!'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plan $plan)
    {
        //
    }
}

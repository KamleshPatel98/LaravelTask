<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: "/subscriptions",
        summary: "Get user subscriptions",
        description: "Fetch all subscriptions or only active subscriptions (cached for 10 minutes)",
        operationId: "getSubscriptions",
        tags: ["Subscriptions"],

        parameters: [
            new OA\Parameter(
                name: "status",
                in: "query",
                required: false,
                description: "Filter by status (Active only)",
                schema: new OA\Schema(
                    type: "string",
                    enum: ["Active"],
                    example: "Active"
                )
            )
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: "Subscriptions fetched successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Subscriptions Fetched Successfully!"),
                        
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                type: "object",
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),

                                    new OA\Property(
                                        property: "plan",
                                        type: "object",
                                        properties: [
                                            new OA\Property(property: "id", type: "integer", example: 1),
                                            new OA\Property(property: "name", type: "string", example: "Premium Plan"),
                                            new OA\Property(property: "price", type: "number", format: "float", example: 999.99),
                                            new OA\Property(property: "billing_cycle", type: "string", example: "Monthly"),
                                        ]
                                    ),

                                    new OA\Property(
                                        property: "user",
                                        type: "object",
                                        properties: [
                                            new OA\Property(property: "id", type: "integer", example: 10),
                                            new OA\Property(property: "name", type: "string", example: "John Doe"),
                                            new OA\Property(property: "email", type: "string", example: "john@example.com"),
                                        ]
                                    ),

                                    new OA\Property(property: "start_date", type: "string", example: "01-04-2026"),
                                    new OA\Property(property: "end_date", type: "string", example: "01-05-2026"),
                                    new OA\Property(property: "amount", type: "number", format: "float", example: 999.99),
                                    new OA\Property(property: "status", type: "string", example: "Active"),
                                ]
                            )
                        ),
                    ]
                )
            )
        ]
    )]
    public function index(Request $request)
    {
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
    #[OA\Post(
        path: "/subscriptions",
        summary: "Create a subscription",
        description: "Create a new user subscription if not already active for the same plan",
        operationId: "storeSubscription",
        tags: ["Subscriptions"],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["plan_id", "user_id", "start_date", "end_date", "amount"],
                properties: [
                    new OA\Property(
                        property: "plan_id",
                        type: "integer",
                        example: 1
                    ),
                    new OA\Property(
                        property: "user_id",
                        type: "integer",
                        example: 10
                    ),
                    new OA\Property(
                        property: "start_date",
                        type: "string",
                        format: "date",
                        example: "2026-04-01"
                    ),
                    new OA\Property(
                        property: "end_date",
                        type: "string",
                        format: "date",
                        example: "2026-05-01"
                    ),
                    new OA\Property(
                        property: "amount",
                        type: "number",
                        format: "float",
                        example: 999.99
                    ),
                ]
            )
        ),

        responses: [
            new OA\Response(
                response: 200,
                description: "Subscription created successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Subscription Created Successfully!")
                    ]
                )
            ),

            new OA\Response(
                response: 422,
                description: "Validation or duplicate subscription error",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "This Plan Subscription Already Exists!"),
                        new OA\Property(property: "error", type: "string", example: "Validation Error!")
                    ]
                )
            )
        ]
    )]
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
    #[OA\Put(
        path: "/subscriptions/{id}",
        summary: "Update a subscription",
        description: "Update an existing subscription and clear cached subscription data",
        operationId: "updateSubscription",
        tags: ["Subscriptions"],

        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Subscription ID",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["plan_id", "user_id", "start_date", "end_date", "amount", "status"],
                properties: [
                    new OA\Property(property: "plan_id", type: "integer", example: 1),
                    new OA\Property(property: "user_id", type: "integer", example: 10),
                    new OA\Property(property: "start_date", type: "string", format: "date", example: "2026-04-01"),
                    new OA\Property(property: "end_date", type: "string", format: "date", example: "2026-05-01"),
                    new OA\Property(property: "amount", type: "number", format: "float", example: 999.99),
                    new OA\Property(
                        property: "status",
                        type: "string",
                        enum: ["Active", "Inactive"],
                        example: "Active"
                    ),
                ]
            )
        ),

        responses: [
            new OA\Response(
                response: 200,
                description: "Subscription updated successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Subscription Updated Successfully!")
                    ]
                )
            ),

            new OA\Response(
                response: 422,
                description: "Validation or duplicate subscription error",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "This Plan Subscription Already Exists!"),
                        new OA\Property(property: "error", type: "string", example: "Validation Error!")
                    ]
                )
            ),

            new OA\Response(
                response: 404,
                description: "Subscription not found"
            )
        ]
    )]
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

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: "/plans",
        summary: "Get active plans",
        description: "Fetch all active plans (cached for 10 minutes)",
        operationId: "getPlans",
        tags: ["Plans"],

        responses: [
            new OA\Response(
                response: 200,
                description: "Plans fetched successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "status",
                            type: "boolean",
                            example: true
                        ),
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "Plans Fetched Successfully!"
                        ),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                type: "object",
                                properties: [
                                    new OA\Property(
                                        property: "id",
                                        type: "integer",
                                        example: 1
                                    ),
                                    new OA\Property(
                                        property: "name",
                                        type: "string",
                                        example: "Basic Plan"
                                    ),
                                    new OA\Property(
                                        property: "price",
                                        type: "number",
                                        format: "float",
                                        example: 499.99
                                    ),
                                    new OA\Property(
                                        property: "billing_cycle",
                                        type: "string",
                                        example: "monthly"
                                    ),
                                ]
                            )
                        ),
                    ]
                )
            )
        ]
    )]
    public function index()
    {
        $plans = Cache::remember('active_plans', 600, function () {
            return Plan::select('id', 'name', 'price', 'billing_cycle')
                ->where('status', 'Active')
                ->orderBy('name', 'asc')
                ->get();
        });
        return response()->json(['status'=>true, 'message'=>'Plans Fetched Successfully!', 'data' => $plans], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: "/plans",
        summary: "Create a new plan",
        description: "Create a new subscription plan and clear cached active plans",
        operationId: "storePlan",
        tags: ["Plans"],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "price", "billing_cycle", "status"],
                properties: [
                    new OA\Property(
                        property: "name",
                        type: "string",
                        example: "Premium Plan"
                    ),
                    new OA\Property(
                        property: "price",
                        type: "number",
                        format: "float",
                        example: 999.99
                    ),
                    new OA\Property(
                        property: "billing_cycle",
                        type: "string",
                        enum: ["Monthly", "Yearly"],
                        example: "Monthly"
                    ),
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
                description: "Plan created successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Plan Created Successfully!")
                    ]
                )
            ),

            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "The name has already been taken."),
                        new OA\Property(property: "error", type: "string", example: "Validation Error!")
                    ]
                )
            )
        ]
    )]
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
        Cache::forget('active_plans');
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
    #[OA\Put(
        path: "/plans/{id}",
        summary: "Update a plan",
        description: "Update an existing plan and clear cached active plans",
        operationId: "updatePlan",
        tags: ["Plans"],

        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Plan ID",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "price", "billing_cycle", "status"],
                properties: [
                    new OA\Property(
                        property: "name",
                        type: "string",
                        example: "Premium Plan"
                    ),
                    new OA\Property(
                        property: "price",
                        type: "number",
                        format: "float",
                        example: 1499.99
                    ),
                    new OA\Property(
                        property: "billing_cycle",
                        type: "string",
                        enum: ["Monthly", "Yearly"],
                        example: "Yearly"
                    ),
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
                description: "Plan updated successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Plan Updated Successfully!")
                    ]
                )
            ),

            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "The name has already been taken."),
                        new OA\Property(property: "error", type: "string", example: "Validation Error!")
                    ]
                )
            ),

            new OA\Response(
                response: 404,
                description: "Plan not found"
            )
        ]
    )]
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
        Cache::forget('active_plans');
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

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
    path: "/users",
    summary: "Get all users",
    description: "Fetch all users with id, name, and email",
    operationId: "getUsers",
    tags: ["Users"],
    responses: [
        new OA\Response(
            response: 200,
            description: "Users fetched successfully",
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
                        example: "User Fetched Successfully!"
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
                                    example: "John Doe"
                                ),
                                new OA\Property(
                                    property: "email",
                                    type: "string",
                                    example: "john@example.com"
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
        $users = User::select('id', 'name', 'email')->get();
        return response()->json(['status'=>true, 'message'=>'User Fetched Successfully!', 'data' => $users], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: "/users",
        summary: "Create a new user",
        description: "Store a new user with name, email, and password",
        operationId: "storeUser",
        tags: ["Users"],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password"],
                properties: [
                    new OA\Property(
                        property: "name",
                        type: "string",
                        example: "John Doe"
                    ),
                    new OA\Property(
                        property: "email",
                        type: "string",
                        format: "email",
                        example: "john@example.com"
                    ),
                    new OA\Property(
                        property: "password",
                        type: "string",
                        format: "password",
                        example: "password123"
                    ),
                ]
            )
        ),

        responses: [
            new OA\Response(
                response: 200,
                description: "User created successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "User Created Successfully!")
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
                        new OA\Property(property: "message", type: "string", example: "The email field is required."),
                        new OA\Property(property: "error", type: "string", example: "Validation Error!")
                    ]
                )
            )
        ]
    )]
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);
        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'error'=>'Validation Error!'], 422);
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        return response()->json(['status'=>true, 'message'=>'User Created Successfully!'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User; // Changed to 'User' (singular) for Laravel convention
use App\Http\Requests\StoreUsersRequest;
use App\Http\Requests\UpdateUsersRequest;
use Illuminate\Http\Response;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /users
     */
    public function index()
    {
        // Fetch all users from the database
        $users = User::all();

        // Return a JSON response with the collection of users
        return response()->json($users, Response::HTTP_OK); // 200 OK
    }

    /**
     * Show the form for creating a new resource.
     * (Generally unused in a pure API, but kept for completeness)
     */
    public function create()
    {
        // In an API, you typically don't show a form.
        // This method can be left empty or removed entirely.
    }

    /**
     * Store a newly created resource in storage.
     * POST /users
     */
    public function store(StoreUsersRequest $request)
    {
        // The data is already validated by StoreUsersRequest
        
        // Create a new user record in the database
        $user = User::create($request->validated());

        // Return a JSON response with the newly created user and a 201 status
        return response()->json($user, Response::HTTP_CREATED); // 201 Created
    }

    /**
     * Display the specified resource.
     * GET /users/{user}
     */
    public function show(User $user) // Type-hinted to match route model binding
    {
        // The User model is automatically retrieved by Laravel (Route Model Binding)
        
        // Return a JSON response with the single user data
        return response()->json($user, Response::HTTP_OK); // 200 OK
    }

    /**
     * Show the form for editing the specified resource.
     * (Generally unused in a pure API)
     */
    public function edit(User $user)
    {
        // In an API, you typically don't show an edit form.
    }

    /**
     * Update the specified resource in storage.
     * PUT/PATCH /users/{user}
     */
    public function update(UpdateUsersRequest $request, User $user)
    {
        // The data is already validated by UpdateUsersRequest
        
        // Update the user record with the validated data
        $user->update($request->validated());

        // Return a JSON response with the updated user data
        return response()->json($user, Response::HTTP_OK); // 200 OK
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /users/{user}
     */
    public function destroy(User $user)
    {
        // Delete the user record
        $user->delete();

        // Return a response indicating success with no content
        return response()->json(null, Response::HTTP_NO_CONTENT); // 204 No Content
    }
}
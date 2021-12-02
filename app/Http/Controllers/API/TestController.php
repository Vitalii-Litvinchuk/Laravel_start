<?php

namespace App\Http\Controllers\API;

/**
 * @OA\Get(
 *     path="/api/test",
 *     @OA\Response(response="200", description="Display a listing of projects.")
 * )
 */

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showAnimals()
    {
        $animals = array(
            'Dog',
            'Puppy',
            'Turtle',
            'Rabbit',
            'Parrot',
            'Cat',
            'Kitten',
            'Goldfish',
            'Mouse',
            'Tropical fish',
            'Hamster');
        return ['Animals' => $animals ];
    }
}

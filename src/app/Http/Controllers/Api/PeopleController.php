<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\People;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PeopleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $people = People::all();
        return response()->json($people);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(Request $request)
    {
        $request->validate(People::getValidationRules());

        $savedModel = null;
        People::createPerson($request->all(), $savedModel);

        return response()->json($savedModel, 201);
    }

    /**
     * Search people by name
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request) {
        $keyword = "%" . $request->get("keyword") . "%";
        $field = $request->get("sort_field");
        $order = $request->get("sort_order");

        $people = People::search($keyword, $field, $order);

        return response()->json($people);
    }
}

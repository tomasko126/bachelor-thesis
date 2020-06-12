<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Station;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class StationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $stations = Station::all();

        return response()->json($stations);
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
        $request->validate(Station::getValidationRules());

        $savedModel = null;
        Station::createStation($request->all(), $savedModel);

        return response()->json($savedModel, 201);
    }

    /**
     * Search stations by name
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request) {
        $keyword = "%" . $request->get("keyword") . "%";
        $field = $request->get("sort_field");
        $order = $request->get("sort_order");

        $stations = Station::search($keyword, $field, $order);

        return response()->json($stations);
    }
}
